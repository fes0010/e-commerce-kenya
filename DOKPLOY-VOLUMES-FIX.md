# Fix: Theme Changes Reset After Redeploy

## Problem
Your theme customizations (images, content) are stored in:
1. **Database** (`/var/lib/mysql`) - theme settings, content
2. **Storage folder** (`/var/www/bagisto/storage`) - uploaded images

Both get **wiped on every redeploy** because there are no persistent volumes configured in Dokploy.

## Solution: Configure Persistent Volumes in Dokploy

### Step 1: Access Dokploy Dashboard
1. Go to your Dokploy dashboard
2. Find your app: `apps-ecommerce-4zagpn`
3. Click on the app to open settings

### Step 2: Add Persistent Volumes

Go to the **"Volumes"** or **"Mounts"** section and add these two volumes:

#### Volume 1: MySQL Database
```
Container Path: /var/lib/mysql
Volume Name: ecommerce-mysql-data
```

#### Volume 2: Storage (Uploaded Files)
```
Container Path: /var/www/bagisto/storage
Volume Name: ecommerce-storage
```

### Step 3: Save and Redeploy
1. Click **"Save"** to apply volume configuration
2. Click **"Redeploy"** to restart with persistent volumes
3. ⚠️ **IMPORTANT**: Your current data will be lost on this first redeploy
4. After this redeploy, all future deploys will preserve your data

### Step 4: Reconfigure Your Store
After the redeploy with volumes:
1. Go to admin panel: `https://ecommerce.munene.shop/admin`
2. Login: `admin@example.com` / `admin123`
3. Set up your theme customizations again
4. Upload your logo/favicon
5. Configure your theme sections

**From now on, these changes will persist across redeployments!**

---

## Alternative: Docker Compose with Volumes (If Dokploy Supports It)

If Dokploy allows you to use a custom `docker-compose.yml`, use this:

```yaml
version: '3.8'

services:
  bagisto:
    image: your-registry/bagisto:latest
    ports:
      - "80:80"
    environment:
      - APP_URL=https://ecommerce.munene.shop
      - DB_HOST=services-freeman-kgiydl
      - DB_PORT=3306
      - DB_DATABASE=bagisto
      - DB_USERNAME=root
      - DB_PASSWORD=Enterpassword001.
      - APP_TIMEZONE=Africa/Nairobi
      - APP_CURRENCY=KES
    volumes:
      # Persistent storage for uploaded files
      - bagisto-storage:/var/www/bagisto/storage
      # If using internal MySQL (not recommended in production)
      - bagisto-mysql:/var/lib/mysql
    restart: unless-stopped

volumes:
  bagisto-storage:
    driver: local
  bagisto-mysql:
    driver: local
```

---

## Verification After Fix

After configuring volumes and redeploying:

### Test 1: Upload a Logo
1. Go to admin → Configuration → General → Design
2. Upload a logo
3. Save
4. **Redeploy the app in Dokploy**
5. ✅ Logo should still be there after redeploy

### Test 2: Create Theme Content
1. Go to admin → Settings → Themes
2. Edit theme ID 3
3. Add images using the visual editor
4. Save
5. **Redeploy the app in Dokploy**
6. ✅ Theme content should still be there after redeploy

---

## Why This Happens

### Without Volumes (Current State):
```
Redeploy → New Container → Fresh MySQL → Fresh Storage → All Data Lost ❌
```

### With Volumes (After Fix):
```
Redeploy → New Container → Same MySQL Volume → Same Storage Volume → Data Preserved ✅
```

---

## Important Notes

### 1. External MySQL (Recommended)
You're already using external MySQL: `services-freeman-kgiydl`

This means you **only need to configure the storage volume**, not the MySQL volume!

Update your Dokploy environment variables:
```
DB_HOST=services-freeman-kgiydl
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=Enterpassword001.
```

Then you only need **ONE volume**:
```
Container Path: /var/www/bagisto/storage
Volume Name: ecommerce-storage
```

### 2. First Redeploy After Volume Configuration
The first redeploy after adding volumes will still lose data because the volumes are empty. This is expected. After that, data persists.

### 3. Backup Before Major Changes
Even with volumes, always backup before major changes:
```bash
# Backup database
docker exec services-freeman-kgiydl mysqldump -u root -p bagisto > backup.sql

# Backup storage
docker cp apps-ecommerce-4zagpn.1.XXXXX:/var/www/bagisto/storage ./storage-backup
```

---

## Quick Fix: Use External MySQL + Storage Volume

Since you already have external MySQL (`services-freeman-kgiydl`), you just need:

### In Dokploy:
1. **Environment Variables** (set these):
   ```
   DB_HOST=services-freeman-kgiydl
   DB_PORT=3306
   DB_DATABASE=bagisto
   DB_USERNAME=root
   DB_PASSWORD=Enterpassword001.
   APP_URL=https://ecommerce.munene.shop
   APP_TIMEZONE=Africa/Nairobi
   APP_CURRENCY=KES
   ```

2. **Volumes** (add this):
   ```
   Container Path: /var/www/bagisto/storage
   Volume Name: ecommerce-storage
   ```

3. **Redeploy** with these settings

4. **Reconfigure** your store (one last time)

5. **Done!** Future redeployments will preserve everything ✅

---

## Troubleshooting

### If storage volume doesn't work:
```bash
# Check if volume is mounted
docker exec apps-ecommerce-4zagpn.1.XXXXX df -h | grep storage

# Check volume permissions
docker exec apps-ecommerce-4zagpn.1.XXXXX ls -la /var/www/bagisto/storage

# Fix permissions if needed
docker exec apps-ecommerce-4zagpn.1.XXXXX chown -R www-data:www-data /var/www/bagisto/storage
docker exec apps-ecommerce-4zagpn.1.XXXXX chmod -R 775 /var/www/bagisto/storage
```

### If database connection fails:
```bash
# Test connection from container
docker exec apps-ecommerce-4zagpn.1.XXXXX php artisan tinker
>>> DB::connection()->getPdo();
```

---

**Next Step**: Configure the storage volume in Dokploy dashboard, then redeploy!
