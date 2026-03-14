<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | Flight Admin</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body data-page="dashboard">
  <div class="site">
    <aside class="sidebar" id="sidebar">
      <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Collapse sidebar">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      </button>

      <nav class="side-nav">
        <a href="index.html" class="nav-item" data-nav="dashboard">
          <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          <span class="nav-label">Dashboard</span>
        </a>

        <button class="nav-item js-config-toggle" type="button" id="config-trigger">
          <svg viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="14" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/><circle cx="17" cy="12" r="3"/></svg>
          <span class="nav-label">Configuration</span>
          <svg class="chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <div class="sub-menu" id="config-menu">
          <a href="airlines.html" class="sub-item" data-nav="airlines">
            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="12" y1="7" x2="12" y2="13"/></svg>
            <span class="nav-label">Airlines</span>
          </a>
          <a href="positions.html" class="sub-item" data-nav="positions">
            <svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="2"/></svg>
            <span class="nav-label">Positions</span>
          </a>
        </div>

        <a href="swap-flight.html" class="nav-item" data-nav="swap-flight">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12h8M14 9l3 3-3 3"/></svg>
          <span class="nav-label">Swap operation</span>
        </a>

        <a href="reports.html" class="nav-item" data-nav="reports">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
          <span class="nav-label">Report</span>
        </a>

        <a href="activation.html" class="nav-item" data-nav="activation">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span class="nav-label">Activation</span>
        </a>

        <a href="support.html" class="nav-item" data-nav="support">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/><circle cx="12" cy="8" r="1.5"/></svg>
          <span class="nav-label">Service</span>
        </a>

        <div class="divider"></div>

        <div class="bottom">
          <a href="#" class="nav-item nav-sep">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span class="nav-label">Settings</span>
          </a>
          <a href="#" class="nav-item">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span class="nav-label">Log out</span>
          </a>
        </div>
      </nav>
    </aside>

    <main class="main">
      <header class="topbar">
        <button class="menu-btn" id="menu-toggle" aria-label="Toggle sidebar">Menu</button>

        <div class="topbar-right">
          <div class="lang-pill">
            <button type="button" class="active">Arabic</button>
            <button type="button">EN</button>
          </div>

          <button class="icon-btn" type="button" aria-label="mail">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </button>
          <button class="icon-btn" type="button" aria-label="alerts">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          </button>

          <div class="profile">
            <div class="avatar">A</div>
            <div>
              <strong>Abdulwahab Samkari</strong>
              <span>nais.a@hotmail.com</span>
            </div>
          </div>
        </div>
      </header>

      <section class="content">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Overview of operations</p>

        <div class="grid">
          <article class="card js-stat-card" data-stat-title="Total flight" data-stat-value="2004" data-stat-note="+343">
            <div class="card-top"><span class="mini-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></span><span class="mini-arrow"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span></div>
            <h3>Total Flights</h3>
            <div class="metric">1,580</div>
            <div class="metric-note ok">+343 this week</div>
          </article>
          <article class="card js-stat-card" data-stat-title="Total airline" data-stat-value="89" data-stat-note="-2">
            <div class="card-top"><span class="mini-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg></span><span class="mini-arrow"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span></div>
            <h3>Total Airlines</h3>
            <div class="metric">89</div>
            <div class="metric-note bad">-2 this week</div>
          </article>
          <article class="card js-stat-card" data-stat-title="Total users" data-stat-value="4390" data-stat-note="stable">
            <div class="card-top"><span class="mini-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span><span class="mini-arrow"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span></div>
            <h3>Total Users</h3>
            <div class="metric">4,390</div>
            <div class="metric-note">Stable</div>
          </article>
          <article class="card js-stat-card" data-stat-title="Total report" data-stat-value="26" data-stat-note="+5">
            <div class="card-top"><span class="mini-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span><span class="mini-arrow"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span></div>
            <h3>Total Reports</h3>
            <div class="metric">26</div>
            <div class="metric-note bad">+5 pending</div>
          </article>
        </div>

        <section class="stat-panel" id="stat-panel">
          <div class="panel-head">
            <div>
              <h4 id="stat-title">Total flight</h4>
              <p><span id="stat-value">2004</span> <span class="metric-note ok" id="stat-note">+343</span></p>
            </div>
            <button type="button" class="btn-small" id="stat-close">Close</button>
          </div>
          <svg viewBox="0 0 720 170" class="panel-chart" aria-hidden="true">
            <path d="M20 152 L95 108 L170 82 L245 67 L320 63 L395 58 L470 52 L545 45 L620 38 L695 20" fill="none" stroke="#2f67d9" stroke-width="3"/>
            <line x1="20" y1="152" x2="700" y2="152" stroke="#cad7ed"/>
          </svg>
        </section>

        <div class="charts-grid">
          <article class="chart-card">
            <div class="chart-head">
              <div>
                <h3>Swaps</h3>
                <p>Offer &amp; Ask</p>
              </div>
              <div class="chart-tools"><select><option>mon</option><option>week</option><option>year</option></select></div>
            </div>
            <div class="legend-item"><span class="line c1"></span> Avilable <strong>27</strong></div>
            <div class="legend-item"><span class="line c2"></span> Canceled <strong>12</strong></div>
            <div class="legend-item"><span class="line c3"></span> Completed <strong>14</strong></div>
            <svg viewBox="0 0 300 120" class="mini-chart">
              <path d="M10 100 L50 72 L90 80 L130 54 L170 62 L210 40 L250 48 L290 28" fill="none" stroke="#22d3ee" stroke-width="2"/>
              <path d="M10 104 L50 94 L90 96 L130 76 L170 78 L210 66 L250 68 L290 86" fill="none" stroke="#f97316" stroke-width="2"/>
              <path d="M10 108 L50 100 L90 98 L130 98 L170 86 L210 90 L250 76 L290 72" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="4 3"/>
            </svg>
          </article>

          <article class="chart-card">
            <div class="chart-head">
              <h3>Reports <span>/ today</span></h3>
            </div>
            <div class="prog"><label>wait</label><div><span style="width:40%"></span></div><b>40%</b></div>
            <div class="prog"><label class="ok">done</label><div><span class="ok" style="width:60%"></span></div><b class="ok">60%</b></div>
            <div class="donut"></div>
          </article>

          <article class="chart-card">
            <div class="chart-head">
              <div>
                <h3>Flight Amount</h3>
                <p>How many flights are uploaded this week?</p>
              </div>
              <div class="chart-tools"><select><option>week</option><option>mon</option><option>year</option></select></div>
            </div>
            <div class="week-row">
              <span>S</span><span>M</span><span class="on">T</span><span>W</span><span>T</span><span>F</span><span>S</span>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>

  <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
  <script src="assets/js/include.js"></script>
</body>
</html>
