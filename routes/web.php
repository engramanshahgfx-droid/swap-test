<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AirlinesController;
use App\Http\Controllers\Admin\PositionsController;
use App\Http\Controllers\Admin\AirportsController;
use App\Http\Controllers\Admin\SwapFlightController;
use App\Http\Controllers\Admin\SwapVacationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ActivationController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Frontend\AuthController as FrontendAuthController;
use App\Http\Controllers\Frontend\FlightController;
use App\Http\Controllers\Admin\LoginController;

// Redirect root to admin login or dashboard
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect('/admin/login');
});

// Admin Login Route (required by auth middleware)
Route::get('/admin/login', function () {
    return view('admin.login');
})->name('login');

// Admin Login POST handler
Route::post('/admin/login', [LoginController::class, 'store'])->name('login');

// Custom Admin Dashboard (protected by web auth)
Route::middleware('auth')->group(function () {
    // Admin Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Configuration
    Route::get('/airlines', [AirlinesController::class, 'index'])->name('airlines');
    Route::post('/airlines', [AirlinesController::class, 'store'])->name('airlines.store');
    Route::put('/airlines/{airline}', [AirlinesController::class, 'update'])->name('airlines.update');
    Route::delete('/airlines/{airline}', [AirlinesController::class, 'destroy'])->name('airlines.destroy');

    Route::get('/positions', [PositionsController::class, 'index'])->name('positions');
    Route::post('/positions', [PositionsController::class, 'store'])->name('positions.store');
    Route::put('/positions/{position}', [PositionsController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionsController::class, 'destroy'])->name('positions.destroy');

    Route::get('/airports', [AirportsController::class, 'index'])->name('airports');
    Route::post('/airports', [AirportsController::class, 'store'])->name('airports.store');
    Route::put('/airports/{airport}', [AirportsController::class, 'update'])->name('airports.update');
    Route::delete('/airports/{airport}', [AirportsController::class, 'destroy'])->name('airports.destroy');

    // Swap operations
    Route::get('/swap-flight', [SwapFlightController::class, 'index'])->name('swap-flight');
    Route::put('/swap-flight/{swap}/status', [SwapFlightController::class, 'updateStatus'])->name('swap-flight.status');

    Route::get('/swap-vacation', [SwapVacationController::class, 'index'])->name('swap-vacation');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::put('/reports/{report}/status', [ReportsController::class, 'updateStatus'])->name('reports.status');
    Route::post('/reports/{report}/moderate', [ReportsController::class, 'moderate'])->name('reports.moderate');

    // User activation management
    Route::get('/activation', [ActivationController::class, 'index'])->name('activation');
    Route::put('/activation/{user}/status', [ActivationController::class, 'updateStatus'])->name('activation.status');

    // Support / Chat
    Route::get('/support', [SupportController::class, 'index'])->name('support');
    Route::post('/support/{conversation}/reply', [SupportController::class, 'reply'])->name('support.reply');

    // Analytics & settings
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.page');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// ============================================
// FRONTEND PAGES ROUTES (localhost:8000/frontend-test)
// ============================================

// Debug endpoint
Route::get('/frontend-test/debug', function () {
    return [
        'airlines' => \App\Models\Airline::count(),
        'positions' => \App\Models\Position::count(),
        'plane_types' => \App\Models\PlaneType::count(),
        'users' => \App\Models\User::count(),
    ];
})->name('frontend.debug');

// Homepage - no auth required
Route::get('/frontend-test', function () {
    return view('frontend-test');
})->name('frontend.index');

// Authentication Routes (explicitly NO auth middleware - completely open)
Route::group([], function () {
    // Registration
    Route::get('/frontend-test/register', [FrontendAuthController::class, 'showRegister'])->name('frontend.register');
    Route::post('/frontend-test/register', function (Request $request) {
        \Log::info('Registration POST received', ['data' => $request->all()]);
        return app(FrontendAuthController::class)->register($request);
    });

    // OTP Verification
    Route::get('/frontend-test/verify-otp', [FrontendAuthController::class, 'showVerifyOtp'])->name('frontend.verify-otp');
    Route::post('/frontend-test/verify-otp', [FrontendAuthController::class, 'verifyOtp']);

    // Login
    Route::get('/frontend-test/login', [FrontendAuthController::class, 'showLogin'])->name('frontend.login');
    Route::post('/frontend-test/login', [FrontendAuthController::class, 'login']);

    // Forgot Password
    Route::get('/frontend-test/forgot-password', [FrontendAuthController::class, 'showForgotPassword'])->name('frontend.forgot-password');
    Route::post('/frontend-test/forgot-password', [FrontendAuthController::class, 'forgotPassword']);

    Route::get('/frontend-test/reset-password-otp', [FrontendAuthController::class, 'showResetPasswordOtp'])->name('frontend.reset-password-otp');
    Route::post('/frontend-test/reset-password-otp', [FrontendAuthController::class, 'verifyResetPasswordOtp']);

    Route::get('/frontend-test/reset-password', [FrontendAuthController::class, 'showResetPassword'])->name('frontend.reset-password');
    Route::post('/frontend-test/reset-password', [FrontendAuthController::class, 'resetPassword']);
});

// Protected Routes (requires frontend authentication - both Auth and Session)
Route::middleware(\App\Http\Middleware\FrontendAuth::class)->group(function () {
    // Dashboard
    Route::get('/frontend-test/dashboard', function () {
        return view('frontend.dashboard');
    })->name('frontend.dashboard');

    // Logout
    Route::post('/frontend-test/logout', [FrontendAuthController::class, 'logout'])->name('frontend.logout');

    // Flights
    Route::get('/frontend-test/flights', [FlightController::class, 'index'])->name('frontend.flights.index');
    Route::get('/frontend-test/flights/add', [FlightController::class, 'showAddFlight'])->name('frontend.flights.add');
    Route::post('/frontend-test/flights', [FlightController::class, 'addFlight'])->name('frontend.flights.store');
    Route::get('/frontend-test/flights/my-flights', [FlightController::class, 'myFlights'])->name('frontend.flights.my-flights');
    Route::get('/frontend-test/flights/{flight}', [FlightController::class, 'show'])->name('frontend.flights.show');
    Route::post('/frontend-test/flights/{flight}/join', [FlightController::class, 'joinFlight'])->name('frontend.flights.join');
    Route::post('/frontend-test/flights/{flight}/leave', [FlightController::class, 'leaveFlight'])->name('frontend.flights.leave');
});

// API Testing Frontend (no auth required for testing)
// Note: Keeping this route but it's now replaced by the /frontend-test route above

