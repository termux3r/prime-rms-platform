<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dining Tables — Dallol RMS</title>
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
  button, select, input { font-family: inherit; }
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

  .panel {
    background: var(--surface-card); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-card); overflow: hidden;
  }
  table { width: 100%; border-collapse: collapse; }
  thead th {
    text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--ink-secondary); font-weight: 600; padding: 12px 20px; border-bottom: 1px solid var(--border-subtle);
  }
  tbody td { padding: 14px 20px; font-size: 14px; border-bottom: 1px solid var(--border-subtle); }
  tbody tr:last-child td { border-bottom: none; }
  tbody tr:hover { background: #FAF8F3; }
  td.mono { font-family: var(--font-mono); font-weight: 600; }

  .badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 100px; }
  .badge-avail { background: var(--accent-teal-soft); color: var(--accent-teal-ink); }
  .badge-occ { background: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }
  .dot { width: 6px; height: 6px; border-radius: 50%; }
  .dot-avail { background: var(--accent-teal); }
  .dot-occ { background: var(--accent-sulfur); }

  .btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; padding: 10px 16px; border-radius: var(--radius-sm); border: 1px solid transparent; cursor: pointer; }
  .btn-primary { background: var(--ink-primary); color: var(--ink-inverse); }
  .btn-primary:hover { background: #2c2820; }
  .btn-action { background: #eae6dc; color: var(--ink-primary); font-size: 12px; padding: 6px 12px; border-radius: 4px; }
  .btn-action:hover { background: var(--accent-sulfur-soft); }

  .modal-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000;
    display: none; align-items: center; justify-content: center;
  }
  .modal-card {
    background: var(--surface-card); width: 100%; max-width: 400px;
    border-radius: var(--radius-lg); padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  }
  .form-group { margin-bottom: 16px; }
  .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
  .form-input { width: 100%; padding: 10px 12px; border: 1px solid var(--border-strong); border-radius: var(--radius-sm); }
</style>
</head>
<body>

<div class="app-shell">
  <aside class="sidebar">
    <div class="sidebar-brand">Dall<span>o</span>l</div>
    <nav class="sidebar-nav">
      <a class="sidebar-link" href="/dashboard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg><span class="label">Dashboard</span></a>
      <a class="sidebar-link" href="/orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14l-1.5 9h-11L5 4Z"/><path d="M9 17a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm7 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/></svg><span class="label">Orders (POS)</span></a>
      <a class="sidebar-link active" href="/tables"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg><span class="label">Tables</span></a>
      <a class="sidebar-link" href="/menu-items"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5Z"/><path d="M8 9h5M8 13h8M8 17h8"/></svg><span class="label">Menu Items</span></a>
      <a class="sidebar-link" href="/reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M11 20V4M18 20v-7"/></svg><span class="label">Reports</span></a>
      <a class="sidebar-link" href="/users"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg><span class="label">Staff</span></a>
    </nav>
    <div class="sidebar-foot">v2.0 · Dallol Tech</div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Floor Management</div>
        <div class="topbar-sub" id="tableCountSub">Real-time table seating & status</div>
      </div>
      <button class="btn btn-primary" onclick="openAddModal()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
        Add Table
      </button>
    </div>

    <div class="content">
      <div class="panel">
        <table>
          <thead>
            <tr><th>Table Number</th><th>Seating Capacity</th><th>Status</th><th>Toggle Status</th><th>Action</th></tr>
          </thead>
          <tbody id="tablesTbody">
            <tr><td colspan="5" style="text-align: center; color: var(--ink-secondary);">Loading tables...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Table Modal -->
<div class="modal-backdrop" id="addModal">
  <div class="modal-card">
    <h3 style="margin: 0 0 16px; font-family: var(--font-display); font-size: 18px;">Add New Table</h3>
    <form onsubmit="handleAddTable(event)">
      <div class="form-group">
        <label class="form-label">Table Number / Label</label>
        <input class="form-input" id="newTableNum" required placeholder="e.g. T-10 or VIP-2">
      </div>
      <div class="form-group">
        <label class="form-label">Guest Capacity</label>
        <input class="form-input" id="newTableCap" type="number" min="1" max="20" required value="4">
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
        <button type="button" class="btn" onclick="closeAddModal()" style="background: transparent; border: 1px solid var(--border-strong);">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveTableBtn">Create Table</button>
      </div>
    </form>
  </div>
</div>

<script>
const token = localStorage.getItem('rms_access_token');
if (!token) window.location.href = '/login';

async function loadTables() {
  try {
    const res = await fetch('/api/v1/tables', { headers: { 'Authorization': 'Bearer ' + token } });
    const json = await res.json();
    const tbody = document.getElementById('tablesTbody');
    if (json.status === 'success' && Array.isArray(json.data)) {
      document.getElementById('tableCountSub').textContent = `${json.data.length} tables configured on floor`;
      tbody.innerHTML = json.data.map(t => {
        const isOcc = t.status === 'occupied';
        const nextStatus = isOcc ? 'available' : 'occupied';
        return `
          <tr>
            <td class="mono">Table ${t.table_number}</td>
            <td>${t.capacity} Guests</td>
            <td>
              <span class="badge ${isOcc ? 'badge-occ' : 'badge-avail'}">
                <span class="dot ${isOcc ? 'dot-occ' : 'dot-avail'}"></span>
                ${isOcc ? 'Occupied' : 'Available'}
              </span>
            </td>
            <td>
              <button class="btn-action" onclick="toggleStatus(${t.id}, '${nextStatus}')">
                Mark ${nextStatus}
              </button>
            </td>
            <td>
              <a href="/orders?table=${t.id}" class="btn-action" style="font-weight: 600; color: var(--accent-teal-ink);">
                Start Order →
              </a>
            </td>
          </tr>
        `;
      }).join('');
    }
  } catch (e) {
    console.error(e);
  }
}

async function toggleStatus(id, newStatus) {
  try {
    const res = await fetch(`/api/v1/tables/${id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ status: newStatus })
    });
    if (res.ok) loadTables();
  } catch (e) {
    alert('Error updating status');
  }
}

function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

async function handleAddTable(e) {
  e.preventDefault();
  const num = document.getElementById('newTableNum').value.trim();
  const cap = parseInt(document.getElementById('newTableCap').value);
  try {
    const res = await fetch('/api/v1/tables', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ table_number: num, capacity: cap })
    });
    if (res.ok) {
      closeAddModal();
      loadTables();
    } else {
      const j = await res.json();
      alert(j.message || 'Error creating table');
    }
  } catch (err) {
    alert('Error creating table');
  }
}

loadTables();
</script>

</body>
</html>
