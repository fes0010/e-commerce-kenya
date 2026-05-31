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
    [[ "$DB_HOST" == "127.0.0.1" || "$DB_HOST" == "localhost" ]]
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

# Escape special characters for sed
APP_URL_ESCAPED=$(echo "$APP_URL" | sed 's/[\/&]/\\&/g')
DB_PASSWORD_ESCAPED=$(echo "$DB_PASSWORD" | sed 's/[\/&]/\\&/g')

sed -i "s|^APP_URL=.*|APP_URL=${APP_URL_ESCAPED}|" .env
sed -i "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD_ESCAPED}/" .env
sed -i "s/^APP_TIMEZONE=.*/APP_TIMEZONE=${APP_TIMEZONE}/" .env
sed -i "s/^APP_CURRENCY=.*/APP_CURRENCY=${APP_CURRENCY}/" .env
sed -i "s/^APP_LOCALE=.*/APP_LOCALE=${APP_LOCALE}/" .env

# Update APP_KEY if provided
if [ -n "$APP_KEY" ]; then
    APP_KEY_ESCAPED=$(echo "$APP_KEY" | sed 's/[\/&]/\\&/g')
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY_ESCAPED}|" .env
fi

# ==========================================================================
# Re-cache config after updating .env
# ==========================================================================
log "Re-caching configuration..."
php artisan optimize:clear --no-interaction 2>/dev/null || true
php artisan optimize --no-interaction 2>/dev/null || true

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

log "Starting services via Supervisor..."

# ==========================================================================
# Hand off to CMD (supervisord)
# ==========================================================================
exec "$@"
