<?php
// routes/api.php - CLEAN VERSION

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
use App\Http\Controllers\Api\VacationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\BiometricAuthController;
use App\Http\Controllers\Api\TripPreferencesController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\UserSettingsController;
use App\Http\Controllers\Api\RosterController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\BlogController as ApiBlogController;
use Illuminate\Support\Facades\Route;

// ==================== PUBLIC ROUTES ====================

// Language routes
Route::get('/languages', [LanguageController::class, 'getSupportedLanguages']);
Route::get('/current-language', [LanguageController::class, 'getCurrentLanguage']);
Route::post('/set-language/{lang}', [LanguageController::class, 'setLanguage']);

// Simple Public routes (for testing)
Route::post('/simple-register', [AuthController::class, 'simpleRegister']);
Route::post('/simple-login', [AuthController::class, 'simpleLogin']);

// Public blog routes
Route::get('/blogs', [ApiBlogController::class, 'index']);
Route::get('/blogs/{blog}', [ApiBlogController::class, 'show']);
Route::get('/blog/posts', [ApiBlogController::class, 'index']);
Route::get('/blog/posts/{blog}', [ApiBlogController::class, 'show']);

// Legacy Public routes
Route::get('/registration-options', [RegistrationOptionsController::class, 'index']);
Route::get('/registration-option', [RegistrationOptionsController::class, 'index']);
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// Password Reset Routes (Public)
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/resend', [PasswordResetController::class, 'resendResetLink']);
Route::get('/password/validate-token', [PasswordResetController::class, 'validateToken']);

// Biometric Login Routes (Public)
Route::post('/auth/login-code', [BiometricAuthController::class, 'loginWithCode']);
Route::post('/auth/login-faceid', [BiometricAuthController::class, 'loginWithFaceId']);

// ==================== PROTECTED ROUTES ====================

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // User Profile
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'showById']);
    Route::post('/user/device-token', [UserController::class, 'storeDeviceToken']);

    // User Settings
    Route::put('/user/notification-settings', [UserSettingsController::class, 'updateNotificationSettings']);
    Route::put('/user/privacy-settings', [UserSettingsController::class, 'updatePrivacySettings']);

    // Password Change (Authenticated)
    Route::post('/password/change', [PasswordResetController::class, 'changePassword']);

    // Biometric Setup (Authenticated)
    Route::post('/auth/setup-code', [BiometricAuthController::class, 'setupCode']);
    Route::post('/auth/setup-faceid', [BiometricAuthController::class, 'setupFaceId']);
    Route::delete('/auth/disable-biometric', [BiometricAuthController::class, 'disableBiometric']);
    Route::get('/auth/biometric-status', [BiometricAuthController::class, 'getBiometricStatus']);
    Route::post('/auth/update-code', [BiometricAuthController::class, 'updateCode']);

    // Trip Preferences
    Route::get('/user/trip-preferences', [TripPreferencesController::class, 'show']);
    Route::put('/user/trip-preferences', [TripPreferencesController::class, 'update']);

    // User plane types (aircraft preferences)
    Route::get('/user/plane-types', [UserController::class, 'planeTypes']);
    Route::post('/user/plane-types', [UserController::class, 'updatePlaneTypes']);
    Route::delete('/user/plane-types/{planeType}', [UserController::class, 'removePlaneType']);

    // Trips
    Route::get('/my-trips', [TripController::class, 'myTrips']);
    Route::put('/my-trips/{trip}', [TripController::class, 'updateMyTrip']);
    Route::delete('/my-trips/{trip}', [TripController::class, 'destroyMyTrip']);
    Route::get('/trip-details/{id}', [TripController::class, 'tripDetails']);

    // Swap Marketplace
    Route::get('/browse-trips', [TripController::class, 'browseTrips']);
    Route::post('/assign-trip-position', [TripController::class, 'assignTripPosition']);
    Route::post('/publish-trip', [TripController::class, 'publishTrip']);
    Route::match(['put', 'patch'], '/publish-trip/{publishedTrip}', [TripController::class, 'updatePublishedTrip']);
    Route::delete('/publish-trip/{publishedTrip}', [TripController::class, 'destroyPublishedTrip']);
    Route::post('/request-swap', [SwapController::class, 'requestSwap']);
    Route::put('/swap-request/{swapRequest}', [SwapController::class, 'updateSwapRequest']);
    Route::post('/confirm-swap/{swapRequest}', [SwapController::class, 'confirmSwap']);
    Route::post('/reject-swap/{swapRequest}', [SwapController::class, 'rejectSwap']);
    Route::post('/cancel-swap/{swapRequest}', [SwapController::class, 'cancelSwap']);
    Route::get('/swap-history', [TripController::class, 'swapHistory']);

    // Admin Settings
    Route::get('/admin/settings', [AdminSettingsController::class, 'index']);
    Route::post('/admin/settings', [AdminSettingsController::class, 'update']);

    // Vacation
    Route::get('/vacation-swaps', [TripController::class, 'browseTrips']);
    Route::post('/vacation-swaps/request', [SwapController::class, 'requestSwap']);
    Route::get('/vacation-swaps/history', [TripController::class, 'swapHistory']);
    Route::post('/publish-vacation', [VacationController::class, 'publishVacation']);
    Route::get('/my-vacations', [VacationController::class, 'myVacations']);
    Route::get('/browse-vacations', [VacationController::class, 'browseVacations']);

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

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{trip}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{trip}', [FavoriteController::class, 'destroy']);

    // Support
    Route::get('/support/conversation', [SupportController::class, 'getSupportConversation']);
    Route::post('/support/send-message', [SupportController::class, 'sendSupportMessage']);
    Route::get('/support/messages/{conversationId}', [SupportController::class, 'getSupportMessages']);
    Route::get('/support/conversations', [SupportController::class, 'listSupportConversations']);

    // Reports
    Route::post('/report-user', [ReportController::class, 'reportUser']);
    Route::get('/my-reports', [ReportController::class, 'myReports']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Dashboard analytics
    Route::get('/dashboard/analytics', [AnalyticsController::class, 'index']);

    // Roster Management
    Route::post('/roster/upload', [RosterController::class, 'uploadRoster']);
    Route::get('/roster', [RosterController::class, 'getRoster']);
    Route::delete('/roster/{id}', [RosterController::class, 'deleteRoster']);

    // Trip Matching Results
    Route::get('/trips/{id}/matching-results', [TripController::class, 'matchingResults']);
    Route::get('/my-trips/{id}/matching-results', [TripController::class, 'matchingResults']);
    Route::get('/published-trips/{id}/matching-results', [TripController::class, 'matchingResults']);

    // Subscription Plans
    Route::get('/subscription/plans', [SubscriptionController::class, 'index']);
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'index']);
    Route::get('/subscription-plans', [SubscriptionController::class, 'index']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);

    // Friends Management
    Route::get('/friends', [FriendController::class, 'index']);
    Route::post('/friends/add', [FriendController::class, 'addFriend']);
    Route::post('/friends/{userId}', [FriendController::class, 'addFriend']);
    Route::post('/users/{userId}/friend', [FriendController::class, 'addFriend']);
    Route::post('/toggle-friend', [FriendController::class, 'toggleFriend']);
    Route::post('/toggle-friend/{userId}', [FriendController::class, 'toggleFriend']);
    Route::delete('/friends/{userId}', [FriendController::class, 'removeFriend']);
});

