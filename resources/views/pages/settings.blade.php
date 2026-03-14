@extends('layouts.app')

@section('title', __('admin.settings_page.title'))
@section('page-name', 'settings')

@section('content')
    <h1 class="page-title">{{ __('admin.settings_page.title') }}</h1>
    <p class="page-sub">{{ __('admin.settings_page.subtitle') }}</p>

    <div class="table-wrap" style="max-width:860px;padding:16px;">
        <form method="POST" action="{{ route('settings.update') }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf

            <div class="form-grid two">
                <div>
                    <label>{{ __('admin.settings_page.full_name') }}</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $admin->full_name) }}" required>
                </div>
                <div>
                    <label>{{ __('admin.settings_page.email') }}</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>
            </div>

            <div class="form-grid two">
                <div>
                    <label>{{ __('admin.settings_page.language') }}</label>
                    <select name="language">
                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
                        <option value="ar" {{ app()->getLocale() === 'ar' ? 'selected' : '' }}>AR</option>
                    </select>
                </div>
                <div>
                    <label>{{ __('admin.settings_page.theme_mode') }}</label>
                    <select name="theme">
                        <option value="light" {{ session('admin_theme', 'light') === 'light' ? 'selected' : '' }}>{{ __('admin.settings_page.light') }}</option>
                        <option value="dark" {{ session('admin_theme') === 'dark' ? 'selected' : '' }}>{{ __('admin.settings_page.dark') }}</option>
                    </select>
                </div>
            </div>

            <div class="form-grid two">
                <div>
                    <label>{{ __('admin.settings_page.new_password') }}</label>
                    <input type="password" name="password" autocomplete="new-password">
                </div>
                <div>
                    <label>{{ __('admin.settings_page.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password">
                </div>
            </div>

            <div class="modal-footer" style="padding:0;justify-content:flex-end;">
                <button type="submit">{{ __('admin.settings_page.save_changes') }}</button>
            </div>
        </form>
    </div>
@endsection
