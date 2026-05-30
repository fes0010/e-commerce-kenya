# Fix Images Not Loading - E-Commerce Kenya

## Problem
Logo and favicon images are not loading on https://ecommerce.munene.shop/

## Root Cause
The storage symbolic link is not created in the production container, so uploaded images in `storage/app/public/` are not accessible via the web.

## Solution

### Option 1: SSH into Dokploy Server (Recommended)

```bash
# 1. SSH into your Dokploy server
ssh user@your-server-ip

# 2. Find your container name
docker ps | grep ecommerce

# 3. Create storage link
docker exec -it <container-name> php artisan storage:link

# 4. Set proper permissions
docker exec -it <container-name> chmod -R 775 storage bootstrap/cache
docker exec -it <container-name> chown -R www-data:www-data storage bootstrap/cache

# 5. Clear cache
docker exec -it <container-name> php artisan cache:clear
docker exec -it <container-name> php artisan config:clear
docker exec -it <container-name> php artisan view:clear
```

### Option 2: Add to Dockerfile

Update `docker/production/Dockerfile` to include:

```dockerfile
# After composer install, add:
RUN php artisan storage:link && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache
```

### Option 3: Add to Entrypoint Script

Update `docker/production/entrypoint.sh`:

```bash
#!/bin/bash

# Create storage link if it doesn't exist
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Start services
exec "$@"
```

## Quick Fix Commands

```bash
# Get container name
CONTAINER=$(docker ps --filter "name=ecommerce" --format "{{.Names}}" | head -1)

# Run all fixes
docker exec $CONTAINER php artisan storage:link
docker exec $CONTAINER chmod -R 775 storage bootstrap/cache
docker exec $CONTAINER chown -R www-data:www-data storage bootstrap/cache
docker exec $CONTAINER php artisan cache:clear
docker exec $CONTAINER php artisan config:clear
```

## Verify Fix

1. Check if symbolic link exists:
```bash
docker exec $CONTAINER ls -la public/ | grep storage
```

Expected output:
```
lrwxrwxrwx 1 www-data www-data   20 May 30 10:00 storage -> ../storage/app/public
```

2. Check if images are accessible:
```bash
docker exec $CONTAINER ls -la storage/app/public/
```

3. Test in browser:
```
https://ecommerce.munene.shop/storage/theme/1/logo.png
```

## Alternative: Use APP_URL for Images

If symbolic link doesn't work, update the channel configuration:

```bash
docker exec $CONTAINER php artisan tinker

# In tinker:
$channel = \Webkul\Core\Models\Channel::first();
$channel->logo = 'theme/1/logo.png';
$channel->favicon = 'theme/1/favicon.ico';
$channel->save();
```

## Permanent Fix for Dokploy

Add this to your `docker-compose.dokploy.yml` in the app service:

```yaml
app:
  # ... existing config
  volumes:
    - storage-data:/var/www/html/storage
    - ./public:/var/www/html/public
  command: >
    sh -c "php artisan storage:link &&
           chmod -R 775 storage bootstrap/cache &&
           php-fpm"
```

And add volume:

```yaml
volumes:
  storage-data:
    driver: local
```

## Check Current Status

```bash
# Check if storage link exists
docker exec $CONTAINER test -L public/storage && echo "Link exists" || echo "Link missing"

# Check permissions
docker exec $CONTAINER ls -la storage/

# Check uploaded files
docker exec $CONTAINER find storage/app/public -type f -name "*.png" -o -name "*.jpg"
```

## After Fix

1. Re-upload logo and favicon in admin panel
2. Clear browser cache (Ctrl+Shift+R)
3. Check if images load

## Admin Panel Steps

1. Go to: https://ecommerce.munene.shop/admin
2. Navigate to: **Settings** → **Channels** → **Edit Default Channel**
3. Upload new logo and favicon
4. Save
5. Clear cache: `docker exec $CONTAINER php artisan cache:clear`
6. Refresh website

## Troubleshooting

### Images still not loading?

1. **Check nginx/apache config** - Ensure it serves `/storage` path
2. **Check file permissions** - Files should be readable by web server
3. **Check .htaccess** - Ensure it's not blocking storage access
4. **Check APP_URL** - Should match your domain exactly

### Check nginx config:

```bash
docker exec $CONTAINER cat /etc/nginx/sites-available/default | grep storage
```

Should have:

```nginx
location /storage {
    alias /var/www/html/storage/app/public;
}
```

## Contact Support

If issues persist, provide:
1. Container logs: `docker logs $CONTAINER`
2. Storage structure: `docker exec $CONTAINER ls -laR storage/app/public/`
3. Public directory: `docker exec $CONTAINER ls -la public/`
