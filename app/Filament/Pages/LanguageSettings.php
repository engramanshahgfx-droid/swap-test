<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class LanguageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationLabel = 'Language';
    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.language-settings';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.language');
    }

    public function handleLanguageSwitch(string $locale): void
    {
        if (in_array($locale, ['en', 'ar'])) {
            Session::put('locale', $locale);
            app()->setLocale($locale);
            redirect('/admin');
        }
    }
}
