<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\SwapController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\LanguageController;
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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User
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
    Route::get('/swap-history', [TripController::class, 'swapHistory']);
    
    // Chat
    Route::get('/conversations', [ChatController::class, 'conversations']);
    Route::get('/messages/{conversation}', [ChatController::class, 'messages']);
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::post('/messages/{conversation}/read', [ChatController::class, 'markAsRead']);
    
    // Reports
    Route::post('/report-user', [ReportController::class, 'reportUser']);
    Route::get('/my-reports', [ReportController::class, 'myReports']);
});
