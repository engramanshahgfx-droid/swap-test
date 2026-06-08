# Image Upload Fix - Production Deployment

## Changes Made

### Backend Updates
1. **Centralized image storage logic** in `TripController`
   - New helper method: `resolvePublishedTripImagePath()`
   - Handles file uploads, remote URLs, and provided paths
   - Replaced 3 duplicate blocks with single centralized method

2. **Enhanced logging** for debugging
   - All upload attempts are now logged
   - File validation errors are captured
   - Remote URL download attempts are recorded

3. **Added diagnostics and setup tools**
   - `php artisan diagnose:image-upload` - Check current status
   - `php artisan setup:image-upload` - Automatically fix issues
   - Full permissions and directory verification

### Files Modified
- `app/Http/Controllers/Api/TripController.php` - Refactored + logging
- `app/Http/Requests/Api/PublishTripRequest.php` - Unchanged
- `config/filesystems.php` - Unchanged (already correct)

### Files Added
- `app/Console/Commands/DiagnoseImageUpload.php` - Diagnostic tool
- `app/Console/Commands/SetupImageUpload.php` - Setup/repair tool
- `docs/IMAGE_UPLOAD_TROUBLESHOOTING.md` - Complete troubleshooting guide
- `scripts/fix-image-upload.sh` - Bash deployment script

---

## Deployment Steps

### 1. Pull Latest Code
```bash
cd /home/u710227726/domains/flightswap.co/public_html
git pull origin main
```

### 2. Install/Update Dependencies (if needed)
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Run Setup Command
```bash
php artisan setup:image-upload
```

This automatically:
- Creates all required directories
- Sets correct permissions
- Creates/fixes storage symlink
- Verifies everything works

### 4. Clear Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 5. Verify
```bash
php artisan diagnose:image-upload
```

All checks should show ✅

---

## Troubleshooting on Production

If you encounter issues:

### Check Current Status
```bash
php artisan diagnose:image-upload
```

### View Recent Errors
```bash
tail -f storage/logs/laravel.log | grep -i image
```

### Manual Directory Creation
```bash
mkdir -p storage/app/public/trip-images
chmod 755 storage/app/public/trip-images
chown -R www-data:www-data storage/
```

### Reset Symlink
```bash
rm -f public/storage
php artisan storage:link
```

### Test Upload
```bash
php artisan tinker
>>> Storage::disk('public')->put('test.jpg', file_get_contents('https://via.placeholder.com/100'));
>>> Storage::disk('public')->url('test.jpg');
```

---

## Expected Results

After deployment:

✅ Images uploaded via the Vue frontend are saved to `storage/app/public/trip-images/`  
✅ Image paths are correctly stored in the database  
✅ Generated URLs are accessible at `https://flightswap.co/storage/trip-images/{filename}`  
✅ Remote image URLs (from Postman tests) are downloaded and saved locally  
✅ All logs show successful image operations  

---

## Files Not Modified

These files work correctly as-is:
- `routes/api.php` - Route definitions are correct
- `config/filesystems.php` - Disk configuration is correct
- `resources/js/pages/TripDetails.vue` - Frontend upload is correct
- `resources/js/services/api.js` - API client is correct

The issue was **missing directories and permissions**, plus duplicate code that has now been centralized.

---

## Monitoring

After deployment, monitor uploads with:
```bash
php artisan tinker

# List uploaded images
>>> Storage::disk('public')->listContents('trip-images');

# Test a new upload
>>> Storage::disk('public')->put('trip-images/test.txt', 'test');
>>> Storage::disk('public')->url('trip-images/test.txt');
>>> Storage::disk('public')->delete('trip-images/test.txt');
```

Check logs continuously:
```bash
tail -f storage/logs/laravel.log
```
