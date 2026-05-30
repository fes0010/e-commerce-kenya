# E-Commerce Kenya 🇰🇪

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Bagisto](https://img.shields.io/badge/Bagisto-2.4.x-orange.svg)](https://bagisto.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg)](https://mysql.com)

> **Full-featured e-commerce platform for Kenya** built on Bagisto 2.4.x with multi-vendor support, payment gateway integration, and production-ready Dokploy deployment configuration.

---

## 🚀 Features

### Core E-Commerce Features
- ✅ **Multi-Vendor Marketplace** - Support for multiple sellers
- ✅ **Product Management** - Simple, configurable, grouped, bundled, and booking products
- ✅ **Order Management** - Complete order lifecycle management
- ✅ **Customer Management** - Customer accounts, wishlists, and reviews
- ✅ **Inventory Management** - Stock tracking and management
- ✅ **Category Management** - Nested category tree structure
- ✅ **Cart & Checkout** - Full-featured shopping cart and checkout flow

### Payment Gateways
- 💳 **PayPal** - PayPal Standard and Express Checkout
- 💳 **Stripe** - Credit card processing
- 💳 **Razorpay** - Indian payment gateway
- 💳 **PayU** - Payment gateway for emerging markets
- 💳 **Cash on Delivery** - COD support
- 💳 **Money Transfer** - Bank transfer support

### Shipping Methods
- 📦 **Flat Rate Shipping**
- 📦 **Free Shipping**
- 📦 **Table Rate Shipping**
- 📦 **Custom Shipping Methods**

### Marketing & SEO
- 🎯 **Cart Price Rules** - Promotional discounts
- 🎯 **Catalog Price Rules** - Bulk pricing rules
- 🎯 **SEO Optimization** - Meta tags, URL rewrites, sitemap
- 🎯 **Email Marketing** - Newsletter subscriptions
- 🎯 **Social Sharing** - Social media integration

### Advanced Features
- 🤖 **AI Integration** - MagicAI features powered by Laravel AI SDK
- 🔍 **Elasticsearch** - Fast product search
- 📊 **Analytics & Reporting** - Sales reports and analytics
- 🌍 **Multi-Language** - 21 languages supported
- 💱 **Multi-Currency** - Multiple currency support
- 📱 **Responsive Design** - Mobile-friendly interface
- 🔐 **Security** - GDPR compliance, secure payments
- 📧 **Email Notifications** - Order confirmations, shipping updates

---

## 📋 Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Redis**: 7.x (for caching and sessions)
- **Elasticsearch**: 7.17 or higher (for search)
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher

### PHP Extensions
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- Filter
- GD
- Hash
- Intl
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Session
- Tokenizer
- XML

---

## 🛠️ Installation

### Local Development

1. **Clone the repository**
```bash
git clone https://github.com/fes0010/e-commerce-kenya.git
cd e-commerce-kenya
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node.js dependencies**
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Update database configuration in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=your_password
```

6. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Build frontend assets**
```bash
# Admin panel
cd packages/Webkul/Admin && npm install && npm run build

# Shop frontend
cd ../Shop && npm install && npm run build

# Installer
cd ../Installer && npm install && npm run build
```

8. **Start development server**
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

---

## 🐳 Dokploy Deployment

This project includes production-ready Dokploy deployment configuration.

### Quick Deploy

1. **Review deployment documentation**
```bash
cat DOKPLOY-DEPLOYMENT.md
```

2. **Configure environment variables**
```bash
cp .env.dokploy .env
# Edit .env with your production values
```

3. **Deploy to Dokploy**
- Create new application in Dokploy
- Connect this GitHub repository
- Set deployment file: `docker-compose.dokploy.yml`
- Configure environment variables
- Deploy!

### Deployment Files
- `docker-compose.dokploy.yml` - Docker Compose configuration
- `.env.dokploy` - Production environment template
- `DOKPLOY-DEPLOYMENT.md` - Complete deployment guide
- `routes/health.php` - Health check endpoints

### Health Check Endpoints
- `/health` - Overall health status
- `/health/ready` - Readiness probe
- `/health/live` - Liveness probe

---

## 📚 Documentation

### Project Documentation
- [Deployment Guide](DOKPLOY-DEPLOYMENT.md) - Complete Dokploy deployment instructions
- [Agent Instructions](AGENTS.md) - Development guidelines and architecture
- [Changelog](CHANGELOG.md) - Version history and changes
- [Contributing](CONTRIBUTING.md) - How to contribute
- [Security](SECURITY.md) - Security policies
- [Upgrade Guide](UPGRADE.md) - Version upgrade instructions

### Bagisto Documentation
- [Official Documentation](https://devdocs.bagisto.com)
- [API Documentation](https://devdocs.bagisto.com/2.x/api/)
- [Package Development](https://devdocs.bagisto.com/2.x/packages/)

---

## 🏗️ Architecture

### Package Structure
```
packages/Webkul/
├── Admin/              # Admin panel
├── Shop/               # Customer storefront
├── Core/               # Core functionality
├── Product/            # Product management
├── Sales/              # Order management
├── Checkout/           # Cart and checkout
├── Customer/           # Customer management
├── Category/           # Category management
├── Attribute/          # EAV attributes
├── Payment/            # Payment methods
├── Paypal/             # PayPal integration
├── Stripe/             # Stripe integration
├── Razorpay/           # Razorpay integration
├── PayU/               # PayU integration
├── Shipping/           # Shipping methods
├── Inventory/          # Stock management
├── CartRule/           # Cart promotions
├── CatalogRule/        # Catalog pricing
├── Tax/                # Tax calculation
├── DataGrid/           # Admin data tables
├── DataTransfer/       # Import/export
├── CMS/                # Content management
├── Marketing/          # SEO and marketing
├── Theme/              # Theme management
├── MagicAI/            # AI features
├── Notification/       # Notifications
├── BookingProduct/     # Booking products
├── User/               # Admin users
├── Installer/          # Installation wizard
├── SocialLogin/        # OAuth login
├── Sitemap/            # XML sitemap
├── GDPR/               # GDPR compliance
└── RMA/                # Returns management
```

### Technology Stack
- **Backend**: Laravel 12.x, PHP 8.2+
- **Frontend**: Vue.js 3, Tailwind CSS, Vite
- **Database**: MySQL 8.0+
- **Cache**: Redis 7.x
- **Search**: Elasticsearch 7.17+
- **Queue**: Redis
- **Session**: Redis
- **File Storage**: Local / S3

---

## 🧪 Testing

### Run Tests
```bash
# All tests
php artisan test --compact

# Specific package
php artisan test --compact packages/Webkul/Admin/tests

# With coverage
php artisan test --coverage
```

### Code Style
```bash
# Fix code style
vendor/bin/pint

# Check only
vendor/bin/pint --test
```

### E2E Tests
```bash
# Admin E2E tests
cd packages/Webkul/Admin
npx playwright test --config=tests/e2e-pw/playwright.config.ts

# Shop E2E tests
cd packages/Webkul/Shop
npx playwright test --config=tests/e2e-pw/playwright.config.ts
```

---

## 🔧 Development

### Commands
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Index products
php artisan indexer:index

# Run queue worker
php artisan queue:work

# Run scheduler
php artisan schedule:work
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Fresh install
php artisan migrate:fresh --seed
```

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Workflow
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Run `vendor/bin/pint` before committing

---

## 🔐 Security

If you discover a security vulnerability, please email security@example.com. All security vulnerabilities will be promptly addressed.

See [SECURITY.md](SECURITY.md) for more information.

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

### Third-Party Licenses
- **Bagisto**: MIT License
- **Laravel**: MIT License
- **Vue.js**: MIT License

---

## 🙏 Acknowledgments

- [Bagisto](https://bagisto.com) - The amazing e-commerce framework
- [Laravel](https://laravel.com) - The PHP framework
- [Webkul](https://webkul.com) - Bagisto creators
- [ECC](https://github.com/affaan-m/ECC) - AI-assisted development tools

---

## 📞 Support

- **Documentation**: [DOKPLOY-DEPLOYMENT.md](DOKPLOY-DEPLOYMENT.md)
- **Issues**: [GitHub Issues](https://github.com/fes0010/e-commerce-kenya/issues)
- **Discussions**: [GitHub Discussions](https://github.com/fes0010/e-commerce-kenya/discussions)
- **Bagisto Forums**: [forums.bagisto.com](https://forums.bagisto.com)

---

## 🗺️ Roadmap

- [ ] M-Pesa payment integration (Kenya)
- [ ] Airtel Money integration (Kenya)
- [ ] Kenya Post shipping integration
- [ ] KRA tax compliance
- [ ] Mobile app (React Native)
- [ ] Progressive Web App (PWA)
- [ ] Advanced analytics dashboard
- [ ] Multi-warehouse support
- [ ] Subscription products
- [ ] Affiliate program

---

## 📊 Project Status

- **Version**: 2.4.x
- **Status**: Active Development
- **Last Updated**: May 30, 2026
- **Maintainer**: [@fes0010](https://github.com/fes0010)

---

<div align="center">

**Made with ❤️ for Kenya**

[Report Bug](https://github.com/fes0010/e-commerce-kenya/issues) · [Request Feature](https://github.com/fes0010/e-commerce-kenya/issues) · [Documentation](DOKPLOY-DEPLOYMENT.md)

</div>
