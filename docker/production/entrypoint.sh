#!/bin/bash
set -e

APP_DIR="/var/www/bagisto"

# ==========================================================================
# Helper: log with timestamp
# ==========================================================================
log() {
    echo "[bagisto-entrypoint] $(date '+%Y-%m-%d %H:%M:%S') $*"
}

# ==========================================================================
# Set defaults for production environment
# These will be used if environment variables are not provided by Dokploy
# ==========================================================================
APP_URL="${APP_URL:-https://ecommerce.munene.shop}"
DB_HOST="${DB_HOST:-services-freeman-kgiydl}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-bagisto}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-Enterpassword001.}"
APP_TIMEZONE="${APP_TIMEZONE:-Africa/Nairobi}"
APP_CURRENCY="${APP_CURRENCY:-KES}"
APP_LOCALE="${APP_LOCALE:-en}"

# ==========================================================================
# Determine database mode based on DB_HOST
# ==========================================================================
use_internal_mysql() {
    [[  "$DB_HOST" == "127.0.0.1" || "$DB_HOST" == "localhost" ]]
}

if use_internal_mysql; then
    log "Mode: INTERNAL MySQL"
    export MYSQL_AUTOSTART=true
else
    log "Mode: EXTERNAL MySQL (${DB_HOST}:${DB_PORT})"
    export MYSQL_AUTOSTART=false
fi

# ==========================================================================
# Update .env with runtime configuration
# Always update these values to ensure correct configuration
# ==========================================================================
cd "$APP_DIR"

log "Applying runtime environment configuration..."

# Ensure storage directories exist (may be missing if volume-mounted)
mkdir -p "$APP_DIR/storage/framework/"{cache/data,sessions,views,testing}
mkdir -p "$APP_DIR/storage/logs"
mkdir -p "$APP_DIR/storage/app/public"
mkdir -p "$APP_DIR/bootstrap/cache"
# Use a safer method to update .env - replace entire lines
grep -v "^APP_URL=" .env > .env.tmp && echo "APP_URL=${APP_URL}" >> .env.tmp && mv .env.tmp .env
grep -v "^DB_HOST=" .env > .env.tmp && echo "DB_HOST=${DB_HOST}" >> .env.tmp && mv .env.tmp .env
grep -v "^DB_PORT=" .env > .env.tmp && echo "DB_PORT=${DB_PORT}" >> .env.tmp && mv .env.tmp .env
grep -v "^DB_DATABASE=" .env > .env.tmp && echo "DB_DATABASE=${DB_DATABASE}" >> .env.tmp && mv .env.tmp .env
grep -v "^DB_USERNAME=" .env > .env.tmp && echo "DB_USERNAME=${DB_USERNAME}" >> .env.tmp && mv .env.tmp .env
grep -v "^DB_PASSWORD=" .env > .env.tmp && echo "DB_PASSWORD=${DB_PASSWORD}" >> .env.tmp && mv .env.tmp .env
grep -v "^APP_TIMEZONE=" .env > .env.tmp && echo "APP_TIMEZONE=${APP_TIMEZONE}" >> .env.tmp && mv .env.tmp .env
grep -v "^APP_CURRENCY=" .env > .env.tmp && echo "APP_CURRENCY=${APP_CURRENCY}" >> .env.tmp && mv .env.tmp .env
grep -v "^APP_LOCALE=" .env > .env.tmp && echo "APP_LOCALE=${APP_LOCALE}" >> .env.tmp && mv .env.tmp .env

# Update APP_KEY if provided
if [ -n "$APP_KEY" ]; then
    grep -v "^APP_KEY=" .env > .env.tmp && echo "APP_KEY=${APP_KEY}" >> .env.tmp && mv .env.tmp .env
fi

# ==========================================================================
# Re-cache config after updating .env
# ==========================================================================
log "Re-caching configuration..."
php artisan optimize:clear --no-interaction 2>/dev/null || true
php artisan optimize --no-interaction 2>/dev/null || true

# Fix permissions after optimize (which runs as root and creates root-owned files)
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ==========================================================================
# Create storage/installed flag so Bagisto skips the installer
# ==========================================================================
if [ ! -f "$APP_DIR/storage/installed" ]; then
    touch "$APP_DIR/storage/installed"
    log "Created storage/installed flag."
fi

# ==========================================================================
# Ensure storage symlink exists
# ==========================================================================
if [ ! -L "$APP_DIR/public/storage" ]; then
    php artisan storage:link --no-interaction 2>/dev/null || true
    log "Created storage symlink."
fi

# ==========================================================================
# External MySQL: wait for connectivity before Supervisor starts
# ==========================================================================
if ! use_internal_mysql; then
    log "Waiting for external MySQL at ${DB_HOST}:${DB_PORT}..."
    for i in $(seq 1 60); do
        if php -r "try { new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
            log "External MySQL is reachable."
            break
        fi
        if [ "$i" -eq 60 ]; then
            log "ERROR: Cannot reach external MySQL at ${DB_HOST}:${DB_PORT} after 60s"
            exit 1
        fi
        sleep 1
    done
fi

log "Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 || log "WARNING: Migration had issues, continuing anyway"

# ==========================================================================
# Fix scraped product data issues (idempotent — safe to run every startup)
# ==========================================================================
log "Fixing product image paths and enabling products..."
php "$APP_DIR/docker/production/fix-product-data.php" 2>&1 || log "WARNING: Product data fix had issues"
log "Product data fixes applied."

log "Starting services via Supervisor..."

# ==========================================================================
# Hand off to CMD (supervisord)
# ==========================================================================
exec "$@"
