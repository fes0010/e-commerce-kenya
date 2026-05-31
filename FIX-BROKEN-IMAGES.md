# Fix: Broken/Blank Images Issue ✅

## Problem
Images were showing as broken/blank because **APP_URL was set to `http://localhost`** instead of the actual domain.

## Root Cause
- Container's `.env` file had `APP_URL=http://localhost`
- All image URLs were generated as `http://localhost/storage/theme/...`
- Browser couldn't load images from localhost

## Immediate Fix Applied ✅

```bash
# Fixed APP_URL in running container
docker exec 777b5eb87dce sed -i 's|^APP_URL=.*|APP_URL=https://ecommerce.munene.shop|' .env

# Cleared caches
docker exec 777b5eb87dce php artisan config:clear
docker exec 777b5eb87dce php artisan cache:clear
```

**Status**: ✅ Images should now load correctly!

## Permanent Fix: Configure in Dokploy

To prevent this from happening on every redeploy, you MUST set the APP_URL environment variable in Dokploy:

### Steps:

1. **Go to Dokploy Dashboard**
2. **Find your app**: `apps-ecommerce-4zagpn`
3. **Click on "Environment" or "Environment Variables"**
4. **Add/Update this variable**:
   ```
   APP_URL=https://ecommerce.munene.shop
   ```
5. **Save**
6. **Redeploy** (optional, but recommended)

### Why This is Important:
- Without this environment variable in Dokploy, every redeploy resets APP_URL to `http://localhost`
- This breaks all image URLs
- Setting it in Dokploy makes it persistent across deployments

## Other Important Environment Variables

While you're in Dokploy environment variables, make sure these are also set:

```
APP_URL=https://ecommerce.munene.shop
DB_HOST=services-freeman-kgiydl
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=Enterpassword001.
APP_TIMEZONE=Africa/Nairobi
APP_CURRENCY=KES
APP_LOCALE=en
```

## Verification

After setting APP_URL in Dokploy:

1. ✅ Go to: `https://ecommerce.munene.shop`
2. ✅ Check if logo displays
3. ✅ Go to admin: `https://ecommerce.munene.shop/admin`
4. ✅ Go to theme edit: `https://ecommerce.munene.shop/admin/settings/themes/edit/3`
5. ✅ Upload an image in Images tab
6. ✅ Check Preview tab - image should display
7. ✅ Check storefront - images should display

## Diagnostic Script

Created `check-images.sh` to diagnose image issues:

```bash
./check-images.sh
```

This checks:
- Storage symlink
- Storage directory
- Theme images
- APP_URL setting
- Permissions

## Common Image Issues & Fixes

### Issue 1: Images broken after redeploy
**Cause**: APP_URL not set in Dokploy
**Fix**: Set APP_URL environment variable in Dokploy

### Issue 2: Storage symlink missing
**Cause**: Container restarted without running `php artisan storage:link`
**Fix**: 
```bash
docker exec <container> php artisan storage:link
```

### Issue 3: Permission errors
**Cause**: Wrong ownership/permissions on storage
**Fix**:
```bash
docker exec <container> chown -R www-data:www-data storage
docker exec <container> chmod -R 775 storage
```

### Issue 4: Images not showing in Preview tab
**Cause**: Images not inserted into HTML
**Fix**: Images tab now auto-inserts images into HTML on upload

## Files Created

- ✅ `check-images.sh` - Diagnostic script for image issues
- ✅ `FIX-BROKEN-IMAGES.md` - This document

## Next Steps

1. **Set APP_URL in Dokploy** (most important!)
2. **Set persistent volume** for storage (see `DOKPLOY-VOLUMES-FIX.md`)
3. **Test image upload** in Images tab
4. **Verify images display** in Preview and storefront

---

**Current Status**: ✅ Fixed in running container
**Permanent Fix**: Set APP_URL in Dokploy environment variables
