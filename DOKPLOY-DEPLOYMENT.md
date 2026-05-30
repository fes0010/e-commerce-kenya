# Bagisto Dokploy Deployment Guide

Complete guide for deploying Bagisto e-commerce platform on Dokploy with production-ready configuration.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Configuration](#environment-configuration)
3. [Database Setup](#database-setup)
4. [Dokploy Configuration](#dokploy-configuration)
5. [Deployment Steps](#deployment-steps)
6. [Post-Deployment](#post-deployment)
7. [Troubleshooting](#troubleshooting)
8. [ECC Integration](#ecc-integration)

---

## Prerequisites

### Required Services

- **Dokploy Server**: Running and accessible
- **MySQL 8.0+**: Database server (existing or new)
- **Redis**: For caching and sessions
- **Elasticsearch 7.17+**: For product search
- **Domain Name**: With DNS configured
- **SSL Certificate**: Let's Encrypt (automatic via Dokploy)

### Local Requirements

- Git repository (GitHub, GitLab, Bitbucket)
- SSH access to Dokploy server (for troubleshooting)
- Basic understanding of Docker and Docker Compose

---

## Environment Configuration

### 1. Copy Environment Template

```bash
cp .env.dokploy .env
```

### 2. Update Critical Variables

Edit `.env` and update these essential values:

```bash
# Application
APP_NAME="Your Store Name"
APP_URL=https://your-domain.com
APP_ENV=production
APP_DEBUG=false

# Database (use your MySQL container IP or service name)
DB_HOST=10.0.1.29
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=your-secure-password

# Redis (will be created by docker-compose)
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# Elasticsearch (will be created by docker-compose)
ELASTICSEARCH_HOST=elasticsearch
ELASTICSEARCH_PORT=9200

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@your-domain.com

# Payment Gateways (add your credentials)
PAYPAL_CLIENT_ID=your-paypal-client-id
PAYPAL_CLIENT_SECRET=your-paypal-secret
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

Copy the generated key to your `.env` file.

---

## Database Setup

### Option 1: Use Existing MySQL Container

Your existing MySQL container is running at `10.0.1.29:3306`:

```bash
# Create database
docker exec -i 4e65c028136e mysql -u root -pEnterpassword001. -e "CREATE DATABASE IF NOT EXISTS bagisto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Verify
docker exec -i 4e65c028136e mysql -u root -pEnterpassword001. -e "SHOW DATABASES;"
```

Update `.env`:
```bash
DB_HOST=10.0.1.29
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=Enterpassword001.
```

### Option 2: Create New MySQL Service in Dokploy

1. In Dokploy dashboard, create a new MySQL service
2. Note the service name and credentials
3. Update `.env` with the new connection details

---

## Dokploy Configuration

### 1. Create New Application

1. Log in to Dokploy dashboard
2. Click **"Create Application"**
3. Choose **"Docker Compose"** deployment type

### 2. Connect Git Repository

1. Connect your Git provider (GitHub, GitLab, etc.)
2. Select your Bagisto repository
3. Choose the branch (e.g., `main` or `production`)

### 3. Configure Build Settings

- **Docker Compose File**: `docker-compose.dokploy.yml`
- **Build Context**: `.` (root directory)
- **Dockerfile**: `docker/production/Dockerfile`

### 4. Set Environment Variables

In Dokploy's environment variable section, add all variables from `.env.dokploy`:

**Critical Variables:**
```
APP_NAME=Bagisto Commerce
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-generated-key

DB_HOST=10.0.1.29
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=Enterpassword001.

REDIS_HOST=redis
REDIS_PORT=6379

ELASTICSEARCH_HOST=elasticsearch
ELASTICSEARCH_PORT=9200
```

### 5. Configure Domain & SSL

1. **Add Domain**: Enter your domain name (e.g., `your-domain.com`)
2. **Enable SSL**: Toggle automatic SSL certificate (Let's Encrypt)
3. **DNS Configuration**: Point your domain to Dokploy server IP

```bash
# DNS Records (example)
A     @              your-dokploy-server-ip
A     www            your-dokploy-server-ip
CNAME admin          your-domain.com
```

### 6. Configure Networking

Ensure the application is connected to the `dokploy-network`:

```yaml
networks:
  - dokploy-network
```

This allows communication with existing services (MySQL, etc.).

---

## Deployment Steps

### 1. Initial Deployment

1. Click **"Deploy"** in Dokploy dashboard
2. Monitor build logs for errors
3. Wait for all containers to start (app, queue, scheduler, redis, elasticsearch)

### 2. Run Database Migrations

After first deployment:

```bash
# SSH into Dokploy server
ssh user@your-dokploy-server

# Run migrations
docker exec bagisto-app php artisan migrate --force

# Seed initial data (optional)
docker exec bagisto-app php artisan db:seed --force
```

### 3. Build Frontend Assets

```bash
# Admin panel
docker exec bagisto-app bash -c "cd packages/Webkul/Admin && npm install && npm run build"

# Shop frontend
docker exec bagisto-app bash -c "cd packages/Webkul/Shop && npm install && npm run build"

# Installer
docker exec bagisto-app bash -c "cd packages/Webkul/Installer && npm install && npm run build"
```

### 4. Optimize Application

```bash
# Clear and cache configuration
docker exec bagisto-app php artisan config:cache

# Clear and cache routes
docker exec bagisto-app php artisan route:cache

# Clear and cache views
docker exec bagisto-app php artisan view:cache

# Optimize autoloader
docker exec bagisto-app composer dump-autoload --optimize
```

### 5. Index Products for Search

```bash
# Index all products in Elasticsearch
docker exec bagisto-app php artisan indexer:index
```

### 6. Set Permissions

```bash
# Set proper permissions for storage and cache
docker exec bagisto-app chown -R www-data:www-data storage bootstrap/cache
docker exec bagisto-app chmod -R 775 storage bootstrap/cache
```

---

## Post-Deployment

### 1. Verify Deployment

- **Homepage**: https://your-domain.com
- **Admin Panel**: https://your-domain.com/admin
- **Health Check**: https://your-domain.com/health

### 2. Create Admin User

```bash
docker exec -it bagisto-app php artisan bagisto:user:create
```

Follow the prompts to create an admin account.

### 3. Configure Store Settings

1. Log in to admin panel
2. Navigate to **Configuration** → **General**
3. Update:
   - Store name and logo
   - Contact information
   - Currency and locale
   - Tax settings
   - Shipping methods
   - Payment methods

### 4. Set Up Monitoring

Monitor these metrics in Dokploy:

- **Container Health**: All containers running
- **CPU Usage**: < 70% average
- **Memory Usage**: < 80% average
- **Disk Usage**: < 80%
- **Response Time**: < 2 seconds

### 5. Configure Backups

```bash
# Database backup (daily cron)
0 2 * * * docker exec bagisto-app php artisan backup:run

# File backup (weekly)
0 3 * * 0 tar -czf /backups/bagisto-files-$(date +\%Y\%m\%d).tar.gz /var/www/html/storage /var/www/html/public
```

### 6. Set Up Queue Workers

Verify queue workers are running:

```bash
docker ps | grep bagisto-queue
docker logs bagisto-queue
```

### 7. Configure Cron Jobs

Verify scheduler is running:

```bash
docker ps | grep bagisto-scheduler
docker logs bagisto-scheduler
```

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed

**Error**: `SQLSTATE[HY000] [2002] Connection timed out`

**Solution**:
```bash
# Check if MySQL is accessible
docker exec bagisto-app ping -c 3 10.0.1.29

# Test MySQL connection
docker exec bagisto-app mysql -h 10.0.1.29 -u root -pEnterpassword001. -e "SELECT 1;"

# If using socat proxy, ensure it's running
ps aux | grep socat
```

#### 2. Redis Connection Failed

**Error**: `Connection refused [tcp://redis:6379]`

**Solution**:
```bash
# Check Redis container
docker ps | grep redis

# Test Redis connection
docker exec bagisto-app redis-cli -h redis ping

# Restart Redis
docker restart bagisto-redis
```

#### 3. Elasticsearch Not Working

**Error**: `No alive nodes found in your cluster`

**Solution**:
```bash
# Check Elasticsearch health
curl http://localhost:9200/_cluster/health

# Check Elasticsearch logs
docker logs bagisto-elasticsearch

# Increase memory if needed
docker update --memory=2g bagisto-elasticsearch
```

#### 4. Permission Denied Errors

**Error**: `The stream or file "/var/www/html/storage/logs/laravel.log" could not be opened`

**Solution**:
```bash
# Fix permissions
docker exec bagisto-app chown -R www-data:www-data storage bootstrap/cache
docker exec bagisto-app chmod -R 775 storage bootstrap/cache
```

#### 5. 502 Bad Gateway

**Error**: Nginx returns 502

**Solution**:
```bash
# Check PHP-FPM status
docker exec bagisto-app ps aux | grep php-fpm

# Check application logs
docker logs bagisto-app

# Restart application
docker restart bagisto-app
```

### Debug Mode

To enable debug mode temporarily:

```bash
# Update environment variable in Dokploy
APP_DEBUG=true

# Redeploy or restart containers
docker restart bagisto-app

# Remember to disable after debugging!
APP_DEBUG=false
```

### View Logs

```bash
# Application logs
docker logs -f bagisto-app

# Queue worker logs
docker logs -f bagisto-queue

# Scheduler logs
docker logs -f bagisto-scheduler

# Redis logs
docker logs -f bagisto-redis

# Elasticsearch logs
docker logs -f bagisto-elasticsearch

# Laravel logs
docker exec bagisto-app tail -f storage/logs/laravel.log
```

---

## ECC Integration

### What is ECC?

ECC (Evolved Code Companion) is a harness-native operator system for agentic work, providing AI-assisted development capabilities across multiple AI coding assistants.

### Setting Up ECC

1. **Clone ECC Repository** (already done):
```bash
# ECC is cloned at /tmp/ecc-repo
```

2. **Configure ECC Environment**:

Create `.env` in your project root with ECC variables:

```bash
# Anthropic API (for Claude)
ANTHROPIC_API_KEY=your-anthropic-api-key

# GitHub Token (for MCP GitHub server)
GITHUB_TOKEN=your-github-token

# Optional: Astraflow / UModelVerse
ASTRAFLOW_API_KEY=your-astraflow-key
ASTRAFLOW_MODEL=gpt-4o-mini
ASTRAFLOW_BASE_URL=https://api.umodelverse.ai/v1

# GitHub username
GITHUB_USER=your-github-username

# Default branch
DEFAULT_BASE_BRANCH=main
```

3. **Copy ECC Skills to Your Project**:

```bash
# Copy skills directory
cp -r /tmp/ecc-repo/skills ./ecc-skills

# Copy rules directory
cp -r /tmp/ecc-repo/rules ./ecc-rules

# Copy agent configuration
cp /tmp/ecc-repo/agent.yaml ./agent.yaml
```

4. **Use ECC Skills in Development**:

ECC provides 250+ skills for:
- Code generation and refactoring
- Testing and debugging
- Documentation generation
- Security scanning
- Performance optimization
- Database migrations
- API development

5. **Integrate with Your AI Assistant**:

- **Cursor**: Copy `.cursor` directory
- **Claude Code**: Copy `.claude` directory
- **Kiro**: Copy `.kiro` directory
- **Zed**: Copy `.zed` directory

### ECC Skills for Bagisto

Relevant ECC skills for Bagisto development:

- **Laravel Skills**: Laravel-specific patterns and best practices
- **PHP Skills**: PHP 8.2+ features and optimization
- **Database Skills**: MySQL optimization and migrations
- **API Skills**: RESTful API design and implementation
- **Testing Skills**: PHPUnit and Pest testing
- **Security Skills**: Security scanning and vulnerability detection
- **Performance Skills**: Caching, optimization, and profiling

### Example: Using ECC for Development

```bash
# Generate a new Bagisto package
ecc generate:package --name=CustomPayment

# Create API endpoints
ecc generate:api --resource=Product

# Generate tests
ecc generate:tests --coverage=80

# Security scan
ecc security:scan --level=strict

# Performance analysis
ecc performance:analyze --threshold=2s
```

---

## Production Checklist

Before going live, ensure:

- [ ] SSL certificate is active and valid
- [ ] Database backups are configured
- [ ] File backups are configured
- [ ] Monitoring and alerts are set up
- [ ] Error tracking is configured (Sentry, Bugsnag, etc.)
- [ ] CDN is configured for static assets
- [ ] Email delivery is tested
- [ ] Payment gateways are tested
- [ ] All environment variables are set correctly
- [ ] Debug mode is disabled (`APP_DEBUG=false`)
- [ ] Caches are optimized
- [ ] Queue workers are running
- [ ] Scheduler is running
- [ ] Security headers are configured
- [ ] Rate limiting is enabled
- [ ] GDPR compliance is configured
- [ ] Terms of service and privacy policy are published
- [ ] Admin account is secured with 2FA
- [ ] Test orders are completed successfully

---

## Support & Resources

### Bagisto Documentation
- Official Docs: https://devdocs.bagisto.com
- GitHub: https://github.com/bagisto/bagisto
- Community Forum: https://forums.bagisto.com

### Dokploy Documentation
- Official Docs: https://docs.dokploy.com
- GitHub: https://github.com/dokploy/dokploy

### ECC Resources
- GitHub: https://github.com/affaan-m/ECC
- Documentation: See `/tmp/ecc-repo/docs/`
- Skills: See `/tmp/ecc-repo/skills/`

---

## License

This deployment guide is provided as-is for Bagisto deployment on Dokploy.

- Bagisto: MIT License
- ECC: MIT License
- This Guide: MIT License

---

**Last Updated**: May 30, 2026
**Version**: 1.0.0
**Bagisto Version**: 2.4.x
**Dokploy Version**: Latest
