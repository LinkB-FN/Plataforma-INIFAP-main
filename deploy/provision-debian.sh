#!/bin/bash
# Deploys Plataforma INIFAP (laravel-app) on Debian 12 with Nginx + PHP-FPM 8.2 + PostgreSQL 15.
#
# Usage, from a copy of this repository on the server:
#   sudo bash deploy/provision-debian.sh
#
# The database schema (1_biblioteca_inifap.sql and 2_biblioteca_seguridad.sql) is not in git.
# Put both files in laravel-app/ (where .gitignore expects them) or point SCHEMA_DIR at them.
#
# Safe to run again to redeploy: it keeps .env, uploaded files and the database.
#
# Environment variables:
#   SRC         repository root (default: the parent of this script's directory)
#   SCHEMA_DIR  directory with the two schema SQL files (default: $SRC/laravel-app)
#   DB_PASS     database password for the first run (default: random). Later runs reuse the one in .env.
set -euo pipefail

SRC=${SRC:-$(cd "$(dirname "$0")/.." && pwd)}
SCHEMA_DIR=${SCHEMA_DIR:-$SRC/laravel-app}
APP_DIR=/var/www/inifap
DB_NAME=inifap_dp
DB_USER=inifap_app
# Vite 7 and pdfjs-dist need Node >= 22.13; Debian 12 ships Node 18.
NODE_MAJOR=22

[ -d "$SRC/laravel-app" ] || { echo "No laravel-app/ in $SRC; set SRC to the repository root." >&2; exit 1; }
for f in 1_biblioteca_inifap.sql 2_biblioteca_seguridad.sql; do
    [ -f "$SCHEMA_DIR/$f" ] || { echo "Missing $SCHEMA_DIR/$f (the schema is not in git)." >&2; exit 1; }
done

if [ -f "$APP_DIR/.env" ]; then
    DB_PASS=$(sed -n 's/^DB_PASSWORD=//p' "$APP_DIR/.env")
fi
DB_PASS=${DB_PASS:-$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')}

echo "==> 1. Packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get install -y -q nginx postgresql postgresql-client \
    php8.2-cli php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl \
    php8.2-zip php8.2-intl php8.2-gd php8.2-bcmath composer unzip git rsync \
    ca-certificates curl gnupg

echo "==> 2. Node.js $NODE_MAJOR (NodeSource)"
if ! command -v node >/dev/null || ! node -e '
    const [major, minor] = process.versions.node.split(".").map(Number);
    process.exit(major > 22 || (major === 22 && minor >= 13) ? 0 : 1)'; then
    install -d -m 0755 /etc/apt/keyrings
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor --yes -o /etc/apt/keyrings/nodesource.gpg
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list
    apt-get update -q
    apt-get install -y -q nodejs
fi
node --version

echo "==> 3. PostgreSQL role and database"
if sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    sudo -u postgres psql -v ON_ERROR_STOP=1 -qc "ALTER ROLE $DB_USER PASSWORD '$DB_PASS'"
else
    sudo -u postgres psql -v ON_ERROR_STOP=1 -qc "CREATE ROLE $DB_USER LOGIN PASSWORD '$DB_PASS'"
fi
sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'" | grep -q 1 \
    || sudo -u postgres createdb -O "$DB_USER" "$DB_NAME"

echo "==> 4. Application files"
mkdir -p "$APP_DIR"
# Excluded paths are also kept on the server: rsync --delete does not touch them.
rsync -a --delete --exclude .env --exclude vendor --exclude node_modules --exclude public/build \
    --exclude 'storage/logs/*' --exclude 'storage/framework/sessions/*' --exclude 'storage/framework/views/*' \
    --exclude 'storage/app/public/uploads' --exclude public/storage \
    "$SRC/laravel-app/" "$APP_DIR/"
# The migration run_postgres_schema_scripts loads these two files from base_path().
cp "$SCHEMA_DIR/1_biblioteca_inifap.sql" "$SCHEMA_DIR/2_biblioteca_seguridad.sql" "$APP_DIR/"
chown -R www-data:www-data "$APP_DIR"

cd "$APP_DIR"
as_web() { sudo -u www-data -H "$@"; }

echo "==> 5. Composer and .env"
as_web composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
if [ ! -f .env ]; then
    as_web cp .env.example .env
    IP=$(hostname -I | awk '{print $1}')
    sed -i \
        -e "s|^APP_NAME=.*|APP_NAME=\"Biblioteca INIFAP\"|" \
        -e "s|^APP_ENV=.*|APP_ENV=production|" \
        -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
        -e "s|^APP_URL=.*|APP_URL=http://$IP|" \
        -e "s|^APP_LOCALE=.*|APP_LOCALE=es|" \
        -e "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql\nDB_HOST=127.0.0.1\nDB_PORT=5432\nDB_DATABASE=$DB_NAME\nDB_USERNAME=$DB_USER\nDB_PASSWORD=$DB_PASS|" \
        -e "s|^SESSION_DRIVER=.*|SESSION_DRIVER=database\nSESSION_TABLE=sessions|" \
        -e "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" \
        -e "s|^CACHE_STORE=.*|CACHE_STORE=file|" \
        .env
    as_web php artisan key:generate --force
fi
chmod 640 .env

echo "==> 6. Frontend assets (Vite)"
# www-data's home (/var/www) is not writable, so npm gets its own cache directory.
install -d -o www-data -g www-data /var/cache/inifap-npm
as_web env npm_config_cache=/var/cache/inifap-npm npm ci --no-audit --no-fund
as_web env npm_config_cache=/var/cache/inifap-npm npm run build

echo "==> 7. Database schema and demo data"
as_web php artisan migrate --force
as_web php artisan db:seed --class=AdminSeeder --force
if [ "$(sudo -u postgres psql -d $DB_NAME -tAc 'SELECT COUNT(*) FROM publicaciones')" = "0" ]; then
    as_web php artisan db:seed --class=JsDataSeeder --force
fi

echo "==> 8. Storage link, permissions and caches"
as_web php artisan storage:link || true
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
as_web php artisan optimize:clear
as_web php artisan config:cache
as_web php artisan view:cache

echo "==> 9. PHP upload limits (videos up to 200 MB)"
PHPINI=/etc/php/8.2/fpm/conf.d/99-inifap.ini
printf 'upload_max_filesize = 200M\npost_max_size = 210M\nmemory_limit = 256M\n' > "$PHPINI"

echo "==> 10. Nginx"
cat > /etc/nginx/sites-available/inifap <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/inifap/public;
    index index.php;
    charset utf-8;
    client_max_body_size 210M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # The pdf.js worker is an ES module (.mjs); browsers refuse it unless it is served as JavaScript.
    location ~* \.mjs$ {
        default_type application/javascript;
        try_files $uri =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
NGINX
ln -sf /etc/nginx/sites-available/inifap /etc/nginx/sites-enabled/inifap
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now php8.2-fpm nginx postgresql
systemctl restart php8.2-fpm nginx

echo "==> Done: http://$(hostname -I | awk '{print $1}')/"
