#!/usr/bin/env bash
#
# Provision a NEW, EMPTY instance of the app alongside an existing one on the
# same Hostinger account.
#
#   new-instance.sh <docroot> <app_url> <db_name> <db_user> <db_pass>
#
# Example:
#   ~/sabah-init/new-instance.sh \
#       ~/domains/noor-alsabah.com/public_html/sabah \
#       https://sabah.noor-alsabah.com \
#       u536066507_sabah u536066507_sabah 'THE-PASSWORD'
#
# NOTE ON LAYOUT: Hostinger puts a subdomain's docroot INSIDE the parent site's
# public_html (…/public_html/sabah). That means the destination lives inside the
# rsync SOURCE, so the copy must exclude itself or it recurses. See step 1.
#
# WHY THIS SCRIPT EXISTS
# ----------------------
# `php artisan migrate` CANNOT build this app's schema. The live database has 96
# tables but only 44 migrations — the permission system, shop_rent, shop_rentpay
# and ~50 others are hand-made legacy tables with no migration at all. A fresh
# instance built from migrations alone is broken in ways that only surface later.
# So the schema comes from a structure-only dump of a working instance instead.
#
# What the new instance gets:
#   - every table's STRUCTURE (all 96)
#   - role definitions + lookup lists (per_controller, per_function, role_per,
#     city, job, moraslat types, vacation types, expense types, migrations)
#   - ONE fresh admin user (emp_job=1, which short-circuits every permission
#     check in App\Helpers\Perm, so no permission rows are needed)
#
# What it deliberately does NOT get:
#   - `permission` (891 rows) — that table maps emp_id -> role. Without the
#     original users those rows are orphans pointing at ids that do not exist.
#   - `users` — a fresh admin is created instead.
#   - any business data: shops, workers, purchases, invoices, financials.
#   - the source instance's uploads, logs, sessions, or .env.
set -euo pipefail

DOCROOT="${1:?usage: new-instance.sh <docroot> <app_url> <db_name> <db_user> <db_pass>}"
APP_URL="${2:?missing app_url, e.g. https://sabah.noor-alsabah.com}"
DB_NAME="${3:?missing db_name}"
DB_USER="${4:?missing db_user}"
DB_PASS="${5:?missing db_pass}"

SRC="$HOME/domains/noor-alsabah.com/public_html"   # working instance to clone code+schema from
STAGE="$HOME/sabah-init"                            # holds 01-schema.sql / 02-reference.sql

[[ -d "$SRC" ]]                  || { echo "source instance not found: $SRC" >&2; exit 1; }
[[ -f "$STAGE/01-schema.sql" ]]  || { echo "missing $STAGE/01-schema.sql" >&2; exit 1; }
[[ -f "$STAGE/02-reference.sql" ]] || { echo "missing $STAGE/02-reference.sql" >&2; exit 1; }
[[ -d "$DOCROOT" ]]              || { echo "docroot not found — create the subdomain in hPanel first: $DOCROOT" >&2; exit 1; }

say() { printf '\n=== %s\n' "$*"; }

