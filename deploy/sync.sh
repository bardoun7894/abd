#!/usr/bin/env bash
#
# Deploy app code to an instance via rsync.
#
#   ./deploy/sync.sh noor          # noor-alsabah.com   (Hostinger shared)
#   ./deploy/sync.sh contabo       # 91.230.110.187     (VPS)
#   ./deploy/sync.sh noor --dry    # preview only
#
# WHY THIS SCRIPT EXISTS
# ----------------------
# rsync does NOT read .gitignore. On 2026-07-25 a hand-rolled rsync copied the
# developer's LOCAL database/invoices.sqlite onto noor-alsabah.com, silently
# publishing 47 test invoices and 37 batches into a production system that was
# supposed to be empty. database/testing.sqlite went with it. Both are
# gitignored, which is exactly why nobody expected them to travel.
#
# Every exclusion below is load-bearing. Read the reason before removing one.
set -euo pipefail

TARGET="${1:-}"
DRY=""
[[ "${2:-}" == "--dry" ]] && DRY="--dry-run"

case "$TARGET" in
  noor)    DEST="noor:~/domains/noor-alsabah.com/public_html/" ;;
  contabo) DEST="MyContabo:/var/www/abd/" ;;
  *) echo "usage: $0 {noor|contabo} [--dry]" >&2; exit 2 ;;
esac

rsync -az --stats $DRY \
  `# --- instance identity: NEVER overwrite. Each server owns these. ---` \
  --exclude='.env' --exclude='.env.*' --exclude='env' \
  --exclude='.htaccess' --exclude='.htaccessOFF' \
  --exclude='php.ini' --exclude='.user.ini' \
  \
  `# --- runtime state owned by the server ---` \
  --exclude='storage/' --exclude='bootstrap/cache/' \
  --exclude='public/uploads/' \
  \
  `# --- LOCAL DEV DATABASES. The 2026-07-25 incident. Gitignored, so easy to` \
  `#     forget they are still sitting in the working tree. ---` \
  --exclude='database/*.sqlite' --exclude='database/*.sqlite-*' \
  --exclude='database/*.db' \
  \
  `# --- per-company assets. public/assets is gitignored so each server holds` \
  `#     its OWN logo; logo-voucher.png however IS tracked and carries صباح` \
  `#     النور's mark — shipping it would print the wrong company on vouchers. ---` \
  --exclude='public/assets/media/' \
  --exclude='resources/images/logo-voucher.png' \
  \
  `# --- build/dev artefacts ---` \
  --exclude='node_modules/' --exclude='vendor/' --exclude='.git/' \
  --exclude='public/hot' --exclude='routes.zip' \
  --exclude='ai-knowledge-base/' --exclude='specs/' --exclude='tests/' \
  --exclude='.specify/' --exclude='.pi/' --exclude='.claude/' \
  --exclude='memory_usage.txt' --exclude='*.log' --exclude='.DS_Store' \
  \
  ./ "$DEST"

# NOTE: deliberately no --delete. These servers hold user uploads and per-company
# assets that do not exist in the repo; a --delete sweep would remove them.

cat <<EOF

rsync finished ($TARGET).

Remaining manual steps — none of these are automatic:
  1. composer install --no-dev -o --ignore-platform-req=ext-oci8   (if deps changed)
  2. php artisan migrate --force
  3. php artisan migrate --database=invoices --path=database/migrations/invoices --force
     ^ separate connection; plain migrate does NOT pick these up and fails SILENTLY
  4. bump config/global.php version_css  (browsers cache per version, not per content)
  5. php artisan config:clear && php artisan view:clear
EOF
