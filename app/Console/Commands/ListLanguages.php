<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListLanguages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:list';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'List all available application languages and their status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $supportedLocales = ['en', 'ar'];
        $langDir = resource_path('lang');
        
        $this->info('Available Languages:');
        $this->line('');

        $headers = ['Code', 'Name', 'Direction', 'Status'];
        $rows = [];

        foreach ($supportedLocales as $locale) {
            $path = "{$langDir}/{$locale}";
            $exists = is_dir($path);
            $filesCount = $exists ? count(scandir($path)) - 2 : 0; // -2 for . and ..

            $name = match($locale) {
                'en' => 'English',
                'ar' => 'العربية (Arabic)',
            };

            $direction = match($locale) {
                'en' => 'LTR',
                'ar' => 'RTL',
            };

            $status = $exists ? "✓ Active ({$filesCount} files)" : '✗ Not Available';

            $rows[] = [
                $locale,
                $name,
                $direction,
                $status,
            ];
        }

        $this->table($headers, $rows);

        $this->line('');
        $this->info('Current Locale: ' . config('app.locale'));
        $this->info('Fallback Locale: ' . config('app.fallback_locale'));

        $this->line('');
        $this->line('Commands:');
        $this->line('  To set language: php artisan tinker');
        $this->line('  > app()->setLocale("ar")');
        $this->line('  > session(["locale" => "ar"])');
    }
}
