<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupImageUpload extends Command
{
    protected $signature = 'setup:image-upload';
    protected $description = 'Setup or repair image upload directories and permissions';

    public function handle()
    {
        $this->line('');
        $this->info('🛠️  Setting up image upload system');
        $this->line('');

        // Step 1: Ensure directories exist
        $this->line('📁 Step 1: Creating directories...');
        $this->createDirectoryStructure();
        $this->line('');

        // Step 2: Fix permissions
        $this->line('🔐 Step 2: Setting permissions...');
        $this->fixPermissions();
        $this->line('');

        // Step 3: Create storage symlink
        $this->line('🔗 Step 3: Creating storage symlink...');
        $this->createStorageSymlink();
        $this->line('');

        // Step 4: Verify setup
        $this->line('✅ Step 4: Verifying setup...');
        $this->verifySetup();
        $this->line('');

        $this->info('✅ Image upload system is ready!');
        $this->line('');
    }

    private function createDirectoryStructure()
    {
        $dirs = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/public/trip-images'),
            storage_path('app/private'),
        ];

        foreach ($dirs as $dir) {
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->info("  ✅ Created: {$dir}");
            } else {
                $this->line("  ℹ️  Exists: {$dir}");
            }
        }
    }

    private function fixPermissions()
    {
        $paths = [
            storage_path('app') => '755',
            storage_path('app/public') => '755',
            storage_path('app/public/trip-images') => '755',
            storage_path('app/private') => '700',
        ];

        foreach ($paths as $path => $perms) {
            if (file_exists($path)) {
                $octal = octdec($perms);
                @chmod($path, $octal);
                $this->info("  ✅ Set {$path} to {$perms}");
            }
        }

        // On Linux systems, also try changing owner if running as root
        if (PHP_OS_FAMILY === 'Linux' && function_exists('posix_getuid')) {
            if (posix_getuid() === 0) {
                $this->line('');
                $this->info('  🔧 Running as root, setting ownership to www-data...');
                $this->changeOwnershipToWebServer();
            }
        }
    }

    private function changeOwnershipToWebServer()
    {
        $paths = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/public/trip-images'),
            bootstrap_path('cache'),
        ];

        $webUser = 'www-data';
        $webGroup = 'www-data';

        foreach ($paths as $path) {
            if (file_exists($path)) {
                @chown($path, $webUser);
                @chgrp($path, $webGroup);
                exec("chown -R {$webUser}:{$webGroup} {$path} 2>/dev/null");
                $this->info("  ✅ Set ownership for {$path}");
            }
        }
    }

    private function createStorageSymlink()
    {
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (is_link($linkPath)) {
            $this->line('  ℹ️  Symlink already exists');
            return;
        }

        if (file_exists($linkPath) && !is_link($linkPath)) {
            // Only remove if it's a directory, not a file
            if (is_dir($linkPath)) {
                $this->warn('  ⚠️  Directory exists at public/storage instead of symlink, removing...');
                @rmdir($linkPath);
                if (file_exists($linkPath)) {
                    $this->error('  ❌ Could not remove directory at public/storage');
                    return;
                }
            } else {
                $this->warn('  ⚠️  File exists at public/storage, removing...');
                File::delete($linkPath);
            }
        }

        try {
            // On Windows, use directory junction if symlinks aren't available
            if (PHP_OS_FAMILY === 'Windows') {
                exec("mklink /D \"{$linkPath}\" \"{$targetPath}\" 2>&1", $output, $return);
                if ($return === 0) {
                    $this->info('  ✅ Created symlink (Windows junction)');
                } else {
                    $this->warn('  ⚠️  Symlink creation failed on Windows (may require admin)');
                }
            } else {
                // On Linux/macOS, use symlink
                symlink($targetPath, $linkPath);
                $this->info('  ✅ Created symlink');
            }
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Could not create symlink: {$e->getMessage()}");
            $this->line('     You may need to run with elevated privileges');
        }
    }

    private function verifySetup()
    {
        $publicStoragePath = storage_path('app/public');
        $tripImagesPath = $publicStoragePath . '/trip-images';
        $symlinkPath = public_path('storage');

        // Check directories
        if (is_dir($publicStoragePath) && is_dir($tripImagesPath)) {
            $this->info('  ✅ Directories exist');
        } else {
            $this->error('  ❌ Directories missing');
            return;
        }

        // Check write permission
        if (is_writable($tripImagesPath)) {
            $this->info('  ✅ Write permissions OK');
        } else {
            $this->error('  ❌ Write permissions missing');
        }

        // Check symlink
        if (is_link($symlinkPath) || (PHP_OS_FAMILY === 'Windows' && is_dir($symlinkPath))) {
            $this->info('  ✅ Storage symlink OK');
        } else {
            $this->warn('  ⚠️  Storage symlink needs attention');
        }
    }
}
