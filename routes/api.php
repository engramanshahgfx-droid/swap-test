<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\SwapController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\RegistrationOptionsController;
use Illuminate\Support\Facades\Route;

// Language routes
Route::get('/languages', [LanguageController::class, 'getSupportedLanguages']);
Route::get('/current-language', [LanguageController::class, 'getCurrentLanguage']);
Route::post('/set-language/{lang}', [LanguageController::class, 'setLanguage']);

// Firebase Notifications (pushed to authenticated users)
// Routes for push notifications will be added here

// Simple Public routes (for testing)
Route::post('/simple-register', [AuthController::class, 'simpleRegister']);
Route::post('/simple-login', [AuthController::class, 'simpleLogin']);

// Legacy Public routes
Route::get('/registration-options', [RegistrationOptionsController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    
    // User
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'showById']);
    Route::post('/user/device-token', [UserController::class, 'storeDeviceToken']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    
    // Trips
    Route::get('/my-trips', [TripController::class, 'myTrips']);
    Route::get('/trip-details/{id}', [TripController::class, 'tripDetails']);
    
    // Swap Marketplace
    Route::get('/browse-trips', [TripController::class, 'browseTrips']);
    Route::post('/publish-trip', [TripController::class, 'publishTrip']);
    Route::post('/request-swap', [SwapController::class, 'requestSwap']);
    Route::post('/confirm-swap/{swapRequest}', [SwapController::class, 'confirmSwap']);
    Route::post('/reject-swap/{swapRequest}', [SwapController::class, 'rejectSwap']);
    Route::post('/cancel-swap/{swapRequest}', [SwapController::class, 'cancelSwap']);
    Route::get('/swap-history', [TripController::class, 'swapHistory']);

    // Vacation swap aliases (using existing swap workflow)
    Route::get('/vacation-swaps', [TripController::class, 'browseTrips']);
    Route::post('/vacation-swaps/request', [SwapController::class, 'requestSwap']);
    Route::get('/vacation-swaps/history', [TripController::class, 'swapHistory']);
    
    // Chat
    Route::get('/conversations', [ChatController::class, 'conversations']);
    Route::get('/messages/{conversation}', [ChatController::class, 'messages']);
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::post('/messages/{conversation}/read', [ChatController::class, 'markAsRead']);
    Route::get('/chat/conversations', [ChatController::class, 'conversations']);
    Route::get('/chat/messages/{conversation}', [ChatController::class, 'messages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
    Route::post('/chat/mark-read', [ChatController::class, 'markRead']);
    
    // Reports
    Route::post('/report-user', [ReportController::class, 'reportUser']);
    Route::get('/my-reports', [ReportController::class, 'myReports']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Dashboard analytics
    Route::get('/dashboard/analytics', [AnalyticsController::class, 'index']);
});
