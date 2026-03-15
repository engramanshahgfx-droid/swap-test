# VPS Deployment

This project is a Laravel 12 application with Vite-built frontend assets. Each environment must have its own `.env`, writable `storage` and `bootstrap/cache` directories, built frontend assets, and complete Firebase configuration.

## Recommended environment mapping

Use one app instance per subdomain:

- `dev-swap.tilalr.com` -> `/home/u710227726/domains/tilalr.com/public_html/dev-swap`
- `test-swap.tilalr.com` -> `/home/u710227726/domains/tilalr.com/public_html/test-swap`
- `main-swap.tilalr.com` -> `/home/u710227726/domains/tilalr.com/public_html/main-swap`

Important: for Laravel, the web server should point to each environment's `public` directory, not the app root.

Preferred document roots:

- `/home/u710227726/domains/tilalr.com/public_html/dev-swap/public`
- `/home/u710227726/domains/tilalr.com/public_html/test-swap/public`
- `/home/u710227726/domains/tilalr.com/public_html/main-swap/public`

If your hosting panel cannot point a domain to `public`, keep the full Laravel app in a private directory and expose only the `public` contents through the domain path.

## Server requirements

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8+ or MariaDB equivalent
- Git
- A process manager for queue workers such as `systemd` or `supervisor`

## First-time setup per environment

Example below uses `test-swap`. Repeat the same flow for `dev-swap` and `main-swap`.

### 1. Clone the repository

```bash
cd /home/u710227726/domains/tilalr.com/public_html
git clone https://github.com/engramanshahgfx-droid/swap-test.git test-swap
cd test-swap
```

### 2. Create the environment file

Start from the matching template:

```bash
cp .env.test.example .env
```

Then fill in real values for:

- `APP_KEY`
- database credentials
- mail credentials
- Firebase server credentials
- Firebase Vite frontend values
- SMS credentials if used

Generate the app key once:

```bash
php artisan key:generate
```

### 3. Install dependencies and build assets

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 4. Prepare writable directories

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 5. Run database migrations

```bash
php artisan migrate --force
```

If this environment needs seed data:

```bash
php artisan db:seed --force
```

### 6. Cache configuration for production-style environments

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

For `dev-swap`, prefer `php artisan optimize:clear` while actively changing config.

## Queue worker

This project uses `QUEUE_CONNECTION=database` by default. Run a worker for each environment.

Example `systemd` service for `test-swap`:

```ini
[Unit]
Description=Laravel Queue Worker - test-swap
After=network.target

[Service]
User=root
Group=root
Restart=always
ExecStart=/usr/bin/php /home/u710227726/domains/tilalr.com/public_html/test-swap/artisan queue:work --sleep=3 --tries=3 --timeout=120
WorkingDirectory=/home/u710227726/domains/tilalr.com/public_html/test-swap

[Install]
WantedBy=multi-user.target
```

Then enable it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-queue-test-swap
```

Use separate service names for `dev-swap` and `main-swap`.

## Update flow

Per environment:

```bash
cd /home/u710227726/domains/tilalr.com/public_html/test-swap
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Database plan

Use separate databases for each environment:

- `swap_dev`
- `swap_test`
- `swap_main`

Do not share one database between `dev`, `test`, and `main`.

## Firebase requirement

This application throws an exception outside local/testing if any required Firebase values are missing. Each environment must define:

- `FIREBASE_API_KEY`
- `FIREBASE_PROJECT_ID`
- `FIREBASE_AUTH_DOMAIN`
- `FIREBASE_SERVICE_ACCOUNT_KEY_PATH`
- all matching `VITE_FIREBASE_*` variables used by the frontend

Place the service account JSON file on the server and reference it with an absolute path such as:

```env
FIREBASE_SERVICE_ACCOUNT_KEY_PATH=/home/u710227726/domains/tilalr.com/shared/firebase/test-service-account.json
```

## Suggested release policy

- `dev-swap`: internal development
- `test-swap`: staging or QA
- `main-swap`: production

Typical values:

- `dev-swap`: `APP_ENV=development`, `APP_DEBUG=true`
- `test-swap`: `APP_ENV=staging`, `APP_DEBUG=false`
- `main-swap`: `APP_ENV=production`, `APP_DEBUG=false`

## Quick verification

Run these checks after each deployment:

```bash
php artisan about
php artisan migrate:status
php artisan queue:monitor default --max=100
```

Then verify in browser:

- `/admin/login`
- `/api/...` endpoints you depend on
- compiled assets load without Vite dev server
