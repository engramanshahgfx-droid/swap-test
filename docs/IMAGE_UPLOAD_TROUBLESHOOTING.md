# Image Upload Troubleshooting Guide

## Problem Description
When users upload images via the `/api/publish-trip` endpoint, only the image path/URL is saved to the database, but the actual image file is not stored on the filesystem. This results in broken image links.

## Root Causes

### 1. **Missing `trip-images` Directory**
The upload destination directory doesn't exist on the server.

### 2. **Permission Issues**
The web server user (typically `www-data` on Linux) doesn't have write permissions to the storage directory.

### 3. **Broken Storage Symlink**
The `public/storage` symlink is missing or broken, preventing access to stored files via web.

### 4. **Incorrect Filesystem Configuration**
The `.env` or config has incorrect disk settings.

---

## Solution Steps

### Step 1: Run the Setup Command (Recommended)

On the production server, SSH in and run:

```bash
cd /home/u710227726/domains/flightswap.co/public_html
php artisan setup:image-upload
```

This will automatically:
- Create all necessary directories
- Fix file permissions
- Create the storage symlink
- Verify the setup

### Step 2: Manual Setup (if command fails)

If the command doesn't work, do this manually:

#### A. Create Directories
```bash
mkdir -p storage/app/public/trip-images
mkdir -p storage/app/private
chmod 755 storage/app
chmod 755 storage/app/public
chmod 755 storage/app/public/trip-images
chmod 700 storage/app/private
```

#### B. Set Ownership (if running as root)
```bash
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

#### C. Create Storage Symlink
```bash
php artisan storage:link
```

If this fails, try removing the old one first:
```bash
rm -f public/storage
php artisan storage:link
```

### Step 3: Verify Configuration

Run the diagnostic command:
```bash
php artisan diagnose:image-upload
```

All checks should show ✅ marks. If you see ❌ or ⚠️, address those issues.

### Step 4: Check Logs

After attempting an upload, check the application logs for detailed error messages:

```bash
tail -f storage/logs/laravel.log
```

Look for entries containing "Image upload" - these will show exactly what went wrong.

---

## Expected Behavior After Fix

### Frontend Upload Flow
1. User selects image in Vue component
2. Frontend sends `FormData` with `image` file to `/api/publish-trip`
3. Backend receives file, validates it
4. File is stored to `storage/app/public/trip-images/`
5. Path is saved to database (e.g., `trip-images/abc123.jpg`)
6. `Storage::url()` generates public URL
7. Client receives: `https://flightswap.co/storage/trip-images/abc123.jpg`

### Postman/JSON Upload Flow
1. Send JSON request with `image_path` as URL: `https://example.com/image.jpg`
2. Backend receives URL, attempts to download it
3. Downloaded file is stored to `storage/app/public/trip-images/`
4. Path is saved to database
5. Public URL is generated and returned

---

## Debugging

### Check if file exists after upload
```bash
ls -la storage/app/public/trip-images/
```

### Test write permission
```bash
touch storage/app/public/trip-images/.test
rm storage/app/public/trip-images/.test
```

### Verify symlink
```bash
ls -la public/storage
```

Should output something like:
```
lrwxrwxrwx 1 www-data www-data 23 Jun  8 12:00 storage -> /home/u710227726/domains/flightswap.co/public_html/storage/app/public
```

### Test Laravel Storage
```bash
php artisan tinker
>>> Storage::disk('public')->put('test.txt', 'test content');
>>> Storage::disk('public')->url('test.txt');
>>> Storage::disk('public')->delete('test.txt');
```

---

## Common Issues & Fixes

### Issue: "The [public/storage] link already exists"
**Fix:** The symlink exists but may be broken. Try:
```bash
rm -f public/storage
php artisan storage:link
```

### Issue: Permission denied when uploading
**Fix:** Ensure web server user owns the storage directory:
```bash
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
```

### Issue: Files uploaded but images don't load in browser
**Fix:** Check that:
1. The symlink exists: `ls -la public/storage`
2. The file exists: `ls -la storage/app/public/trip-images/`
3. File permissions allow reading: `chmod 644 storage/app/public/trip-images/*`

### Issue: "call to undefined method Http"
**Fix:** Ensure Laravel version supports `Http` facade. Run:
```bash
composer require laravel/http-client
```

---

## Environment Configuration

Ensure `.env` has these settings:

```env
FILESYSTEM_DISK=local
APP_URL=https://flightswap.co
```

**Important:** `FILESYSTEM_DISK=local` is correct for local file storage. Don't change it to "public" - that's the disk name, not a mode.

---

## After Fix: Clear Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Monitoring

Monitor these logs for upload issues:
```bash
tail -f storage/logs/laravel.log | grep -i image
```

This will show all image-related operations including successful uploads and errors.
