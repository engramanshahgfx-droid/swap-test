# flightSwap  Multi-Environment Deployment Guide

**Last Updated:** March 15, 2026  
**Status:** ✅ All environments live and operational

---

## 🌍 Environment Overview

flightSwap  is deployed across **three independent environments** on a VPS at `72.62.190.247` (Hostinger Ubuntu 24.04 LTS).

| Environment | URL | Purpose | Database | Status |
|---|---|---|---|---|
| **dev-swap** | http://dev-swap.tilalr.com | Development & Testing | `swap_dev` | ✅ Live |
| **test-swap** | http://test-swap.tilalr.com | QA & Staging | `swap_test` | ✅ Live |
| **main-swap** | http://main-swap.tilalr.com | Production | `swap_main` | ✅ Live |

Each environment has:
- Isolated database
- Separate `.env` configuration
- Independent Laravel application instance
- Dedicated nginx vhost
- Own storage and logs

---

## 🔐 Access Credentials

### VPS SSH Access
```bash
Host: 72.62.190.247
User: root
Password: Aa12345678@12Aa12345678@12
Developer User: u710227726
Developer Password: Aa112233@@
```

### Admin Login (All Environments)
```
Email: admin@flightSwap .com
Password: Password
```

---

## 📁 Directory Structure

```
/home/u710227726/domains/tilalr.com/public_html/
├── dev-swap/           # Development environment
│   ├── public/         # Web root (nginx serves this)
│   ├── .env            # Development config
│   ├── storage/
│   └── ...
├── test-swap/          # Testing environment
│   ├── public/
│   ├── .env
│   └── ...
└── main-swap/          # Production environment
    ├── public/
    ├── .env
    └── ...
```

---

## 🚀 Deploying Code Updates

### Pull Latest Code to All Environments

```bash
# SSH to VPS
ssh root@72.62.190.247

# Deploy to dev-swap
cd /home/u710227726/domains/tilalr.com/public_html/dev-swap
git pull origin main
composer install --no-dev
npm install
npm run build
php artisan optimize:clear

# Deploy to test-swap
cd /home/u710227726/domains/tilalr.com/public_html/test-swap
git pull origin main
composer install --no-dev
npm install
npm run build
php artisan optimize:clear

# Deploy to main-swap
cd /home/u710227726/domains/tilalr.com/public_html/main-swap
git pull origin main
composer install --no-dev
npm install
npm run build
php artisan optimize:clear
```

### Deployment Checklist
- [ ] Code committed and pushed to GitHub `main` branch
- [ ] Pull latest code on VPS
- [ ] Run `composer install`
- [ ] Run `npm install && npm run build`
- [ ] Clear cache: `php artisan optimize:clear`
- [ ] Test `/admin` dashboard loads
- [ ] Test API endpoints
- [ ] Verify database queries work
- [ ] Check error logs: `tail -f storage/logs/laravel.log`

---

## 🗄️ Database Management

### Database Credentials

**Development (swap_dev)**
```
User: swap_dev_user
Password: Swap@Dev123
Database: swap_dev
```

**Testing (swap_test)**
```
User: swap_test_user
Password: Swap@Test123
Database: swap_test
```

**Production (swap_main)**
```
User: swap_main_user
Password: Swap@Main123
Database: swap_main
```

### Run Migrations

```bash
# SSH to dev-swap, then:
cd /home/u710227726/domains/tilalr.com/public_html/dev-swap
php artisan migrate --force

# Same for test-swap and main-swap
```

### Seed Database

```bash
# Full seed (users, airlines, positions, etc.)
php artisan db:seed --force

# Seed specific seeder
php artisan db:seed --seeder=UserSeeder --force
```

### Database Backup

```bash
# Create backup
mysqldump -u swap_dev_user -p swap_dev > backup_dev_$(date +%Y%m%d).sql

# Restore from backup
mysql -u swap_dev_user -p swap_dev < backup_dev_20260315.sql
```

---

