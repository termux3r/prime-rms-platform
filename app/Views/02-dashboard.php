<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Dallol RMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;700;900&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --surface-base: #F2EFE7;
    --surface-card: #FFFFFF;
    --surface-sidebar: #17130E;
    --surface-sidebar-active: #241D15;

    --ink-primary: #1C1A16;
    --ink-secondary: #756F60;
    --ink-faint: #A39C8A;
    --ink-inverse: #F2EFE7;
    --ink-inverse-muted: #9C9384;

    --accent-sulfur: #B58A12;
    --accent-sulfur-ink: #7A5C0C;
    --accent-sulfur-soft: #F3E8C9;

    --accent-rust: #A8451D;
    --accent-rust-ink: #7C3315;
    --accent-rust-soft: #F2DED2;

    --accent-teal: #2B685F;
    --accent-teal-ink: #1E4A43;
    --accent-teal-soft: #DAE9E5;

    --border-subtle: #E4DFD1;
    --border-strong: #CFC7B2;

    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 18px;

    --shadow-card: 0 1px 2px rgba(23,19,14,.05), 0 10px 30px -14px rgba(23,19,14,.18);

    --font-display: 'Archivo', sans-serif;
    --font-body: 'Inter', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
  }

  * { box-sizing: border-box; }
  html { -webkit-font-smoothing: antialiased; }
  body {
    margin: 0;
    font-family: var(--font-body);
    color: var(--ink-primary);
    background: var(--surface-base);
  }
  a { color: inherit; text-decoration: none; }
  :focus-visible { outline: 2px solid var(--accent-sulfur); outline-offset: 2px; }

  /* ---------- App shell ---------- */
  .app-shell { display: grid; grid-template-columns: 248px 1fr; min-height: 100vh; }

  .sidebar {
    background: var(--surface-sidebar);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
  }
  .sidebar-brand {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 16px;
    letter-spacing: 0.12em;
    color: var(--ink-inverse);
    text-transform: uppercase;
    padding: 8px 12px 24px;
  }
  .sidebar-brand span { color: var(--accent-sulfur); }
  .sidebar-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
  .sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    color: var(--ink-inverse-muted);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
  }
  .sidebar-link:hover { background: var(--surface-sidebar-active); color: var(--ink-inverse); }
  .sidebar-link.active {
    background: var(--surface-sidebar-active);
    color: var(--ink-inverse);
    box-shadow: inset 3px 0 0 var(--accent-sulfur);
  }
  .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; }
  .sidebar-foot {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--ink-inverse-muted);
    padding: 12px;
    border-top: 1px solid rgba(242,239,231,0.08);
  }

  .main { display: flex; flex-direction: column; min-width: 0; }

  .topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 32px;
    border-bottom: 1px solid var(--border-subtle);
    background: var(--surface-base);
  }
  .topbar-title { font-family: var(--font-display); font-weight: 700; font-size: 20px; }
  .topbar-date { font-family: var(--font-mono); font-size: 12px; color: var(--ink-secondary); margin-top: 2px; }
  .topbar-user { display: flex; align-items: center; gap: 12px; }
  .role-badge {
    font-family: var(--font-mono);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: var(--accent-sulfur-soft);
    color: var(--accent-sulfur-ink);
    padding: 4px 9px;
    border-radius: 100px;
  }
  .avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--ink-primary); color: var(--ink-inverse);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-weight: 700; font-size: 13px;
  }
  .signout-btn {
    font-size: 12px;
    color: var(--accent-rust-ink);
    background: var(--accent-rust-soft);
    border: 1px solid var(--border-subtle);
    padding: 5px 10px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-weight: 600;
  }
  .signout-btn:hover { background: #ebd1c4; }

  .content { padding: 28px 32px 48px; }
  .section-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 15px;
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .section-title .link { font-family: var(--font-body); font-size: 13px; font-weight: 600; color: var(--accent-teal-ink); cursor: pointer; }

  /* ---------- Floor status (hero) ---------- */
  .floor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 12px;
    margin-bottom: 28px;
  }
  .floor-tile {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    box-shadow: var(--shadow-card);
    display: flex;
    flex-direction: column;
    gap: 6px;
    cursor: pointer;
    transition: transform 0.1s ease, border-color 0.15s ease;
  }
  .floor-tile:hover {
    transform: translateY(-2px);
    border-color: var(--accent-sulfur);
  }
  .floor-tile.occupied {
    background: var(--surface-card);
    border-color: var(--accent-sulfur);
  }
  .floor-tile-num { font-family: var(--font-mono); font-weight: 600; font-size: 15px; }
  .floor-tile-status {
    font-size: 11.5px;
    color: var(--ink-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .dot { width: 7px; height: 7px; border-radius: 50%; }
  .dot-available { background: var(--accent-teal); }
  .dot-occupied { background: var(--accent-sulfur); }

  /* ---------- Stats row ---------- */
  .stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 32px;
  }
  .stat-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 22px;
    box-shadow: var(--shadow-card);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .stat-card.highlight {
    background: var(--surface-sidebar);
    color: var(--ink-inverse);
    border-color: transparent;
  }
  .stat-card.highlight .stat-card-label { color: var(--ink-inverse-muted); }
  .stat-card.highlight .stat-card-value { color: var(--accent-sulfur); }
  .stat-card-label {
    font-size: 12.5px;
    color: var(--ink-secondary);
    font-weight: 500;
    margin-bottom: 8px;
  }
  .stat-card-value {
    font-family: var(--font-mono);
    font-weight: 600;
    font-size: 26px;
    letter-spacing: -0.02em;
  }
  .stat-card-rule {
    border: none;
    border-top: 1px dashed rgba(242,239,231,0.2);
    margin: 12px 0 0;
  }

  /* ---------- Panel & Table ---------- */
  .panel {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
  }
  table { width: 100%; border-collapse: collapse; }
  thead th {
    text-align: left;
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--ink-secondary);
    font-weight: 600;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border-subtle);
  }
  tbody td { padding: 14px 20px; font-size: 14px; border-bottom: 1px solid var(--border-subtle); }
  tbody tr:last-child td { border-bottom: none; }
  tbody tr:hover { background: #FAF8F3; }
  td.mono, th.mono { font-family: var(--font-mono); }
  .badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
    padding: 4px 10px; border-radius: 100px;
  }
  .badge-teal { background: var(--accent-teal-soft); color: var(--accent-teal-ink); }
  .badge-sulfur { background: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }
  .badge-rust { background: var(--accent-rust-soft); color: var(--accent-rust-ink); }

  @media (max-width: 980px) {
    .stat-row { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 720px) {
    .app-shell { grid-template-columns: 72px 1fr; }
    .sidebar-brand, .sidebar-link span.label, .sidebar-foot { display: none; }
    .sidebar-link { justify-content: center; }
    .content { padding: 20px 16px 40px; }
    .topbar { padding: 16px 20px; }
    .stat-row { grid-template-columns: 1fr 1fr; }
  }
</style>
</head>
<body>

<div class="app-shell">
  <aside class="sidebar">
    <div class="sidebar-brand">Dall<span>o</span>l</div>
    <nav class="sidebar-nav">
      <a class="sidebar-link active" href="/dashboard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg><span class="label">Dashboard</span></a>
      <a class="sidebar-link" href="/orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14l-1.5 9h-11L5 4Z"/><path d="M9 17a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm7 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/></svg><span class="label">Orders (POS)</span></a>
      <a class="sidebar-link" href="/tables"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg><span class="label">Tables</span></a>
      <a class="sidebar-link" href="/menu-items"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5Z"/><path d="M8 9h5M8 13h8M8 17h8"/></svg><span class="label">Menu Items</span></a>
      <a class="sidebar-link" href="/reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M11 20V4M18 20v-7"/></svg><span class="label">Reports</span></a>
      <a class="sidebar-link" href="/users"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg><span class="label">Staff</span></a>
    </nav>
    <div class="sidebar-foot">v2.0 · Dallol Tech</div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title" id="welcomeMsg">Welcome to Dallol RMS</div>
        <div class="topbar-date" id="currentDateTime">Live Shift Operational Console</div>
      </div>
      <div class="topbar-user">
        <span class="role-badge" id="userRole">Staff</span>
        <div class="avatar" id="userAvatar">D</div>
        <button class="signout-btn" onclick="signOut()">Sign out</button>
      </div>
    </div>

    <div class="content">
      <div class="section-title">Floor status <a href="/orders" class="link">Open Order Screen →</a></div>
      <div class="floor-grid" id="floorGrid">
        <div style="font-size: 13px; color: var(--ink-secondary);">Loading tables...</div>
      </div>

      <div class="stat-row">
        <div class="stat-card">
          <div class="stat-card-label">Menu items</div>
          <div class="stat-card-value" id="statMenuItems">--</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-label">Total Tables</div>
          <div class="stat-card-value" id="statTables">--</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-label">Today's orders</div>
          <div class="stat-card-value" id="statOrders">--</div>
        </div>
        <div class="stat-card highlight">
          <div class="stat-card-label">Today's sales</div>
          <div class="stat-card-value" id="statSales">ETB 0.00</div>
          <hr class="stat-card-rule">
        </div>
      </div>

      <div class="section-title">Recent orders <a href="/orders" class="link">View Order POS →</a></div>
      <div class="panel">
        <table>
          <thead>
            <tr><th class="mono">Order #</th><th>Table</th><th>Status</th><th class="mono">Total</th><th>Action</th></tr>
          </thead>
          <tbody id="ordersTbody">
            <tr><td colspan="5" style="text-align: center; color: var(--ink-secondary);">Loading orders...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const token = localStorage.getItem('rms_access_token');
if (!token) {
  window.location.href = '/login';
}

function signOut() {
  localStorage.removeItem('rms_access_token');
  localStorage.removeItem('rms_refresh_token');
  window.location.href = '/login';
}

async function loadUserData() {
  try {
    const res = await fetch('/api/v1/auth/me', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    if (res.status === 401) { signOut(); return; }
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      const u = json.data;
      document.getElementById('welcomeMsg').textContent = `Good afternoon, ${u.name}`;
      document.getElementById('userRole').textContent = u.role || 'Staff';
      const initials = u.name ? u.name.split(' ').map(n=>n[0]).join('').toUpperCase().slice(0, 2) : 'U';
      document.getElementById('userAvatar').textContent = initials;
    }
  } catch (e) {
    console.error(e);
  }
}

async function loadSummary() {
  try {
    const res = await fetch('/api/v1/dashboard/summary', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      const d = json.data;
      document.getElementById('statMenuItems').textContent = d.total_menu_items ?? 0;
      document.getElementById('statTables').textContent = d.total_tables ?? 0;
      document.getElementById('statOrders').textContent = d.today_orders ?? 0;
      document.getElementById('statSales').textContent = `ETB ${parseFloat(d.today_sales || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    }
  } catch (e) {
    console.error(e);
  }
}

async function loadFloor() {
  try {
    const res = await fetch('/api/v1/tables', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const json = await res.json();
    const grid = document.getElementById('floorGrid');
    if (json.status === 'success' && Array.isArray(json.data) && json.data.length > 0) {
      grid.innerHTML = json.data.map(t => {
        const isOccupied = t.status === 'occupied';
        return `
          <div class="floor-tile ${isOccupied ? 'occupied' : ''}" onclick="window.location.href='/orders?table=${t.id}'">
            <div class="floor-tile-num">${t.table_number}</div>
            <div class="floor-tile-status">
              <span class="dot ${isOccupied ? 'dot-occupied' : 'dot-available'}"></span>
              ${isOccupied ? 'Occupied' : 'Available'}
            </div>
          </div>
        `;
      }).join('');
    } else {
      grid.innerHTML = '<div style="font-size: 13px; color: var(--ink-secondary);">No tables found.</div>';
    }
  } catch (e) {
    console.error(e);
  }
}

async function loadRecentOrders() {
  try {
    const res = await fetch('/api/v1/orders', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const json = await res.json();
    const tbody = document.getElementById('ordersTbody');
    const orders = json.data?.orders || json.data || [];
    if (Array.isArray(orders) && orders.length > 0) {
      tbody.innerHTML = orders.slice(0, 8).map(o => {
        let badgeClass = 'badge-sulfur';
        if (o.status === 'completed' || o.status === 'paid') badgeClass = 'badge-teal';
        if (o.status === 'cancelled') badgeClass = 'badge-rust';
        return `
          <tr>
            <td class="mono">#${o.id}</td>
            <td>Table ${o.table_number || o.table_id}</td>
            <td><span class="badge ${badgeClass}">${o.status}</span></td>
            <td class="mono">ETB ${parseFloat(o.total_amount || 0).toFixed(2)}</td>
            <td><a href="/orders?order=${o.id}&table=${o.table_id}" class="link" style="font-size: 12.5px; color: var(--accent-teal-ink); font-weight: 600;">Open →</a></td>
          </tr>
        `;
      }).join('');
    } else {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: var(--ink-secondary);">No active orders yet. Start one from the POS!</td></tr>';
    }
  } catch (e) {
    console.error(e);
  }
}

const now = new Date();
document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

loadUserData();
loadSummary();
loadFloor();
loadRecentOrders();
</script>

</body>
</html>
