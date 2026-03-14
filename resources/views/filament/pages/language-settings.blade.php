<x-filament-panels::page>
    @php
        $isArabic = app()->getLocale() === 'ar';
        $htmlDirection = $isArabic ? 'rtl' : 'ltr';
    @endphp
    
    <div class="space-y-6" dir="{{ $htmlDirection }}">
        <!-- Current Language -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('labels.current_language') }}
            </x-slot>

            <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800">
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('labels.active_language') }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-black mt-1">
                        @if(app()->getLocale() === 'en')
                            <span class="text-2xl mr-2">🇺🇸</span>{{ __('labels.english') }}
                        @else
                            <span class="text-2xl mr-2">🇸🇦</span>{{ __('labels.arabic') }}
                        @endif
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- Language Selection -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('labels.switch_language') }}
            </x-slot>

            <div class="grid gap-3 md:grid-cols-2">
                <!-- English Button -->
                <a href="?lang=en" onclick="document.location='?lang=en'; return false;" 
                    @class([
                        'block p-6 text-center rounded-lg border-2 font-semibold transition cursor-pointer hover:shadow-lg',
                        'border-green-500 bg-green-50 dark:bg-green-950' => app()->getLocale() === 'en',
                        'border-gray-200 bg-white dark:bg-gray-900 hover:border-blue-400' => app()->getLocale() !== 'en',
                    ])
                >
                    <div class="text-4xl mb-3">🇺🇸</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ __('labels.english') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('labels.left_to_right') }}</div>
                    @if(app()->getLocale() === 'en')
                        <div class="mt-3 inline-block px-3 py-1 bg-green-500 text-white text-xs font-bold rounded">{{ __('labels.selected') }}</div>
                    @endif
                </a>

                <!-- Arabic Button -->
                <a href="?lang=ar" onclick="document.location='?lang=ar'; return false;"
                    @class([
                        'block p-6 text-center rounded-lg border-2 font-semibold transition cursor-pointer hover:shadow-lg',
                        'border-green-500 bg-green-50 dark:bg-green-950' => app()->getLocale() === 'ar',
                        'border-gray-200 bg-white dark:bg-gray-900 hover:border-blue-400' => app()->getLocale() !== 'ar',
                    ])
                >
                    <div class="text-4xl mb-3">🇸🇦</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ __('labels.arabic') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('labels.right_to_left') }}</div>
                    @if(app()->getLocale() === 'ar')
                        <div class="mt-3 inline-block px-3 py-1 bg-green-500 text-white text-xs font-bold rounded">{{ __('labels.selected') }}</div>
                    @endif
                </a>
            </div>
        </x-filament::section>

        <!-- Language Info -->
        <x-filament::section>
            <x-slot name="heading">
                {{ __('labels.language') }}
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                @if(app()->getLocale() === 'ar')
                    <p>تم تعيين لغة الواجهة الخاصة بك على العربية. ستظهر جميع النصوص والعناصر من اليمين إلى اليسار.</p>
                    <p>يمكنك تبديل اللغة في أي وقت باستخدام الأزرار أعلاه أو من خلال قائمة الملف الشخصي.</p>
                @else
                    <p>Your interface language is set to English. All text and elements will display from left to right.</p>
                    <p>You can switch the language at any time using the buttons above or through your profile menu.</p>
                @endif
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