## 📊 Admin Dashboard

All three environments have a **custom admin dashboard** with:

- Real-time statistics (users, flights, airlines, reports)
- Activity charts and trends
- Recent swap requests and reports
- User growth analytics
- Airline activity heatmap

### Accessing Dashboards
- Dev: http://dev-swap.tilalr.com/admin
- Test: http://test-swap.tilalr.com/admin
- Main: http://main-swap.tilalr.com/admin

**Note:** Dashboard redirects from `/admin` to `/dashboard` after login.

---

## 🔧 Environment Variables

Each environment has its own `.env` file with:

```bash
# Environment name
APP_ENV=development|staging|production

# Debug mode (dev=true, test=false, main=false)
APP_DEBUG=true|false

# Database config (per-environment)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=swap_dev|swap_test|swap_main
DB_USERNAME=swap_dev_user|swap_test_user|swap_main_user
DB_PASSWORD=...

# Cache prefix (prevents cache collision)
CACHE_PREFIX=dev_|test_|main_

# Firebase (shared, but environment-aware)
FIREBASE_API_KEY=...
FIREBASE_PROJECT_ID=...
FIREBASE_AUTH_DOMAIN=...
FIREBASE_SERVICE_ACCOUNT_KEY_PATH=storage/firebase/service-account.json
```

### Updating Environment Variables

```bash
# SSH to VPS
ssh root@72.62.190.247

# Edit dev-swap config
nano /home/u710227726/domains/tilalr.com/public_html/dev-swap/.env

# After editing, clear cache
php artisan optimize:clear
```

---

## 🌐 Nginx Configuration

Nginx configs are located at `/etc/nginx/conf.d/`:

```bash
/etc/nginx/conf.d/
├── dev-swap.tilalr.com.conf
├── test-swap.tilalr.com.conf
└── main-swap.tilalr.com.conf
```

Each config:
- Routes HTTP requests to Laravel `public/` directory
- Forwards `.php` files to PHP-FPM (127.0.0.1:9000)
- Handles URL rewriting for Laravel routing

### Reload Nginx After Config Changes
```bash
sudo nginx -t     # Test config
sudo systemctl reload nginx
```

---

## 🧪 Testing API Endpoints

### Login (All Environments)
```bash
curl -X POST http://dev-swap.tilalr.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@flightSwap .com",
    "password": "Password123"
  }'
```

### Get Users
```bash
curl -X GET http://dev-swap.tilalr.com/api/users \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### View Logs (Real-time)
```bash
ssh root@72.62.190.247
tail -f /home/u710227726/domains/tilalr.com/public_html/dev-swap/storage/logs/laravel.log
```

---

## 🐛 Troubleshooting

### Dashboard Not Loading
```bash
# Clear cache and compiled files
php artisan optimize:clear

# Check Laravel log
tail -f storage/logs/laravel.log

# Check nginx error log
sudo tail -f /var/log/nginx/error.log
```

### Database Connection Error
```bash
# Verify .env credentials
cat /home/u710227726/domains/tilalr.com/public_html/dev-swap/.env | grep DB_

# Test database connection
mysql -h 127.0.0.1 -u swap_dev_user -p -e "SELECT 1;"
```

### 500 Internal Server Error
```bash
# Check Laravel error log
tail -50 storage/logs/laravel.log

# Check nginx access/error logs
sudo tail -50 /var/log/nginx/error.log

# Verify file permissions
ls -la /home/u710227726/domains/tilalr.com/public_html/dev-swap/storage
```

### Nginx Not Responding
```bash
# Check nginx status
sudo systemctl status nginx

# Restart nginx
sudo systemctl restart nginx

