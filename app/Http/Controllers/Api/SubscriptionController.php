<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Retrieve available subscription plans for the application.
     */
    public function index(Request $request)
    {
        $plans = [
            [
                'id' => 'free',
                'name' => 'Free Plan',
                'price' => 0,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'description' => 'Basic flight swap browsing and messaging',
                'features' => [
                    'Browse flight swaps',
                    'Basic in-app messaging',
                    '1 roster upload per month',
                ],
                'is_popular' => false,
            ],
            [
                'id' => 'pro_monthly',
                'name' => 'Pro Monthly',
                'price' => 9.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'description' => 'Full access to automated matching and roster synchronization',
                'features' => [
                    'Unlimited flight swap requests',
                    'Instant mobile push notifications',
                    'Unlimited roster PDF parsing',
                    'Same-roster crew matching',
                    'Priority customer support',
                ],
                'is_popular' => true,
            ],
            [
                'id' => 'pro_yearly',
                'name' => 'Pro Yearly',
                'price' => 89.99,
                'currency' => 'USD',
                'billing_cycle' => 'yearly',
                'description' => 'Best value - 2 months free with full Pro features',
                'features' => [
                    'All Pro Monthly features',
                    'Save over 25% annually',
                    'Exclusive early access to newly published trips',
                ],
                'is_popular' => false,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $plans,
                'current_subscription' => [
                    'plan_id' => 'pro_monthly',
                    'status' => 'active',
                    'renews_at' => now()->addMonth()->toIso8601String(),
                ],
            ],
        ]);
    }
}
