# Quick Dokploy Setup - Stop Data Loss on Redeploy

## The Problem
Every time you redeploy, your theme changes disappear because:
- ❌ No persistent volumes configured
- ❌ Storage folder gets wiped
- ❌ Database gets reset (if using internal MySQL)

## The Solution (5 Minutes)

### Step 1: Open Dokploy Dashboard
Go to your Dokploy dashboard and find app: `apps-ecommerce-4zagpn`

### Step 2: Configure Environment Variables
Click on **"Environment"** or **"Environment Variables"** tab and set:

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

### Step 3: Add Persistent Volume
Click on **"Volumes"** or **"Mounts"** tab and add:

```
Container Path: /var/www/bagisto/storage
Volume Name: ecommerce-storage
```

**Note**: You don't need a MySQL volume because you're using external MySQL (`services-freeman-kgiydl`)

### Step 4: Save and Redeploy
1. Click **"Save"** to apply changes
2. Click **"Redeploy"** 
3. ⚠️ Check **"No Cache"** option for fresh build
4. Wait for deployment to complete

### Step 5: Reconfigure Store (One Last Time)
After redeploy:
1. Go to: `https://ecommerce.munene.shop/admin`
2. Login: `admin@example.com` / `admin123`
3. Upload logo/favicon
4. Configure themes
5. Add your content

**✅ From now on, all changes will persist across redeployments!**

---

## Verify It Works

### Test 1: Upload Logo
1. Admin → Configuration → General → Design
2. Upload logo → Save
3. **Redeploy in Dokploy**
4. ✅ Logo should still be there

### Test 2: Theme Content
1. Admin → Settings → Themes → Edit
2. Add images → Save
3. **Redeploy in Dokploy**
4. ✅ Content should still be there

---

## What Changed in Code

✅ **Dockerfile**: Added `VOLUME` declarations
✅ **Pushed to GitHub**: Commit `ab6b8662d1`

When you redeploy, Dokploy will:
1. Pull latest code from GitHub
2. Build new Docker image with volume declarations
3. Mount persistent volumes
4. Start container with data preserved

---

## If You Can't Find Volume Settings in Dokploy

Some Dokploy versions hide volume settings. Alternative:

### Option A: Use Docker Compose
If Dokploy supports custom `docker-compose.yml`:

1. In Dokploy, switch deployment method to "Docker Compose"
2. Use this compose file:

```yaml
version: '3.8'
services:
  app:
    image: ${DOCKER_IMAGE}
    ports:
      - "80:80"
    environment:
      APP_URL: https://ecommerce.munene.shop
      DB_HOST: services-freeman-kgiydl
      DB_PORT: 3306
      DB_DATABASE: bagisto
      DB_USERNAME: root
      DB_PASSWORD: Enterpassword001.
      APP_TIMEZONE: Africa/Nairobi
      APP_CURRENCY: KES
    volumes:
      - storage-data:/var/www/bagisto/storage
    restart: unless-stopped

volumes:
  storage-data:
```

### Option B: Manual Volume Mount
SSH into your server and manually create volume:

```bash
# Create volume
docker volume create ecommerce-storage

# Stop current container
docker stop apps-ecommerce-4zagpn.1.XXXXX

# Start with volume
docker run -d \
  --name ecommerce-app \
  -p 80:80 \
  -v ecommerce-storage:/var/www/bagisto/storage \
  -e APP_URL=https://ecommerce.munene.shop \
  -e DB_HOST=services-freeman-kgiydl \
  -e DB_PORT=3306 \
  -e DB_DATABASE=bagisto \
  -e DB_USERNAME=root \
  -e DB_PASSWORD=Enterpassword001. \
  your-image:latest
```

---

## Need Help?

If you can't find volume settings in Dokploy:
1. Take a screenshot of your Dokploy app settings page
2. Check Dokploy documentation for "volumes" or "persistent storage"
3. Or use the manual Docker volume method above

---

**Status**: ✅ Code updated and pushed
**Next**: Configure volume in Dokploy dashboard