# Test nginx config
sudo nginx -t
```

---

## 📋 Deployment Workflow

### Standard Deployment Process

1. **Local Development**
   - Make code changes locally
   - Test locally: `php artisan serve`
   - Commit: `git commit -m "Feature description"`

2. **Push to GitHub**
   ```bash
   git push origin main
   ```

3. **Deploy to dev-swap (Test)**
   ```bash
   ssh root@72.62.190.247
   cd /home/u710227726/domains/tilalr.com/public_html/dev-swap
   git pull origin main
   php artisan optimize:clear
   ```
   - Test in browser: http://dev-swap.tilalr.com/admin
   - Check logs for errors

4. **Deploy to test-swap (QA)**
   ```bash
   cd /home/u710227726/domains/tilalr.com/public_html/test-swap
   git pull origin main
   php artisan optimize:clear
   ```
   - Team QA testing

5. **Deploy to main-swap (Production)**
   ```bash
   cd /home/u710227726/domains/tilalr.com/public_html/main-swap
   git pull origin main
   php artisan optimize:clear
   ```
   - Live for end users

---

## ⚙️ Key Services & Versions

| Service | Version | Status |
|---|---|---|
| PHP | 8.3/8.4 | ✅ Running |
| Laravel | 12.53.0 | ✅ Latest |
| Filament | 3.3.49 | ✅ Latest |
| Livewire | 3.7.11 | ✅ Latest |
| PostgreSQL/MySQL | 8.0+ | ✅ Running |
| Nginx | 1.24.0 | ✅ Running |
| Node.js | 18+ | ✅ Installed |
| Vite | 7.3.0 | ✅ Latest |

---

## 📞 Monitoring & Alerts

### Health Check URLs
```bash
# Test all three
curl -I http://dev-swap.tilalr.com/admin
curl -I http://test-swap.tilalr.com/admin
curl -I http://main-swap.tilalr.com/admin
```

All should return `HTTP 302` (redirect to login) or `HTTP 200` (if logged in).

### Log Files Location
```bash
/home/u710227726/domains/tilalr.com/public_html/[ENV]/storage/logs/laravel.log
```

### Nginx Access/Error Logs
```bash
/var/log/nginx/access.log
/var/log/nginx/error.log
```

---

## 🔄 Database Sync Between Environments

### Copy dev data to test-swap
```bash
# Backup dev
mysqldump -u swap_dev_user -p swap_dev > dev_backup.sql

# Restore to test
mysql -u swap_test_user -p swap_test < dev_backup.sql
```

### Copy test data to main
```bash
# Backup test
mysqldump -u swap_test_user -p swap_test > test_backup.sql

# Restore to main
mysql -u swap_main_user -p swap_main < test_backup.sql
```

---

## 📝 Notes

- Each environment is **completely isolated** - changes in one don't affect others
- Use **dev-swap** for testing new features
- Use **test-swap** for QA and staging releases
- Use **main-swap** for production (live users)
- Always test on dev-swap before pushing to test-swap or main-swap
- Database backups recommended before major updates
- Check error logs immediately if something breaks

---

## 🚨 Emergency Procedures

### If main-swap is down (Production Emergency)

1. **Check service status**
   ```bash
   ssh root@72.62.190.247
   sudo systemctl status nginx
   sudo systemctl status php-fpm
   ```

2. **Restart services**
   ```bash
   sudo systemctl restart nginx
   sudo systemctl restart php-fpm
   ```

3. **Check error logs**
   ```bash
   sudo tail -100 /var/log/nginx/error.log
   tail -100 /home/u710227726/domains/tilalr.com/public_html/main-swap/storage/logs/laravel.log
   ```

4. **Clear cache if needed**
   ```bash
   cd /home/u710227726/domains/tilalr.com/public_html/main-swap
   php artisan optimize:clear
   ```

5. **Restore from backup if corrupted**
   ```bash
   // Restore database from latest backup
   mysql -u swap_main_user -p swap_main < main_backup_latest.sql
   ```

---

## 📧 Support

For issues or questions:
- Check error logs first
- Review this documentation
- Contact the development team
- Check GitHub issues and commits

---

**Document Version:** 1.0  
**Last Updated:** March 15, 2026  
**Created By:** GitHub Copilot  
**Status:** Production Ready ✅
