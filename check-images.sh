#!/bin/bash

echo "=== Image Storage Diagnostic ==="
echo ""

# Find the container
CONTAINER=$(docker ps | grep ecommerce | awk '{print $1}' | head -1)

if [ -z "$CONTAINER" ]; then
    echo "❌ Container not found!"
    exit 1
fi

echo "✅ Container: $CONTAINER"
echo ""

echo "1. Checking storage symlink..."
docker exec $CONTAINER ls -la public/storage
echo ""

echo "2. Checking storage/app/public directory..."
docker exec $CONTAINER ls -la storage/app/public/
echo ""

echo "3. Checking theme directory..."
docker exec $CONTAINER ls -la storage/app/public/theme/ 2>/dev/null || echo "Theme directory doesn't exist"
echo ""

echo "4. Checking APP_URL..."
docker exec $CONTAINER grep "^APP_URL=" .env
echo ""

echo "5. Checking storage permissions..."
docker exec $CONTAINER stat -c "%a %U:%G %n" storage/app/public
echo ""

echo "6. Testing image access..."
echo "Checking if images are accessible via web..."
docker exec $CONTAINER ls -lh storage/app/public/theme/*/
echo ""

echo "=== Fix Commands ==="
echo ""
echo "If symlink is missing, run:"
echo "docker exec $CONTAINER php artisan storage:link"
echo ""
echo "If permissions are wrong, run:"
echo "docker exec $CONTAINER chown -R www-data:www-data storage"
echo "docker exec $CONTAINER chmod -R 775 storage"
echo ""
echo "If APP_URL is wrong, update it in Dokploy environment variables"
