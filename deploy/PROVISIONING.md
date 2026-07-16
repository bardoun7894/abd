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
