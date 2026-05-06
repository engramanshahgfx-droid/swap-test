/* ============================================================
   CrewSwap Admin – include.js
   Sidebar, tabs, inline forms, modals, table search, CSRF
   ============================================================ */

(function () {
  'use strict';

  /* ── CSRF token for all fetch/XHR requests ─────────────── */
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const CSRF = csrfMeta ? csrfMeta.content : '';

  /* ── Sidebar collapse ───────────────────────────────────── */
  const sidebar   = document.getElementById('sidebar');
  const sideToggle = document.getElementById('sidebar-toggle');
  const menuBtn   = document.getElementById('menu-toggle');
  const backdrop  = document.getElementById('sidebar-backdrop');

  function isMobile() { return window.innerWidth <= 900; }

  if (sideToggle) {
    sideToggle.addEventListener('click', () => {
      if (isMobile()) {
        sidebar.classList.remove('open');
        backdrop && backdrop.classList.remove('open');
      } else {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
      }
    });
  }

  if (menuBtn) {
    menuBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      backdrop && backdrop.classList.toggle('open');
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', () => {
      sidebar.classList.remove('open');
      backdrop.classList.remove('open');
    });
  }

  // Restore collapsed state on desktop
  if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
    document.body.classList.add('sidebar-collapsed');
  }

  /* ── Active nav item ────────────────────────────────────── */
  const page = document.body.getAttribute('data-page');
  if (page) {
    document.querySelectorAll('[data-nav="' + page + '"]').forEach(el => el.classList.add('active'));
    // Open submenu if a child is active
    const sub = document.querySelector('[data-nav="' + page + '"]')?.closest('.sub-menu');
    if (sub) {
      sub.classList.add('open');
      const trigger = sub.previousElementSibling;
      if (trigger) trigger.classList.add('open');
    }
  }

  /* ── Config submenu toggle ──────────────────────────────── */
  const configTrigger = document.getElementById('config-trigger');
  const configMenu    = document.getElementById('config-menu');

  if (configTrigger && configMenu) {
    // Auto-open if active page is in config
    const configPages = ['airlines', 'positions', 'settings'];
    if (configPages.includes(page)) {
      configMenu.classList.add('open');
      configTrigger.classList.add('open');
    }

    configTrigger.addEventListener('click', () => {
      configMenu.classList.toggle('open');
      configTrigger.classList.toggle('open');
    });
  }

  /* ── Tabs ───────────────────────────────────────────────── */
  document.querySelectorAll('[data-tabs]').forEach(tabsEl => {
    const buttons = tabsEl.querySelectorAll('[data-tab]');
    const panes   = tabsEl.closest('.content, body').querySelectorAll('[data-pane]');

    function activateTab(tabName) {
      buttons.forEach(b => b.classList.toggle('active', b.getAttribute('data-tab') === tabName));
      panes.forEach(p => p.classList.toggle('active', p.getAttribute('data-pane') === tabName));
    }

    buttons.forEach(btn => {
      btn.addEventListener('click', () => activateTab(btn.getAttribute('data-tab')));
    });
  });

  /* ── Inline table search ────────────────────────────────── */
  document.querySelectorAll('.js-table-search').forEach(input => {
    input.addEventListener('input', () => {
      const term = input.value.toLowerCase().trim();
      const table = document.querySelector(input.closest('.actions, .tab-pane, .content')?.querySelector('table') ? null : 'table');
      // Find nearest table
      const container = input.closest('.tab-pane, .content, section');
      const tbl = container ? container.querySelector('table') : document.querySelector('table');
      if (!tbl) return;
      tbl.querySelectorAll('tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  });

  /* ── Airlines inline form ───────────────────────────────── */
  const openAirlineBtn   = document.querySelector('.js-open-airline-form');
  const cancelAirlineBtn = document.querySelector('.js-cancel-airline-form');
  const airlineForm      = document.getElementById('create-airline-form');
  const submitAirlineBtn = document.querySelector('.js-submit-airline-form');

  if (openAirlineBtn && airlineForm) {
    openAirlineBtn.addEventListener('click', () => {
      airlineForm.style.display = airlineForm.style.display === 'none' ? 'block' : 'none';
    });
  }
  if (cancelAirlineBtn && airlineForm) {
    cancelAirlineBtn.addEventListener('click', () => { airlineForm.style.display = 'none'; });
  }
  if (submitAirlineBtn) {
    submitAirlineBtn.addEventListener('click', () => {
      const form = document.getElementById('airline-create-form-el');
      if (form) form.submit();
    });
  }

  /* ── Positions inline form ──────────────────────────────── */
  const openPosBtn   = document.querySelector('.js-open-position-form');
  const cancelPosBtn = document.querySelector('.js-cancel-position-form');
  const posForm      = document.getElementById('position-create-form');
  const submitPosBtn = document.querySelector('.js-submit-position-form');

  if (openPosBtn && posForm) {
    openPosBtn.addEventListener('click', () => {
      posForm.style.display = posForm.style.display === 'none' ? 'block' : 'none';
    });
  }
  if (cancelPosBtn && posForm) {
    cancelPosBtn.addEventListener('click', () => { posForm.style.display = 'none'; });
  }
  if (submitPosBtn) {
    submitPosBtn.addEventListener('click', () => {
      const form = document.getElementById('position-create-form-el');
      if (form) form.submit();
    });
  }

  /* ── Generic modal ──────────────────────────────────────── */
  function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
  }
  function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
  }

  // Close modal on backdrop click
  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', e => {
      if (e.target === backdrop) backdrop.classList.remove('open');
    });
  });
  // Close on Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
  });

  // Expose globally for onclick handlers in templates
  window.openModal  = openModal;
  window.closeModal = closeModal;

  /* ── Edit airline modal ─────────────────────────────────── */
  window.editAirline = function (id, name, code, country) {
    const modal = document.getElementById('edit-airline-modal');
    if (!modal) return;
    modal.querySelector('[name="name"]').value    = name;
    modal.querySelector('[name="code"]').value    = code;
    modal.querySelector('[name="country"]').value = country;
    modal.querySelector('form').action = '/airlines/' + id;
    openModal('edit-airline-modal');
  };

  /* ── Edit position modal ────────────────────────────────── */
  window.editPosition = function (id, name, slug, level, description) {
    const modal = document.getElementById('edit-position-modal');
    if (!modal) return;
    modal.querySelector('[name="name"]').value = name;
    modal.querySelector('[name="slug"]').value = slug;
    const levelInput = modal.querySelector('[name="level"]');
    const descriptionInput = modal.querySelector('[name="description"]');
    if (levelInput) levelInput.value = level;
    if (descriptionInput) descriptionInput.value = description;
    modal.querySelector('form').action = '/positions/' + id;
    openModal('edit-position-modal');
  };

  /* ── Update status modals (swap / report / user) ─────────── */
  window.setSwapStatus = function (id, currentStatus) {
    const modal = document.getElementById('status-modal');
    if (!modal) return;
    modal.querySelector('[name="status"]').value = currentStatus;
    modal.querySelector('form').action = '/swap-flight/' + id + '/status';
    openModal('status-modal');
  };

  window.setReportStatus = function (id, currentStatus) {
    const modal = document.getElementById('report-status-modal');
    if (!modal) return;
    modal.querySelector('[name="status"]').value = currentStatus;
    modal.querySelector('form').action = '/reports/' + id + '/status';
    openModal('report-status-modal');
  };

  window.setUserStatus = function (id, currentStatus) {
    const modal = document.getElementById('user-status-modal');
    if (!modal) return;
    modal.querySelector('[name="status"]').value = currentStatus;
    modal.querySelector('form').action = '/activation/' + id + '/status';
    openModal('user-status-modal');
  };

  /* ── Auto-dismiss flash alerts ──────────────────────────── */
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 4000);
  });

  /* ── Support: chat item selection ──────────────────────── */
  const chatSearchInput = document.querySelector('.js-chat-search');
  if (chatSearchInput) {
    chatSearchInput.addEventListener('input', () => {
      const term = chatSearchInput.value.toLowerCase().trim();
      document.querySelectorAll('.chat-list .chat-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(term) ? '' : 'none';
      });
    });
  }

  document.querySelectorAll('.chat-item[data-user]').forEach(item => {
    item.addEventListener('click', e => {
      e.preventDefault();
      const userId = item.getAttribute('data-user');
      window.location.href = '/support?user_id=' + userId;
    });
  });

  /* ── Dashboard stat focus panel ───────────────────────── */
  if (page === 'dashboard') {
    const statCards = document.querySelectorAll('.js-stat-card');
    const statPanel = document.getElementById('dashboard-stat-panel');
    const statTitle = document.getElementById('dashboard-stat-title');
    const statValue = document.getElementById('dashboard-stat-value');
    const statTrend = document.getElementById('dashboard-stat-trend');
    const statClose = document.getElementById('dashboard-stat-close');

    function applyCard(card) {
      if (!card || !statTitle || !statValue || !statTrend || !statPanel) return;

      statCards.forEach(el => el.classList.remove('is-active'));
      card.classList.add('is-active');

      statTitle.textContent = card.getAttribute('data-stat-title') || '';
      statValue.textContent = card.getAttribute('data-stat-value') || '';
      statTrend.textContent = card.getAttribute('data-stat-trend') || '';

      statTrend.classList.remove('up', 'down');
      const trendClass = card.getAttribute('data-stat-trend-class');
      if (trendClass === 'up' || trendClass === 'down') {
        statTrend.classList.add(trendClass);
      }

      statPanel.classList.remove('is-hidden');
    }

    statCards.forEach(card => {
      card.addEventListener('click', () => applyCard(card));
    });

    if (statClose && statPanel) {
      statClose.addEventListener('click', () => {
        statPanel.classList.add('is-hidden');
      });
    }

    const current = document.querySelector('.js-stat-card.is-active') || statCards[0];
    applyCard(current);
  }

})();

