<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AirlinesController;
use App\Http\Controllers\Admin\PositionsController;
use App\Http\Controllers\Admin\SwapFlightController;
use App\Http\Controllers\Admin\SwapVacationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ActivationController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\SettingsController;

// Redirect root to admin login or dashboard
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect('/admin/login');
});

// Custom Admin Dashboard (protected by web auth)
Route::middleware('auth')->group(function () {
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

// API Testing Frontend (no auth required for testing)
Route::get('/frontend-test', function () {
    return view('frontend-test');
});

Route::get('/test-login', function () {
    return view('test-login');
});

Route::post('/test-login', function () {
    $credentials = request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        return 'SUCCESS: ' . Auth::user()->full_name;
    }

    return 'FAILED: Invalid credentials';
});

