# Dokploy Environment Variables Not Being Injected

## Problem
Environment variables configured in Dokploy are **NOT being passed to the container**.

## Evidence
```bash
# Container only has default environment variables
docker inspect 777b5eb87dce | grep -A 20 '"Env":'
# Output shows only: PATH, DEBIAN_FRONTEND, TZ
# Missing: APP_URL, DB_HOST, DB_PORT, etc.
```

## Why This Happens
Dokploy might be configured to use:
1. **Docker Compose mode** - Environment variables should be in `docker-compose.yml`
2. **Dockerfile mode** - Environment variables need to be passed via `-e` flags
3. **Build-time variables** - These are baked into the image, not runtime

## Solutions

### Solution 1: Check Dokploy Deployment Method

1. Go to Dokploy dashboard
2. Find app: `apps-ecommerce-4zagpn`
3. Check deployment method:
   - If "Docker Compose" → Environment variables should be in compose file
   - If "Dockerfile" → Environment variables should be in "Environment" tab

### Solution 2: Use Docker Compose (Recommended)

Create `docker-compose.production.yml` in your repo:

```yaml
version: '3.8'

services:
  app:
    image: ${DOCKER_IMAGE:-apps-ecommerce-4zagpn:latest}
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
      APP_LOCALE: en
    volumes:
      - storage-data:/var/www/bagisto/storage
    restart: unless-stopped

volumes:
  storage-data:
    driver: local
```

Then in Dokploy:
1. Set deployment method to "Docker Compose"
2. Point to `docker-compose.production.yml`
3. Redeploy

### Solution 3: Manual Environment File

If Dokploy doesn't support environment injection, we can bake it into the image:

Update `Dockerfile` to copy a production `.env` file:

```dockerfile
# After copying .env.example
COPY .env.production .env
```

Create `.env.production` in repo:
```
APP_URL=https://ecommerce.munene.shop
DB_HOST=services-freeman-kgiydl
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=Enterpassword001.
APP_TIMEZONE=Africa/Nairobi
APP_CURRENCY=KES
```

### Solution 4: Fix Entrypoint to Use Defaults

Update `docker/production/entrypoint.sh` to use sensible defaults:

```bash
# Set defaults if environment variables are not provided
APP_URL="${APP_URL:-https://ecommerce.munene.shop}"
DB_HOST="${DB_HOST:-services-freeman-kgiydl}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-bagisto}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-Enterpassword001.}"
APP_TIMEZONE="${APP_TIMEZONE:-Africa/Nairobi}"
APP_CURRENCY="${APP_CURRENCY:-KES}"

# Always update .env with these values
sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
sed -i "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
sed -i "s/^APP_TIMEZONE=.*/APP_TIMEZONE=${APP_TIMEZONE}/" .env
sed -i "s/^APP_CURRENCY=.*/APP_CURRENCY=${APP_CURRENCY}/" .env
```

## Recommended Approach

**Use Solution 4** (Fix Entrypoint with Defaults) because:
- ✅ Works regardless of Dokploy configuration
- ✅ No need to figure out Dokploy's environment injection
- ✅ Sensible defaults baked in
- ✅ Can still be overridden by environment variables if needed

## Implementation

I'll update the entrypoint script to use defaults for your production environment.

---

**Current Status**: Environment variables not being injected by Dokploy
**Recommended Fix**: Update entrypoint.sh with production defaults
