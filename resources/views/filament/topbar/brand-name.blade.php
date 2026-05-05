<style>
    /* Hide the default Filament brand name */
    [data-filament-panel="admin"] .fi-topbar-logo {
        display: none !important;
    }
</style>

@php
    $isArabic = app()->getLocale() === 'ar';
    $brandName = $isArabic ? 'كرو سواب' : 'flightSwap ';
    $user = auth()->user();
    $userName = $user->name ?? 'User';
    $userInitials = strtoupper(substr($userName, 0, 2));
@endphp

<!--<div class="flex items-center gap-4 pl-4">
    Brand Name -->
    <!-- <span class="text-lg font-bold text-gray-900 dark:text-white tracking-tight"> -->
        <!-- {{ $brandName }} -->
    <!-- </span> -->

    <!-- Separator -->
    <!-- <span class="text-gray-300 dark:text-gray-600">|</span> -->

    <!-- User Profile -->
    <!-- <div class="flex items-center gap-2"> -->
        <!-- User Avatar -->
        <!-- <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 text-white font-semibold text-sm shadow-md border-2 border-blue-200 dark:border-blue-800"> -->
            <!-- {{ $userInitials }} -->
        <!-- </div> -->

        <!-- User Name -->
        <!-- <span class="text-sm font-medium text-gray-700 dark:text-gray-300"> -->
            <!-- {{ $userName }} -->
        <!-- </span> -->
    <!-- </div> -->
<!-- </div>  -->
