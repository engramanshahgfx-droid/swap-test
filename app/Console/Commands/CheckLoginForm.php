<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckLoginForm extends Command
{
    protected $signature = 'test:check-form';
    protected $description = 'Check login form';

    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        
        $this->info('Fetching login form...');
        try {
            $response = $client->get('http://localhost:8000/admin/login');
            $html = (string) $response->getBody();
            
            $this->info("Response length: " . strlen($html) . " bytes");
            $this->info("First 1000 chars:");
            $this->line(substr($html, 0, 1000));
            
            $this->info("\n\nSearching for form elements...");
            if (preg_match_all('/<input[^>]*name="([^"]*)"[^>]*>/i', $html, $matches)) {
                $this->info("Found input fields:");
                foreach($matches[1] as $name) {
                    $this->line("  - " . $name);
                }
            } else {
                $this->error("No input fields found!");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
