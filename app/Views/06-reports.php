<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports & Sales Analytics — Dallol RMS</title>
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
  body { margin: 0; font-family: var(--font-body); color: var(--ink-primary); background: var(--surface-base); }
  a { color: inherit; text-decoration: none; }
  button, input { font-family: inherit; }
  :focus-visible { outline: 2px solid var(--accent-sulfur); outline-offset: 2px; }

  .app-shell { display: grid; grid-template-columns: 248px 1fr; min-height: 100vh; }

  .sidebar { background: var(--surface-sidebar); padding: 24px 16px; display: flex; flex-direction: column; }
  .sidebar-brand { font-family: var(--font-display); font-weight: 900; font-size: 16px; letter-spacing: 0.12em; color: var(--ink-inverse); text-transform: uppercase; padding: 8px 12px 24px; }
  .sidebar-brand span { color: var(--accent-sulfur); }
  .sidebar-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
  .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: var(--radius-sm); color: var(--ink-inverse-muted); font-size: 14px; font-weight: 500; }
  .sidebar-link:hover { background: var(--surface-sidebar-active); color: var(--ink-inverse); }
  .sidebar-link.active { background: var(--surface-sidebar-active); color: var(--ink-inverse); box-shadow: inset 3px 0 0 var(--accent-sulfur); }
  .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; }
  .sidebar-foot { font-family: var(--font-mono); font-size: 11px; color: var(--ink-inverse-muted); padding: 12px; border-top: 1px solid rgba(242,239,231,0.08); }

  .main { display: flex; flex-direction: column; min-width: 0; }

  .topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 32px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-base);
  }
  .topbar-title { font-family: var(--font-display); font-weight: 700; font-size: 20px; }
  .topbar-sub { font-size: 12.5px; color: var(--ink-secondary); margin-top: 2px; }

  .content { padding: 28px 32px 48px; }

  .stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 32px; }
  .stat-card {
    background: var(--surface-card); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg); padding: 22px; box-shadow: var(--shadow-card);
  }
  .stat-card.highlight { background: var(--surface-sidebar); color: var(--ink-inverse); border-color: transparent; }
  .stat-card.highlight .stat-card-label { color: var(--ink-inverse-muted); }
  .stat-card.highlight .stat-card-value { color: var(--accent-sulfur); }
  .stat-card-label { font-size: 12.5px; color: var(--ink-secondary); font-weight: 500; margin-bottom: 8px; }
  .stat-card-value { font-family: var(--font-mono); font-weight: 600; font-size: 26px; }

  .panel {
    background: var(--surface-card); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 24px;
  }
  .panel-header { padding: 18px 20px; border-bottom: 1px solid var(--border-subtle); font-family: var(--font-display); font-weight: 700; }
  table { width: 100%; border-collapse: collapse; }
  thead th {
    text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--ink-secondary); font-weight: 600; padding: 12px 20px; border-bottom: 1px solid var(--border-subtle);
  }
  tbody td { padding: 14px 20px; font-size: 14px; border-bottom: 1px solid var(--border-subtle); }
  tbody tr:last-child td { border-bottom: none; }
  td.mono { font-family: var(--font-mono); font-weight: 600; }

  .btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; padding: 10px 16px; border-radius: var(--radius-sm); border: 1px solid transparent; cursor: pointer; }
  .btn-primary { background: var(--ink-primary); color: var(--ink-inverse); }
</style>
</head>
<body>

<div class="app-shell">
  <aside class="sidebar">
    <div class="sidebar-brand">Dall<span>o</span>l</div>
    <nav class="sidebar-nav">
      <a class="sidebar-link" href="/dashboard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg><span class="label">Dashboard</span></a>
      <a class="sidebar-link" href="/orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14l-1.5 9h-11L5 4Z"/><path d="M9 17a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm7 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/></svg><span class="label">Orders (POS)</span></a>
      <a class="sidebar-link" href="/tables"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg><span class="label">Tables</span></a>
      <a class="sidebar-link" href="/menu-items"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5Z"/><path d="M8 9h5M8 13h8M8 17h8"/></svg><span class="label">Menu Items</span></a>
      <a class="sidebar-link active" href="/reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M11 20V4M18 20v-7"/></svg><span class="label">Reports</span></a>
      <a class="sidebar-link" href="/users"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg><span class="label">Staff</span></a>
    </nav>
    <div class="sidebar-foot">v2.0 · Dallol Tech</div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Financial & Operational Reports</div>
        <div class="topbar-sub">Reconciled daily and monthly business analytics</div>
      </div>
      <div>
        <input type="date" id="reportDate" onchange="loadReports()" style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); margin-right: 8px;">
        <button class="btn btn-primary" onclick="window.print()">Print Report</button>
      </div>
    </div>

    <div class="content">
      <div class="stat-row">
        <div class="stat-card highlight">
          <div class="stat-card-label">Total Daily Sales</div>
          <div class="stat-card-value" id="dailyTotalSales">ETB 0.00</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-label">Orders Settled</div>
          <div class="stat-card-value" id="dailyTotalOrders">0</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-label">Average Order Ticket</div>
          <div class="stat-card-value" id="dailyAvgTicket">ETB 0.00</div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header">Category Sales Breakdown</div>
        <table>
          <thead>
            <tr><th>Category</th><th>Items Sold</th><th class="mono">Total Revenue</th></tr>
          </thead>
          <tbody id="categoryReportTbody">
            <tr><td colspan="3" style="text-align: center; color: var(--ink-secondary);">No category sales data for selected date.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const token = localStorage.getItem('rms_access_token');
if (!token) window.location.href = '/login';

const todayStr = new Date().toISOString().split('T')[0];
document.getElementById('reportDate').value = todayStr;

async function loadReports() {
  const d = document.getElementById('reportDate').value || todayStr;
  try {
    const res = await fetch(`/api/v1/reports/daily?date=${d}`, {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      const data = json.data;
      const totalSales = parseFloat(data.total_sales || 0);
      const totalOrders = parseInt(data.total_orders || 0);
      const avg = totalOrders > 0 ? totalSales / totalOrders : 0;

      document.getElementById('dailyTotalSales').textContent = `ETB ${totalSales.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
      document.getElementById('dailyTotalOrders').textContent = totalOrders;
      document.getElementById('dailyAvgTicket').textContent = `ETB ${avg.toLocaleString('en-US', {minimumFractionDigits: 2})}`;

      const tbody = document.getElementById('categoryReportTbody');
      const cats = data.categories || [];
      if (cats.length > 0) {
        tbody.innerHTML = cats.map(c => `
          <tr>
            <td><strong>${c.category_name}</strong></td>
            <td>${c.quantity_sold || 0}</td>
            <td class="mono">ETB ${parseFloat(c.total_amount || 0).toFixed(2)}</td>
          </tr>
        `).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: var(--ink-secondary);">No sales recorded for this date yet.</td></tr>';
      }
    }
  } catch (e) {
    console.error(e);
  }
}

loadReports();
</script>

</body>
</html>
