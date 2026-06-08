<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DiagnoseImageUpload extends Command
{
    protected $signature = 'diagnose:image-upload';
    protected $description = 'Diagnose image upload configuration and permissions';

    public function handle()
    {
        $this->line('');
        $this->info('🔍 Image Upload Diagnostics');
        $this->line('');

        // Check storage path
        $this->line('📁 Storage Configuration:');
        $this->line('  Default Disk: ' . config('filesystems.default'));
        $this->line('  Public Disk Root: ' . config('filesystems.disks.public.root'));
        $this->line('  Public Disk URL: ' . config('filesystems.disks.public.url'));
        $this->line('');

        // Check if directories exist
        $this->line('🔧 Directory Status:');
        $publicStoragePath = storage_path('app/public');
        $tripImagesPath = $publicStoragePath . '/trip-images';

        $this->checkDirectory($publicStoragePath, 'storage/app/public');
        $this->checkDirectory($tripImagesPath, 'storage/app/public/trip-images');
        $this->line('');

        // Check symlink
        $this->line('🔗 Symlink Status:');
        $symlinkPath = public_path('storage');
        if (is_link($symlinkPath)) {
            $target = readlink($symlinkPath);
            $this->info("  ✅ Symlink exists: public/storage -> {$target}");
        } else {
            $this->error('  ❌ Symlink does not exist at public/storage');
            $this->line('     Run: php artisan storage:link');
        }
        $this->line('');

        // Test file write permissions
        $this->line('💾 Write Permission Test:');
        $this->testWritePermission($tripImagesPath);
        $this->line('');

        // Check Laravel Storage facade
        $this->line('📦 Laravel Storage Facade Test:');
        $this->testStorageFacade();
        $this->line('');

        // Check environment
        $this->line('🌍 Environment Check:');
        $this->line('  APP_URL: ' . config('app.url'));
        $this->line('  APP_DEBUG: ' . config('app.debug'));
        $this->line('  APP_ENV: ' . config('app.env'));
        $this->line('');

        $this->info('✅ Diagnostics complete');
    }

    private function checkDirectory($path, $label)
    {
        if (File::isDirectory($path)) {
            $this->info("  ✅ {$label} exists");

            // Check permissions
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $this->line("     Permissions: {$perms}");

            // Check if writable
            if (is_writable($path)) {
                $this->info('     Writable: Yes');
            } else {
                $this->error('     Writable: No');
            }

            // Count files
            $files = count(glob($path . '/*'));
            $this->line("     Files: {$files}");
        } else {
            $this->error("  ❌ {$label} does not exist");
            $this->line("     Create with: mkdir -p {$path}");
        }
    }

    private function testWritePermission($path)
    {
        $testFile = $path . '/.test_write_' . uniqid();

        if (!is_dir($path)) {
            $this->error('  ❌ Directory does not exist, cannot test write');
            return;
        }

        try {
            File::put($testFile, 'test');
            File::delete($testFile);
            $this->info('  ✅ Write permission test passed');
        } catch (\Exception $e) {
            $this->error('  ❌ Write permission test failed: ' . $e->getMessage());
        }
    }

    private function testStorageFacade()
    {
        try {
            $testFile = 'trip-images/.facade_test_' . uniqid() . '.txt';
            Storage::disk('public')->put($testFile, 'test content');

            if (Storage::disk('public')->exists($testFile)) {
                $this->info('  ✅ Storage::put() works');
                $url = Storage::disk('public')->url($testFile);
                $this->line("     URL: {$url}");
                Storage::disk('public')->delete($testFile);
            } else {
                $this->error('  ❌ File was written but cannot be read back');
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Storage test failed: ' . $e->getMessage());
        }
    }
}
