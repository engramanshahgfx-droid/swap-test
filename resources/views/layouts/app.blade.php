<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title') | Flight Admin</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>
<body data-page="@yield('page-name')">
    <div class="site">
        @include('components.sidebar')

        <main class="main">
            @include('components.header')

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>

    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <script src="{{ asset('js/include.js') }}"></script>
</body>
</html>
