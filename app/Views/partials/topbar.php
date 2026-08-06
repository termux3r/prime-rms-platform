<div class="topbar-inner">
  <div class="topbar-left">
    <button class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sidebar">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>
    <div class="topbar-title">
      <div class="eyebrow" id="topbarEyebrow"><?= esc($eyebrow ?? 'Dashboard') ?></div>
      <h1 class="page-title" id="topbarTitle"><?= esc($title ?? 'Dashboard') ?></h1>
    </div>
  </div>

  <div class="topbar-right">
    <div class="connection-status" id="connectionStatus">
      <span class="status-dot" id="connectionDot"></span>
      <span class="status-text" id="connectionText">Connecting...</span>
    </div>
    <button class="btn btn-secondary" type="button" id="refreshPage" aria-label="Refresh data">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
    </button>
    <div class="user-menu">
      <button class="icon-btn" id="userMenuBtn" aria-label="User menu" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      </button>
      <div class="dropdown-menu" id="userDropdown" role="menu" hidden>
        <div class="dropdown-header">
          <div class="dropdown-name" id="dropdownName">Loading...</div>
          <div class="dropdown-role" id="dropdownRole">Loading...</div>
        </div>
        <a class="dropdown-item" href="#" id="profileLink" role="menuitem">Profile</a>
        <a class="dropdown-item" href="#" id="logoutBtn" role="menuitem">Sign out</a>
      </div>
    </div>
  </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

<style>
  .topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
  }

  .topbar-left { display: flex; align-items: center; gap: 16px; }

  .sidebar-toggle { display: none; }
  @media (max-width: 991.98px) { .sidebar-toggle { display: inline-flex; } }

  .topbar-title .eyebrow { margin-bottom: 4px; }
  .topbar-title .page-title { margin: 0; }

  .topbar-right { display: flex; align-items: center; gap: 12px; }

  .connection-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--ink-secondary);
  }
  .connection-status .status-dot {
    width: 7px; height: 7px; border-radius: 50%; background: var(--ink-faint);
  }
  .connection-status.online .status-dot { background: var(--accent-teal); box-shadow: 0 0 0 4px rgba(43,104,95,0.12); }
  .connection-status.error .status-dot { background: var(--accent-rust); box-shadow: 0 0 0 4px rgba(168,69,29,0.12); }

  .user-menu { position: relative; }
  .dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 200px;
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-panel);
    padding: 8px;
    opacity: 0; visibility: hidden; transform: translateY(-8px);
    transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease;
  }
  .dropdown-menu[hidden] { display: none; }
  .dropdown-menu.open { opacity: 1; visibility: visible; transform: translateY(0); }
  .dropdown-header { padding: 8px 12px; border-bottom: 1px solid var(--border-subtle); margin-bottom: 4px; }
  .dropdown-name { font-weight: 600; color: var(--ink-primary); font-size: 14px; }
  .dropdown-role { font-family: var(--font-mono); font-size: 11px; color: var(--ink-secondary); text-transform: uppercase; }
  .dropdown-item {
    display: block; padding: 10px 12px; border-radius: var(--radius-sm);
    color: var(--ink-primary); font-size: 14px; text-decoration: none;
    transition: background 0.15s ease;
  }
  .dropdown-item:hover { background: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }

  .sidebar-overlay { display: none; }
  @media (max-width: 991.98px) { .sidebar-overlay.open { display: block; } }
</style>

<script>
  (function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');
    const userBtn = document.getElementById('userMenuBtn');
    const dropdown = document.getElementById('userDropdown');
    const logoutBtn = document.getElementById('logoutBtn');
    const dropdownName = document.getElementById('dropdownName');
    const dropdownRole = document.getElementById('dropdownRole');

    // Fetch and display user info
    async function loadUserInfo() {
      const token = localStorage.getItem('rms_token');
      if (!token) return;
      
      try {
        const response = await fetch('/api/v1/auth/me', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (response.ok) {
          const data = await response.json();
          const user = data.data || data;
          if (dropdownName) dropdownName.textContent = user.name || 'User';
          if (dropdownRole) dropdownRole.textContent = (user.role || 'cashier').toUpperCase();
        }
      } catch (e) {
        console.error('Failed to load user info:', e);
      }
    }

    if (toggle && sidebar && overlay) {
      toggle.addEventListener('click', function () {
        const isOpen = sidebar.classList.toggle('open');
        overlay.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen);
      });
      overlay.addEventListener('click', function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    }

    if (userBtn && dropdown) {
      userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('open');
        dropdown.hidden = !dropdown.classList.contains('open');
        userBtn.setAttribute('aria-expanded', dropdown.classList.contains('open'));
      });
      document.addEventListener('click', function (e) {
        if (!userBtn.contains(e.target) && !dropdown.contains(e.target)) {
          dropdown.classList.remove('open');
          dropdown.hidden = true;
          userBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }

    if (logoutBtn) {
      logoutBtn.addEventListener('click', async function (e) {
        e.preventDefault();
        try {
          const token = localStorage.getItem('rms_token');
          if (token) {
            await fetch('/api/v1/auth/logout', {
              method: 'POST',
              headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
          }
        } catch (_) {}
        localStorage.removeItem('rms_token');
        window.location.href = '/login';
      });
    }

    const refreshBtn = document.getElementById('refreshPage');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', function () {
        window.location.reload();
      });
    }

    // Load user info on page load
    loadUserInfo();

    // Update connection status indicator
    async function updateConnectionStatus() {
      const statusEl = document.getElementById('connectionStatus');
      const dotEl = document.getElementById('connectionDot');
      const textEl = document.getElementById('connectionText');
      if (!statusEl || !dotEl || !textEl) return;

      const token = localStorage.getItem('rms_token');
      if (!token) {
        statusEl.classList.remove('online');
        dotEl.style.background = '';
        textEl.textContent = 'Offline';
        return;
      }

      try {
        const res = await fetch('/api/v1/auth/me', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (res.ok) {
          statusEl.classList.add('online');
          dotEl.style.background = '';
          textEl.textContent = 'Online';
        } else {
          statusEl.classList.remove('online');
          dotEl.style.background = '';
          textEl.textContent = 'Offline';
        }
      } catch {
        statusEl.classList.remove('online');
        dotEl.style.background = '';
        textEl.textContent = 'Offline';
      }
    }

    updateConnectionStatus();
    // Re-check every 60s
    setInterval(updateConnectionStatus, 60000);
  })();
</script>