<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:validate {--missing : Show missing translations} {--verbose : Show detailed output}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Validate translation files and check for missing keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $langDir = resource_path('lang');
        $locales = ['en', 'ar'];
        
        $this->info('Validating translation files...');
        $this->line('');

        $translations = [];
        
        // Load all translation files
        foreach ($locales as $locale) {
            $localePath = "{$langDir}/{$locale}";
            
            if (!is_dir($localePath)) {
                $this->warn("Language directory not found: {$locale}");
                continue;
            }

            $translations[$locale] = [];
            
            // Load all PHP files in the language directory
            $files = File::files($localePath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $filename = $file->getFilenameWithoutExtension();
                    $content = include $file->getPathname();
                    
                    if (is_array($content)) {
                        $translations[$locale][$filename] = array_keys($content);
                    }
                }
            }
        }

        if (empty($translations['en'])) {
            $this->error('No translation files found!');
            return 1;
        }

        // Find discrepancies
        $missing = [];
        $this->line('Checking for missing translations...');
        $this->line('');

        foreach ($translations['en'] as $file => $enKeys) {
            if (!isset($translations['ar'][$file])) {
                $this->warn("File missing in Arabic: {$file}");
                $missing[$file] = $enKeys;
                continue;
            }

            $arKeys = $translations['ar'][$file];
            $missingInAr = array_diff($enKeys, $arKeys);
            $extraInAr = array_diff($arKeys, $enKeys);

            if ($missingInAr || $extraInAr) {
                if ($missingInAr) {
                    $this->error("Missing keys in {$file} (Arabic):");
                    foreach ($missingInAr as $key) {
                        $this->line("  - {$key}");
                        $missing[$file][] = $key;
                    }
                }

                if ($extraInAr && $this->option('verbose')) {
                    $this->warn("Extra keys in {$file} (Arabic):");
                    foreach ($extraInAr as $key) {
                        $this->line("  - {$key}");
                    }
                }
            } else {
                $this->info("✓ {$file} - All keys present");
            }
        }

        $this->line('');
        $this->info('Validation complete!');

        if ($missing && $this->option('missing')) {
            $this->warn('Missing translations summary:');
            foreach ($missing as $file => $keys) {
                $this->line("{$file}: " . count($keys) . " keys");
            }
        }

        return count($missing) > 0 ? 1 : 0;
    }
}
