@php
    $currentLocale = app()->getLocale();
@endphp

<div class="flex items-center gap-2 px-3 py-2">
    <!-- English Button -->
    @if($currentLocale === 'en')
        <button 
            class="px-3 py-1.5 rounded-full text-sm font-medium bg-blue-600 text-black shadow-md transition border-2 border-blue-600"
            title="English"
            disabled
        >
            English
        </button>
    @else
        <a 
            href="?lang=en" 
            class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition border-2 border-transparent"
            title="Switch to English"
        >
            English
        </a>
    @endif

    <!-- Arabic Button -->
    @if($currentLocale === 'ar')
        <button 
            class="px-3 py-1.5 rounded-full text-sm font-medium bg-blue-600 text-black shadow-md transition border-2 border-blue-600"
            title="العربية"
            disabled
        >
            Ar
        </button>
    @else
        <a 
            href="?lang=ar" 
            class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition border-2 border-transparent"
            title="Switch to العربية"
        >
            Ar
        </a>
    @endif
</div>

