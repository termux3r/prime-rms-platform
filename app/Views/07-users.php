<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Management — Dallol RMS</title>
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
  .badge-active { background: var(--accent-teal-soft); color: var(--accent-teal-ink); }
  .badge-inactive { background: var(--accent-rust-soft); color: var(--accent-rust-ink); }
  .badge-role { background: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }
  .dot { width: 6px; height: 6px; border-radius: 50%; }
  .dot-active { background: var(--accent-teal); }
  .dot-inactive { background: var(--accent-rust); }

  .btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; padding: 10px 16px; border-radius: var(--radius-sm); border: 1px solid transparent; cursor: pointer; }
  .btn-primary { background: var(--ink-primary); color: var(--ink-inverse); }
  .btn-action { background: #eae6dc; color: var(--ink-primary); font-size: 12px; padding: 6px 12px; border-radius: 4px; }

  .modal-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000;
    display: none; align-items: center; justify-content: center;
  }
  .modal-card {
    background: var(--surface-card); width: 100%; max-width: 440px;
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
      <a class="sidebar-link" href="/tables"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg><span class="label">Tables</span></a>
      <a class="sidebar-link" href="/menu-items"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5Z"/><path d="M8 9h5M8 13h8M8 17h8"/></svg><span class="label">Menu Items</span></a>
      <a class="sidebar-link" href="/reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M11 20V4M18 20v-7"/></svg><span class="label">Reports</span></a>
      <a class="sidebar-link active" href="/users"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg><span class="label">Staff</span></a>
    </nav>
    <div class="sidebar-foot">v2.0 · Dallol Tech</div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Staff & Users Management</div>
        <div class="topbar-sub" id="staffSub">Active system accounts & access privileges</div>
      </div>
      <button class="btn btn-primary" onclick="openAddModal()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
        Add Staff User
      </button>
    </div>

    <div class="content">
      <div class="panel">
        <table>
          <thead>
            <tr><th>Full Name</th><th>Username</th><th>Role</th><th>Status</th><th>Toggle Status</th></tr>
          </thead>
          <tbody id="usersTbody">
            <tr><td colspan="5" style="text-align: center; color: var(--ink-secondary);">Loading staff...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal-backdrop" id="addModal">
  <div class="modal-card">
    <h3 style="margin: 0 0 16px; font-family: var(--font-display); font-size: 18px;">Create Staff Account</h3>
    <form onsubmit="handleAddUser(event)">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-input" id="newName" required placeholder="e.g. Yonas Alemu">
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-input" id="newUsername" required placeholder="yonas.cashier">
      </div>
      <div class="form-group">
        <label class="form-label">Email (optional)</label>
        <input class="form-input" id="newEmail" type="email" placeholder="yonas@restaurant.com">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-input" id="newPassword" type="password" required placeholder="••••••••">
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select class="form-input" id="newRole">
          <option value="cashier">Cashier</option>
          <option value="admin">Administrator</option>
        </select>
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
        <button type="button" class="btn" onclick="closeAddModal()" style="background: transparent; border: 1px solid var(--border-strong);">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveUserBtn">Create Account</button>
      </div>
    </form>
  </div>
</div>

<script>
const token = localStorage.getItem('rms_access_token');
if (!token) window.location.href = '/login';

async function loadUsers() {
  try {
    const res = await fetch('/api/v1/users', { headers: { 'Authorization': 'Bearer ' + token } });
    const json = await res.json();
    const tbody = document.getElementById('usersTbody');
    const users = json.data?.users || json.data || [];
    if (json.status === 'success' && Array.isArray(users)) {
      document.getElementById('staffSub').textContent = `${users.length} staff accounts configured`;
      tbody.innerHTML = users.map(u => {
        const isActive = u.status === 'active';
        const nextStatus = isActive ? 'inactive' : 'active';
        return `
          <tr>
            <td><strong>${u.name}</strong></td>
            <td class="mono">${u.username}</td>
            <td><span class="badge badge-role">${u.role}</span></td>
            <td>
              <span class="badge ${isActive ? 'badge-active' : 'badge-inactive'}">
                <span class="dot ${isActive ? 'dot-active' : 'dot-inactive'}"></span>
                ${u.status}
              </span>
            </td>
            <td>
              <button class="btn-action" onclick="toggleStatus(${u.id}, '${nextStatus}')">
                Mark ${nextStatus}
              </button>
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
    const res = await fetch(`/api/v1/users/${id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ status: newStatus })
    });
    if (res.ok) loadUsers();
    else {
      const j = await res.json();
      alert(j.message || 'Action rejected');
    }
  } catch (e) {
    alert('Error updating user status');
  }
}

function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

async function handleAddUser(e) {
  e.preventDefault();
  const name = document.getElementById('newName').value.trim();
  const username = document.getElementById('newUsername').value.trim();
  const email = document.getElementById('newEmail').value.trim();
  const password = document.getElementById('newPassword').value;
  const role = document.getElementById('newRole').value;

  try {
    const res = await fetch('/api/v1/users', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ name, username, email, password, role })
    });
    if (res.ok) {
      closeAddModal();
      loadUsers();
    } else {
      const j = await res.json();
      alert(j.message || 'Error creating user');
    }
  } catch (err) {
    alert('Error creating user');
  }
}

loadUsers();
</script>

</body>
</html>
