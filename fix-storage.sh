#!/bin/bash

# ============================================================================
# Fix Storage Link for E-Commerce Kenya
# ============================================================================
# This script fixes image loading issues by creating the storage symbolic link
# and setting proper permissions in the production container.
# ============================================================================

echo "🔧 Fixing storage link for E-Commerce Kenya..."
echo ""

# Find container name
echo "📦 Finding container..."
CONTAINER=$(docker ps --filter "name=ecommerce" --format "{{.Names}}" | head -1)

if [ -z "$CONTAINER" ]; then
    echo "❌ Error: Could not find ecommerce container"
    echo "Available containers:"
    docker ps --format "{{.Names}}"
    exit 1
fi

echo "✅ Found container: $CONTAINER"
echo ""

# Create storage link
echo "🔗 Creating storage symbolic link..."
docker exec $CONTAINER php artisan storage:link
echo ""

# Set permissions
echo "🔐 Setting permissions..."
docker exec $CONTAINER chmod -R 775 storage bootstrap/cache
docker exec $CONTAINER chown -R www-data:www-data storage bootstrap/cache
echo ""

# Clear caches
echo "🧹 Clearing caches..."
docker exec $CONTAINER php artisan cache:clear
docker exec $CONTAINER php artisan config:clear
docker exec $CONTAINER php artisan view:clear
echo ""

# Verify
echo "✅ Verifying storage link..."
docker exec $CONTAINER ls -la public/ | grep storage
echo ""

echo "📁 Checking storage directory..."
docker exec $CONTAINER ls -la storage/app/public/
echo ""

echo "✅ Done! Storage link created and permissions set."
echo ""
echo "📝 Next steps:"
echo "1. Go to: https://ecommerce.munene.shop/admin"
echo "2. Navigate to: Settings → Channels → Edit Default Channel"
echo "3. Re-upload logo and favicon"
echo "4. Clear browser cache (Ctrl+Shift+R)"
echo "5. Check if images load"
echo ""
echo "🔍 Test image URL:"
echo "https://ecommerce.munene.shop/storage/theme/1/logo.png"
