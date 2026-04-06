<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use Resend\Client;
use Resend\Transporters\HttpTransporter;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;

class EmailOtpService
{
    private ?string $lastError = null;

    /**
     * Send OTP via email to user
     */
    public function sendOtp(string $email, string $otp): bool
    {
        $this->lastError = null;
        $html = $this->getOtpEmailTemplate($otp, $email);

        // Use Resend when configured; fallback to Laravel mailer if unavailable.
        if (!$this->hasResendKey()) {
            return $this->sendViaLaravelMailer($email, $otp, $html);
        }

        try {
            $resend = $this->createResendClient();
            
            $response = $resend->emails->send([
                'from' => $this->resolveFromAddress(),
                'to' => $email,
                'subject' => '🔐 Your CrewSwap OTP Code: ' . $otp,
                'html' => $html,
            ]);
            
            \Log::info('OTP sent via Resend', ['email' => $email, 'otp' => $otp, 'response' => (array)$response]);
            return $response ? true : false;
        } catch (\Exception $e) {
            $this->lastError = 'Resend: ' . $e->getMessage();
            \Log::error('Failed to send OTP email via Resend', ['email' => $email, 'error' => $e->getMessage()]);

            return $this->sendViaLaravelMailer($email, $otp, $html);
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function createResendClient(): Client
    {
        $apiKey = ApiKey::from((string) config('services.resend.key'));
        $baseUri = BaseUri::from(getenv('RESEND_BASE_URL') ?: 'api.resend.com');
        $headers = Headers::withAuthorization($apiKey);

        $guzzleOptions = [];

        if (app()->environment('local') && PHP_OS_FAMILY === 'Windows') {
            $guzzleOptions['verify'] = false;
        }

        $client = new GuzzleClient($guzzleOptions);
        $transporter = new HttpTransporter($client, $baseUri, $headers);

        return new Client($transporter);
    }

    private function hasResendKey(): bool
    {
        $key = trim((string) config('services.resend.key'));

        return $key !== '';
    }

    private function sendViaLaravelMailer(string $email, string $otp, string $html): bool
    {
        try {
            Mail::html($html, function ($message) use ($email, $otp) {
                $message->to($email)
                    ->subject('Your CrewSwap OTP Code: ' . $otp);
            });

            \Log::info('OTP sent via Laravel mailer', ['email' => $email]);
            return true;
        } catch (\Exception $e) {
            $existing = $this->lastError;
            $fallbackError = 'Mailer: ' . $e->getMessage();
            $this->lastError = $existing ? ($existing . ' | ' . $fallbackError) : $fallbackError;

            \Log::error('Failed to send OTP email via Laravel mailer', ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function resolveFromAddress(): string
    {
        $fromAddress = (string) config('mail.from.address');

        if (app()->environment('local') && str_ends_with($fromAddress, '.local')) {
            return 'onboarding@resend.dev';
        }

        return $fromAddress;
    }

    /**
     * Generate HTML template for OTP email
     */
    private function getOtpEmailTemplate(string $otp, string $email): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: bold; color: #667eea; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 8px; text-align: center; }
        .otp-box { 
            background: #667eea; 
            color: white; 
            padding: 20px; 
            border-radius: 8px; 
            font-size: 32px; 
            font-weight: bold; 
            letter-spacing: 4px; 
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; }
        .note { background: #fff3cd; padding: 12px; border-radius: 4px; color: #856404; font-size: 13px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🚀 CrewSwap</div>
        </div>
        
        <div class="content">
            <h2 style="color: #333; margin-top: 0;">Email Verification</h2>
            <p>Welcome to CrewSwap! Your one-time verification code is:</p>
            
            <div class="otp-box">$otp</div>
            
            <p style="color: #666; font-size: 14px;">This code will expire in <strong>10 minutes</strong></p>
            
            <div class="note">
                ⏰ If you didn't request this code, please ignore this email. Your account won't be created without verified access.
            </div>
            
            <p style="color: #999; font-size: 13px; margin-top: 20px;">
                Never share your OTP code with anyone, including CrewSwap staff.
            </p>
        </div>
        
        <div class="footer">
            <p>© 2026 CrewSwap. All rights reserved.</p>
            <p>Sent to: $email</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
