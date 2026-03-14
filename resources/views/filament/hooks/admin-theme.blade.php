<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

    [data-filament-panel="admin"] {
        --admin-blue: #2f6fd1;
        --admin-blue-deep: #275db0;
        --admin-blue-soft: #1064ff;
        --admin-border: #8eb4f2;
        --admin-surface: #ffffff;
        --admin-text: #1f2937;
        --admin-muted: #6b7280;
        font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
    }

    [data-filament-panel="admin"] .fi-body {
        background: linear-gradient(180deg, #f3f6fc 0%, #eaf0fb 100%);
    }

    [data-filament-panel="admin"] aside.fi-sidebar,
    [data-filament-panel="admin"] .fi-sidebar,
    [data-filament-panel="admin"] .fi-sidebar-header,
    [data-filament-panel="admin"] .fi-sidebar-nav,
    [data-filament-panel="admin"] .fi-sidebar-nav-ctn {
        background: var(--admin-blue) !important;
    }

    aside.fi-sidebar,
    .fi-sidebar,
    .fi-sidebar-header,
    .fi-sidebar-nav,
    .fi-sidebar-nav-ctn {
        background: #ffffff !important;
    }

    [data-filament-panel="admin"] aside.fi-sidebar,
    [data-filament-panel="admin"] .fi-sidebar {
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(3, 97, 238, 0.34);
        border-inline-end: 0;
        margin: 14px 0 14px 12px;
        height: calc(100vh - 28px);
        width: 220px !important;
        max-width: 220px !important;
    }

    [data-filament-panel="admin"] .fi-sidebar-header {
        padding-top: 0.25rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-nav {
        padding: 0.5rem 0.55rem 0.75rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-group,
    [data-filament-panel="admin"] .fi-sidebar-group-items {
        gap: 0.2rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-group-label,
    [data-filament-panel="admin"] .fi-sidebar-item-grouped-label {
        color: rgba(255, 255, 255, 0.75) !important;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding-inline: 0.7rem;
        margin-bottom: 0.35rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-button {
        justify-content: flex-start;
        gap: 0.72rem;
        border-radius: 999px;
        margin: 2px 0;
        min-height: 44px;
        padding: 0.66rem 0.9rem;
        width: 100%;
        color: #ffffff !important;
        background: transparent !important;
        transition: background-color 0.18s ease, opacity 0.18s ease;
        font-size: 0.94rem;
        font-weight: 500;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-icon {
        color: #ffffff !important;
        width: 20px;
        height: 20px;
        opacity: 0.9;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-label {
        display: inline !important;
        color: #ffffff !important;
        font-size: 0.95rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        border-radius: 999px;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-active .fi-sidebar-item-button,
    [data-filament-panel="admin"] .fi-sidebar-item-button:hover {
        background: var(--admin-blue-deep) !important;
        color: #ffffff !important;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-active .fi-sidebar-item-icon,
    [data-filament-panel="admin"] .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #ffffff !important;
        opacity: 1;
    }

    [data-filament-panel="admin"] .fi-sidebar-group-button {
        border-radius: 12px;
        color: #ffffff !important;
        min-height: 44px;
        padding: 0.66rem 0.8rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-group-button:hover,
    [data-filament-panel="admin"] .fi-sidebar-group[aria-expanded="true"] .fi-sidebar-group-button {
        background: rgba(255, 255, 255, 0.13) !important;
    }

    [data-filament-panel="admin"] .fi-sidebar-group-collapse-button,
    [data-filament-panel="admin"] .fi-sidebar-group-button-icon {
        color: #ffffff !important;
        opacity: 0.92;
    }

    [data-filament-panel="admin"] .fi-sidebar-item-grouped-border {
        border-color: rgba(255, 255, 255, 0.14) !important;
        margin-inline-start: 0.9rem;
    }

    [data-filament-panel="admin"] .fi-sidebar-nav-footer,
    [data-filament-panel="admin"] .fi-sidebar-nav-tenant-menu {
        display: block !important;
    }

    [data-filament-panel="admin"] .fi-main {
        padding-inline-start: 1rem;
    }

    [data-filament-panel="admin"] .fi-topbar {
        background: transparent !important;
        box-shadow: none !important;
        border-bottom: 0 !important;
    }

    [data-filament-panel="admin"] .fi-topbar nav,
    [data-filament-panel="admin"] .fi-topbar > div {
        background: transparent !important;
    }

    /* Header Icons Styling */
    [data-filament-panel="admin"] .fi-topbar .fi-dropdown-trigger,
    [data-filament-panel="admin"] .fi-topbar button,
    [data-filament-panel="admin"] .fi-topbar a[role="button"] {
        color: var(--admin-blue) !important;
        transition: all 0.2s ease;
    }

    [data-filament-panel="admin"] .fi-topbar .fi-dropdown-trigger:hover,
    [data-filament-panel="admin"] .fi-topbar button:hover,
    [data-filament-panel="admin"] .fi-topbar a[role="button"]:hover {
        color: var(--admin-blue-deep) !important;
        background: rgba(47, 111, 209, 0.1) !important;
        border-radius: 8px;
    }

    [data-filament-panel="admin"] .fi-topbar svg,
    [data-filament-panel="admin"] .fi-topbar .fi-icon {
        color: var(--admin-blue) !important;
        transition: color 0.2s ease;
    }

    [data-filament-panel="admin"] .fi-topbar button:hover svg,
    [data-filament-panel="admin"] .fi-topbar a[role="button"]:hover svg {
        color: var(--admin-blue-deep) !important;
    }

    /* Hide default Filament user menu/avatar on right side of topbar */
    [data-filament-panel="admin"] .fi-topbar .fi-user-menu,
    [data-filament-panel="admin"] .fi-topbar .fi-user-menu-trigger,
    [data-filament-panel="admin"] .fi-topbar button[type="button"] .fi-avatar,
    [data-filament-panel="admin"] .fi-topbar .fi-dropdown-list-item .fi-avatar,
    [data-filament-panel="admin"] .fi-topbar-end .fi-avatar {
        display: none !important;
    }

    [data-filament-panel="admin"] .fi-avatar {
        border: 2px solid var(--admin-blue) !important;
        box-shadow: 0 2px 8px rgba(47, 111, 209, 0.2) !important;
    }

    [data-filament-panel="admin"] .fi-badge {
        background: var(--admin-blue) !important;
        color: #ffffff !important;
    }

    [data-filament-panel="admin"] .fi-page,
    [data-filament-panel="admin"] .fi-section,
    [data-filament-panel="admin"] .fi-ta-ctn,
    [data-filament-panel="admin"] .fi-in-entry,
    [data-filament-panel="admin"] .fi-fo-field-wrp {
        border-radius: 12px !important;
    }

    [data-filament-panel="admin"] .fi-section,
    [data-filament-panel="admin"] .fi-ta-ctn,
    [data-filament-panel="admin"] .fi-fo-builder-item,
    [data-filament-panel="admin"] .fi-tabs,
    [data-filament-panel="admin"] .fi-in-grid {
        background: var(--admin-surface) !important;
        border: 1px solid var(--admin-border) !important;
        box-shadow: 0 8px 18px rgba(71, 116, 197, 0.14) !important;
    }

    [data-filament-panel="admin"] .fi-ta-header,
    [data-filament-panel="admin"] .fi-ta-table thead,
    [data-filament-panel="admin"] .fi-tabs-item-active {
        background: var(--admin-blue-soft) !important;
    }

    [data-filament-panel="admin"] .fi-ta-table tbody tr {
        background: rgba(255, 255, 255, 0.3);
    }

    [data-filament-panel="admin"] .fi-ta-table tbody tr:hover {
        background: rgba(47, 111, 209, 0.09) !important;
    }

    [data-filament-panel="admin"] .fi-input,
    [data-filament-panel="admin"] .fi-select-input,
    [data-filament-panel="admin"] .fi-fo-text-input,
    [data-filament-panel="admin"] .fi-fo-select {
        border-radius: 8px !important;
        border: 1px solid #bfd1f1 !important;
        background: #f8fbff !important;
        color: var(--admin-text) !important;
    }

    [data-filament-panel="admin"] .fi-btn {
        border-radius: 8px !important;
        font-weight: 600;
    }

    [data-filament-panel="admin"] .fi-btn-color-primary {
        background: var(--admin-blue) !important;
        border-color: var(--admin-blue) !important;
        color: #fff !important;
    }

    [data-filament-panel="admin"] .fi-btn-color-primary:hover {
        background: var(--admin-blue-deep) !important;
        border-color: var(--admin-blue-deep) !important;
    }

    [data-filament-panel="admin"] .fi-btn-color-danger {
        background: #ff4545 !important;
        border-color: #ff4545 !important;
        color: #fff !important;
    }

    [data-filament-panel="admin"] .fi-btn-color-warning {
        background: #ff9f2f !important;
        border-color: #ff9f2f !important;
        color: #fff !important;
    }

    [data-filament-panel="admin"] .fi-badge,
    [data-filament-panel="admin"] .fi-color-custom {
        border-radius: 999px !important;
    }

    [data-filament-panel="admin"] h1,
    [data-filament-panel="admin"] h2,
    [data-filament-panel="admin"] h3,
    [data-filament-panel="admin"] .fi-header-heading {
        color: #111827;
        font-weight: 800 !important;
    }

    [data-filament-panel="admin"] .fi-header-subheading,
    [data-filament-panel="admin"] .fi-fo-field-wrp-label,
    [data-filament-panel="admin"] .fi-ta-empty-state-description,
    [data-filament-panel="admin"] .text-gray-500,
    [data-filament-panel="admin"] .text-gray-600 {
        color: var(--admin-muted) !important;
    }

    @media (max-width: 1024px) {
        [data-filament-panel="admin"] .fi-sidebar {
            margin: 0;
            border-radius: 0;
            height: 100vh;
            width: min(85vw, 320px) !important;
            max-width: min(85vw, 320px) !important;
        }

        [data-filament-panel="admin"] .fi-main {
            padding-inline-start: 0;
        }
    }
</style>