# ---------------------------------------------------------------- 1. code
# Server-to-server copy from the working instance, so the new one runs exactly
# the same version. vendor/ is copied deliberately: composer is slow (and often
# unavailable) on shared hosting, and the source vendor/ is already known-good.
say "1/6 copying application code"
# If DOCROOT sits inside SRC (Hostinger's subdomain layout), rsync would copy the
# destination into itself and recurse forever. Exclude it by its path relative to SRC.
SELF_EXCLUDE=()
case "$DOCROOT/" in
  "$SRC"/*) SELF_EXCLUDE=( --exclude="/${DOCROOT#"$SRC"/}/" )
            echo "    (destination is inside source — excluding /${DOCROOT#"$SRC"/}/)" ;;
esac

rsync -a "${SELF_EXCLUDE[@]}" \
  --exclude='.env' --exclude='.env.*' --exclude='env' \
  --exclude='storage/' --exclude='bootstrap/cache/' \
  --exclude='public/uploads/' \
  --exclude='database/*.sqlite' --exclude='database/*.sqlite-*' --exclude='database/*.db' \
  --exclude='.git/' --exclude='node_modules/' \
  --exclude='*.log' --exclude='error_log' --exclude='.DS_Store' \
  --exclude='ai-knowledge-base/' --exclude='specs/' --exclude='.claude/' \
  "$SRC/" "$DOCROOT/"

# ------------------------------------------------- 2. writable runtime state
# storage/ is excluded above because it holds the SOURCE instance's logs,
# sessions and cached views. Recreate the skeleton empty.
# .htaccess is excluded from the rsync above (it is per-instance identity), but
# this instance has the SAME layout as the source — app root is the docroot and
# everything is rewritten into public/. Copy the proven one rather than invent a
# new rewrite, then make doubly sure .env can never be fetched over HTTP.
say "1b/6 installing .htaccess"
cp "$SRC/.htaccess" "$DOCROOT/.htaccess"
if ! grep -q 'FilesMatch.*\\\.env' "$DOCROOT/.htaccess"; then
  cat >> "$DOCROOT/.htaccess" <<'HT'

# The app root is the document root here, so deny the dotfiles outright instead
# of relying only on the rewrite above to hide them.
<FilesMatch "^\.(env|env\..*|git.*)$">
    Require all denied
</FilesMatch>
HT
fi

say "2/6 creating empty storage skeleton + uploads"
mkdir -p "$DOCROOT"/storage/{app/public,framework/{cache/data,sessions,testing,views},logs}
mkdir -p "$DOCROOT"/bootstrap/cache
mkdir -p "$DOCROOT"/public/uploads
chmod -R 775 "$DOCROOT"/storage "$DOCROOT"/bootstrap/cache "$DOCROOT"/public/uploads

# ---------------------------------------------------------------- 3. .env
say "3/6 writing .env"
cp "$SRC/.env" "$DOCROOT/.env"
php -r '
$f = $argv[1];
$s = file_get_contents($f);
$set = [
  "APP_URL"     => $argv[2],
  "DB_DATABASE" => $argv[3],
  "DB_USERNAME" => $argv[4],
  "DB_PASSWORD" => $argv[5],
  "APP_ENV"     => "production",
  "APP_DEBUG"   => "false",
  // Must stay false: the HTML minifier strips newlines inside inline <script>
  // blocks, so any // comment silently voids the rest of that script.
  "HTML_MINIFY" => "false",
];
foreach ($set as $k => $v) {
  $line = $k."=".$v;
  $s = preg_match("/^".preg_quote($k,"/")."=.*$/m", $s)
     ? preg_replace("/^".preg_quote($k,"/")."=.*$/m", $line, $s)
     : rtrim($s)."\n".$line."\n";
}
file_put_contents($f, $s);
' "$DOCROOT/.env" "$APP_URL" "$DB_NAME" "$DB_USER" "$DB_PASS"

cd "$DOCROOT"
php artisan key:generate --force >/dev/null   # its OWN APP_KEY — never share one across instances
echo "    APP_URL=$APP_URL  DB=$DB_NAME"

# ---------------------------------------------------------------- 4. schema
say "4/6 loading schema (structure only) + reference data"
mysql -h 127.0.0.1 -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$STAGE/01-schema.sql"
mysql -h 127.0.0.1 -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$STAGE/02-reference.sql"
TABLES=$(mysql -N -B -h 127.0.0.1 -u"$DB_USER" -p"$DB_PASS" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';")
echo "    $TABLES tables created"

# The invoices module runs on a SEPARATE sqlite connection whose migrations live
# in a subdirectory no service provider registers. Plain `migrate` does not pick
# them up and fails SILENTLY — the app then degrades quietly because the invoice
# code is Schema::hasColumn-guarded.
say "5/6 invoices connection (separate sqlite, separate migration path)"
touch "$DOCROOT/database/invoices.sqlite"
chmod 664 "$DOCROOT/database/invoices.sqlite"
php artisan migrate --database=invoices --path=database/migrations/invoices --force

# ------------------------------------------------------- 6. admin + branding
say "6/6 creating admin user and applying branding"
php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

if (! DB::table("users")->where("username", "admin")->exists()) {
    DB::table("users")->insert([
        "name" => "مدير النظام", "username" => "admin",
        "email" => "admin@example.com",
        "password" => Hash::make("ChangeMe!2026"),
        "emp_job" => 1, "active" => 1, "emp_is_delete" => 0,
        "created_at" => now(), "updated_at" => now(),
    ]);
    echo "    admin created (username: admin / ChangeMe!2026) — CHANGE THIS ON FIRST LOGIN\n";
} else {
    echo "    admin already exists, left untouched\n";
}

// Green identity, matching the MyContabo instance. These live in app_settings
// because Settings::applyToConfig() overrides config/brand.php at boot.
$brand = [
    "brand_theme"       => "sabah-emerald",
    "brand_pdf_primary" => "#0E6B4F",
    "brand_pdf_deep"    => "#0A4F3A",
    "brand_pdf_tint"    => "#E4EFE9",
    "brand_pdf_line"    => "#CBD5D1",
    "company_name_ar"   => "شركة نور الصباح",
    "company_name_en"   => "Noor Al-Sabah CO.",
];
foreach ($brand as $k => $v) {
    DB::table("app_settings")->updateOrInsert(
        ["skey" => $k],
        ["svalue" => $v, "updated_at" => now(), "created_at" => now()]
    );
}
echo "    branding applied (sabah-emerald)\n";
'

php artisan config:clear >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

cat <<EOF

Done. $APP_URL

Still manual:
  1. Copy the logo files from the MyContabo instance (they are per-company and
     deliberately excluded from every deploy):
       public/assets/media/logos/logo.png
       resources/images/logo-voucher.png
  2. Log in as admin / ChangeMe!2026 and change the password immediately.
  3. Set the Gemini API key in Settings if this instance should use AI.
EOF
