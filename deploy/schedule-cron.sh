#!/bin/sh
#
# Laravel scheduler tick, driven by cron every minute.
#
# This is the SECOND cron entry this app needs. deploy/queue-cron.sh runs the
# queue worker; it does NOT run the scheduler, so everything in
# App\Console\Kernel::schedule() has simply never fired on noor-alsabah.com:
#
#   leases:scan-alerts    dailyAt('06:00')  — Spec 003 FR-204, the lease
#                                             due/expiry alert scan that sends
#                                             the in-app + email + SMS notices.
#                                             Without this cron the client is
#                                             never warned about an expiring
#                                             contract, and nothing in the UI
#                                             reveals that the scan is dead.
#   ai:recover-stale-jobs everyTenMinutes() — releases AI extractions left in
#                                             `processing` by a worker that was
#                                             killed mid-batch. Without it such a
#                                             batch stays "قيد المعالجة" forever.
#   testing:cron / worker:cron  lastDayOfMonth
#
# Laravel's own design is one `schedule:run` per minute; the framework decides
# internally which commands are actually due, so a per-minute tick is correct
# and cheap (an idle tick does nothing).
#
# WHY THIS IS A SCRIPT AND NOT AN INLINE CRON COMMAND
# ---------------------------------------------------
# Same reason as queue-cron.sh: Hostinger's hPanel runs the entry through
# `timeout <n> <command>` with NO shell, so `cd`, `&&`, `>>` and `2>&1` are
# never interpreted and an inline command dies with
# `timeout: failed to run command 'cd': No such file or directory`.
#
# INSTALL (hPanel -> Advanced -> Cron Jobs, "Custom", every minute `* * * * *`):
#
#   /home/u536066507/domains/noor-alsabah.com/public_html/deploy/schedule-cron.sh
#
# Must be executable: chmod +x deploy/schedule-cron.sh
#
# Note: crontab(1) does not exist inside CageFS and hand-writing
# /var/spool/cron/<user> does not fire — hPanel is the only way in.
# On the MyContabo box use a normal root crontab instead.
set -eu

APP_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$APP_DIR"

# php8.2 — the default `php` on PATH inside CageFS is not guaranteed to be it.
PHP=/opt/alt/php82/usr/bin/php
[ -x "$PHP" ] || PHP=php

# -n: skip this tick if the previous one is still running. schedule:run itself
# returns quickly, but a ->runInBackground() command it launched can outlive the
# tick, and piling up overlapping scheduler runs is how duplicate alerts happen.
exec /usr/bin/flock -n storage/schedule.lock \
    "$PHP" artisan schedule:run \
        >> storage/logs/schedule.log 2>&1
