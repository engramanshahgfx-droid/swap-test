<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $driver;

    public function __construct()
    {
        $this->driver = config('services.sms.driver', 'africastalking');
    }

    public function sendOtp($phone, $otp)
    {
        $message = "Your CrewSwap verification code is: {$otp}. Valid for 10 minutes.";

        return $this->send($phone, $message);
    }

    public function send($phone, $message)
    {
        try {
            if ($this->driver === 'africastalking') {
                return $this->sendViaAfricasTalking($phone, $message);
            }

            // Add more drivers as needed
            Log::warning("SMS driver not configured: {$this->driver}");
            return false;
        } catch (\Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage(), [
                'phone' => $phone,
                'driver' => $this->driver,
            ]);
            return false;
        }
    }

    protected function sendViaAfricasTalking($phone, $message)
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');
        $from = config('services.africastalking.from');

        if (!$username || !$apiKey) {
            Log::warning('Africa\'s Talking credentials not configured');
            return false;
        }

        $AT = new \AfricasTalking\SDK\AfricasTalking($username, $apiKey);
        $sms = $AT->sms();

        $result = $sms->send([
            'to' => $phone,
            'message' => $message,
            'from' => $from,
        ]);

        Log::info('SMS sent via Africa\'s Talking', [
            'phone' => $phone,
            'result' => $result,
        ]);

        return $result;
    }
}

