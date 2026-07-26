#!/bin/sh
#
# Queue worker tick, driven by cron every minute.
#
# WHY THIS IS A SCRIPT AND NOT AN INLINE CRON COMMAND
# ---------------------------------------------------
# Hostinger's hPanel cron runs the command through `timeout <n> <command>`
# WITHOUT a shell. So an inline entry like
#
#   cd /path && flock -n lock php artisan queue:work >> log 2>&1
#
# fails immediately with:
#
#   timeout: failed to run command 'cd': No such file or directory
#
# because `cd` is a shell builtin, not an executable — and for the same reason
# `&&`, `>>` and `2>&1` are never interpreted either. Pointing cron at this
# file gives us a single executable path, so no shell metacharacters need to
# survive hPanel's argv splitting.
#
# INSTALL (hPanel -> Advanced -> Cron Jobs, "Custom", every minute `* * * * *`):
#
#   /home/u536066507/domains/noor-alsabah.com/public_html/deploy/queue-cron.sh
#
# Must be executable: chmod +x deploy/queue-cron.sh
#
# WHY THIS EXISTS AT ALL
# ----------------------
# noor-alsabah.com is Hostinger shared hosting behind LiteSpeed, which
# hard-kills every web request at ~121s (measured: a set_time_limit(0) probe
# in the docroot returns 500 at 121.18s; the box already has
# max_execution_time=0, so this is not a PHP limit and no ini change moves it).
#
# With QUEUE_CONNECTION=sync, ProcessInvoiceBatch runs inline in the upload
# request, which capped AI invoice extraction at ~26 pages (~3.5s/page). A
# 34-page scan died on page 33 of 34. CLI PHP has no such wall — the same batch
# completed in 130.7s under `queue:work`. See deploy/PROVISIONING.md §1.
#
# Requires QUEUE_CONNECTION=invoices in .env, otherwise jobs still run inline
# and this worker finds an empty queue forever.
set -eu

APP_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$APP_DIR"

# php8.2 — the default `php` on PATH inside CageFS is not guaranteed to be it.
PHP=/opt/alt/php82/usr/bin/php
[ -x "$PHP" ] || PHP=php

# -n: if a worker from a previous tick is still running, exit rather than pile
# up a second one. Batches can legitimately run for minutes while cron fires
# every 60s, so without this the queue would be worked concurrently.
#
# --max-time=290 keeps each tick well under any per-process ceiling and lets a
# fresh worker pick up where this one left off. --stop-when-empty means an idle
# tick costs ~1s instead of holding a slot open.
exec /usr/bin/flock -n storage/queue.lock \
    "$PHP" artisan queue:work invoices \
        --stop-when-empty \
        --max-time=290 \
        --tries=1 \
        >> storage/logs/queue.log 2>&1
