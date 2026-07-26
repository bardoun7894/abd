# Server provisioning notes (MyContabo: 91.230.110.187, served by nginx + php8.2-fpm on :9095)

These are **server-side** settings that live outside the repo. Re-apply them on any
fresh server. App code is deployed via `git pull` in `/var/www/abd`.

> IMPORTANT: run all `php artisan` commands as **www-data**, never root, e.g.
> `sudo -u www-data HOME=/tmp php artisan ...`. Running artisan as root creates
> root-owned files under `storage/`+`bootstrap/cache/` that then break the web
> user with `Permission denied` (500). If it happens:
> `chown -R www-data:www-data storage bootstrap/cache`.

## 1. Async queue (fixes invoice-upload 504)
Invoice/lease processing is dispatched as a job (`ProcessInvoiceBatch`). It MUST run
on a background worker, not `sync` (sync runs the job inline in the web request →
504 on multi-page PDFs).

```bash
# .env
QUEUE_CONNECTION=database

# jobs table (also created by database/migrations/2026_07_16_110000_create_jobs_table.php)
sudo -u www-data HOME=/tmp php artisan migrate --path=database/migrations/2026_07_16_110000_create_jobs_table.php --force

# persistent worker
cp deploy/abd-queue.service /etc/systemd/system/abd-queue.service
touch /var/www/abd/storage/logs/queue-worker.log
chown www-data:www-data /var/www/abd/storage/logs/queue-worker.log
systemctl daemon-reload && systemctl enable --now abd-queue
systemctl status abd-queue          # should be active (running)
```
After deploying code that changes jobs, restart the worker: `systemctl restart abd-queue`.

### 1b. Same fix on noor-alsabah.com (Hostinger shared — no systemd)

This step was **missed** during the 2026-07-25 Hostinger migration and cost a day
of debugging on 2026-07-26. Symptom: `66666.pdf` (34 pages) always failed with
`فشل الرفع، تأكد أن الملف PDF` while 24–26-page scans succeeded. Root cause was
exactly what §1 describes, on a host where the §1 recipe does not apply.

**The wall is ~121 seconds and it is not PHP's.** A docroot probe with
`set_time_limit(0)` returns HTTP 500 at 121.18s. The box already has
`max_execution_time=0` and `memory_limit=1536M`, so this is LiteSpeed's own
request ceiling — no `php.ini`, `.user.ini`, or `set_time_limit()` change moves
it. At ~3.5s/page that capped extraction at ~26 pages. (For reference: the same
34-page batch completes in 130.7s under a CLI `queue:work`.)

Note the repo's root `php.ini` / `.user.ini` carrying `upload_max_filesize =
99999999999M` are **not** in this host's effective path and do nothing at all.

```bash
# .env  — the invoices connection keeps the jobs table in the invoices DB
QUEUE_CONNECTION=invoices
php artisan config:clear
```

`jobs` / `failed_jobs` already exist on BOTH the mysql and invoices connections
on this host — no migration needed. Verify before assuming.

Worker: there is no systemd. `crontab` does not even exist inside CageFS, and
writing `/var/spool/cron/<user>` by hand does **not** fire (tested). Cron must be
registered in **hPanel → Advanced → Cron Jobs**:

- Type **Custom**
- Command: `/home/u536066507/domains/noor-alsabah.com/public_html/deploy/queue-cron.sh`
- Schedule: Minute = `Every minute (*)`, everything else `*`. The Hour/Day/Month/
  Weekday fields are all **required** — pick the `Once an hour (0 * * * *)` preset
  first to fill them, then change Minute to `Every minute (*)`.

**Do not paste an inline shell command.** hPanel runs the entry through
`timeout <n> <command>` with **no shell**, so `cd`, `&&`, `>>` and `2>&1` are
never interpreted. An inline entry fails with:

```
timeout: failed to run command 'cd': No such file or directory
```

That is why the worker lives in `deploy/queue-cron.sh` (must be `chmod +x`) —
a single executable path with nothing for hPanel's argv splitting to mangle.
Read that file's header before changing it.

Verify end to end (a no-op job — batch 999999 does not exist, so zero AI calls):
```bash
php -r '...ProcessInvoiceBatch::dispatch(999999);'   # jobs count must go 0 -> 1
# within ~60s, cron drains it:
tail storage/logs/queue.log     # "ProcessInvoiceBatch ... DONE"
php artisan queue:failed        # "No failed jobs found."
```
If the jobs count stays 0, dispatch is still inline — `QUEUE_CONNECTION` did not
take (check `config:clear`).

## 2. Upload size limits (fixes 413 Content Too Large)
```ini
# /etc/php/8.2/fpm/php.ini
upload_max_filesize = 100M
post_max_size       = 105M
max_file_uploads    = 50
```
```nginx
# /etc/nginx/sites-available/abd  (inside server {})
client_max_body_size 100M;
```
```bash
nginx -t && systemctl reload nginx
systemctl restart php8.2-fpm
```

## 3. Gemini AI (key + model)
Key and model are edited in-app: **Settings → إعدادات مفاتيح الـ API** (DB `app_settings`,
overrides `config('services.gemini.*')` at boot). `.env` values are the fallback.

- Working model on the current (limited-tier) key: **`gemini-flash-lite-latest`**.
- `gemini-3.5-flash` is a valid GA model but the current key returns 429/503 (quota) for
  it — needs a **billing-enabled** key. `GEMINI_TIMEOUT=300` in `.env`.

## 4. ZATCA / SMS (env or Settings)
`ZATCA_SELLER_NAME="شركة صباح النور"` set; `ZATCA_VAT_NUMBER` pending. SMS (Taqnyat)
keys pending — both editable from the Settings screen.
