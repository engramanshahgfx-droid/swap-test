<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailOtpService
{
    private ?string $lastError = null;

    public function sendOtp(string $email, string $otp): bool
    {
        $this->lastError = null;

        try {
            Mail::html($this->getOtpEmailTemplate($otp, $email), function ($message) use ($email, $otp) {
                $message->to($email)
                    ->subject('Your flightSwap  OTP Code: ' . $otp);
            });

            \Log::info('OTP sent', ['email' => $email]);
            return true;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            \Log::error('Failed to send OTP email', ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

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
            <div class="logo">🚀 flightSwap </div>
        </div>

        <div class="content">
            <h2 style="color: #333; margin-top: 0;">Email Verification</h2>
            <p>Welcome to flightSwap ! Your one-time verification code is:</p>

            <div class="otp-box">$otp</div>

            <p style="color: #666; font-size: 14px;">This code will expire in <strong>10 minutes</strong></p>

            <div class="note">
                ⏰ If you didn't request this code, please ignore this email. Your account won't be created without verified access.
            </div>

            <p style="color: #999; font-size: 13px; margin-top: 20px;">
                Never share your OTP code with anyone, including flightSwap  staff.
            </p>
        </div>

        <div class="footer">
            <p>© 2026 flightSwap . All rights reserved.</p>
            <p>Sent to: $email</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
