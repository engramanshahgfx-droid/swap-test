<aside class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Collapse sidebar">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </button>

    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <span class="sidebar-logo-text">{{ __('admin.brand') }}</span>
    </div>

    <nav class="side-nav">
        <a href="{{ route('dashboard') }}" class="nav-item" data-nav="dashboard">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            <span class="nav-label">{{ __('admin.nav.dashboard') }}</span>
        </a>

        <a href="{{ route('activation') }}" class="nav-item" data-nav="activation">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span class="nav-label">{{ __('admin.nav.users_management') }}</span>
        </a>

        <button class="nav-item js-config-toggle" type="button" id="config-trigger">
            <svg viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="14" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/><circle cx="17" cy="12" r="3"/></svg>
            <span class="nav-label">{{ __('admin.nav.configuration') }}</span>
            <svg class="chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <div class="sub-menu" id="config-menu">
            <a href="{{ route('airlines') }}" class="sub-item" data-nav="airlines">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="12" y1="7" x2="12" y2="13"/></svg>
                <span class="nav-label">{{ __('admin.nav.airlines') }}</span>
            </a>
            <a href="{{ route('positions') }}" class="sub-item" data-nav="positions">
                <svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="2"/></svg>
                <span class="nav-label">{{ __('admin.nav.positions') }}</span>
            </a>
        </div>

        <a href="{{ route('swap-flight') }}" class="nav-item" data-nav="swap-flight">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12h8M14 9l3 3-3 3"/></svg>
            <span class="nav-label">{{ __('admin.nav.flight_swaps') }}</span>
        </a>

        <a href="{{ route('swap-vacation') }}" class="nav-item" data-nav="swap-vacation">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span class="nav-label">{{ __('admin.nav.vacation_swaps') }}</span>
        </a>

        <a href="{{ route('reports') }}" class="nav-item" data-nav="reports">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
            <span class="nav-label">{{ __('admin.report') }}</span>
        </a>

        <a href="{{ route('support') }}" class="nav-item" data-nav="support">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            <span class="nav-label">{{ __('admin.nav.support') }}</span>
        </a>

        <a href="{{ route('analytics') }}" class="nav-item" data-nav="analytics">
            <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span class="nav-label">{{ __('admin.nav.analytics') }}</span>
        </a>

        <div class="divider"></div>

        <div class="bottom">
            <a href="{{ route('settings.page') }}" class="nav-item nav-sep" data-nav="settings">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span class="nav-label">{{ __('admin.settings') }}</span>
            </a>
            <form method="POST" action="/admin/logout" style="display:contents">
                @csrf
                <button type="submit" class="nav-item" style="text-align:left">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span class="nav-label">{{ __('admin.logout') }}</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
