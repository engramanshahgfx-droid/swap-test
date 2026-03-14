<header class="topbar">
    <button class="menu-btn" id="menu-toggle" aria-label="Toggle sidebar">{{ __('admin.menu') }}</button>

    <form method="GET" action="{{ route('dashboard') }}" class="search-box" style="max-width:360px;flex:1;margin:0 12px;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.header.quick_search') }}" />
    </form>

    <div class="topbar-right">
        <div class="lang-pill">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}"
                    class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">{{ __('admin.arabic') }}</a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                    class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">{{ __('admin.english') }}</a>
        </div>

        <button class="icon-btn" type="button" aria-label="mail">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
        </button>
        <button class="icon-btn" type="button" aria-label="alerts">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </button>

        <div class="profile">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}</div>
            <div>
                <strong>{{ auth()->user()->full_name ?? 'Admin' }}</strong>
                <span>{{ auth()->user()->email ?? '' }}</span>
            </div>
        </div>

        <form method="POST" action="/admin/logout" style="display:contents">
            @csrf
            <button type="submit" class="icon-btn" aria-label="logout">{{ __('admin.logout') }}</button>
        </form>
    </div>
</header>
