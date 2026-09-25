<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>POS Order Station — Dallol RMS</title>
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
  button, select { font-family: inherit; }
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

  .order-shell { display: grid; grid-template-columns: 1fr 380px; flex: 1; min-height: 0; }

  /* ---- Left: table context + menu ---- */
  .order-main { padding: 24px 28px 32px; overflow-y: auto; }
  .order-context { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
  .order-context h1 { font-family: var(--font-display); font-weight: 700; font-size: 22px; margin: 0; }
  .order-context .meta { font-family: var(--font-mono); font-size: 12.5px; color: var(--ink-secondary); }

  .table-selector {
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-strong);
    background: var(--surface-card);
    font-size: 14px;
    font-weight: 600;
  }

  .tab-row { display: flex; gap: 8px; margin-bottom: 22px; flex-wrap: wrap; }
  .tab {
    font-size: 13.5px; font-weight: 600; padding: 8px 16px;
    border-radius: 100px; border: 1px solid var(--border-subtle);
    color: var(--ink-secondary); background: var(--surface-card); cursor: pointer;
  }
  .tab.active { background: var(--ink-primary); color: var(--ink-inverse); border-color: var(--ink-primary); }

  .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; }
  .menu-item-card {
    background: var(--surface-card); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md); padding: 16px; box-shadow: var(--shadow-card);
    display: flex; flex-direction: column; gap: 10px;
    cursor: pointer;
    transition: transform 0.08s ease, border-color 0.15s ease;
  }
  .menu-item-card:hover {
    transform: translateY(-2px);
    border-color: var(--accent-sulfur);
  }
  .menu-item-swatch {
    height: 64px; border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--accent-sulfur-soft), var(--accent-teal-soft));
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-weight: 900; font-size: 20px; color: var(--ink-secondary);
  }
  .menu-item-name { font-size: 14px; font-weight: 600; line-height: 1.3; }
  .menu-item-desc { font-size: 12px; color: var(--ink-secondary); min-height: 28px; }
  .menu-item-row { display: flex; align-items: center; justify-content: space-between; }
  .menu-item-price { font-family: var(--font-mono); font-weight: 600; font-size: 14px; }
  .add-btn {
    width: 30px; height: 30px; border-radius: 50%;
    border: none; background: var(--ink-primary); color: var(--ink-inverse);
    font-size: 18px; line-height: 1; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
  }
  .add-btn:hover { background: var(--accent-sulfur-ink); }

  /* ---- Right: order / receipt panel ---- */
  .order-panel {
    background: var(--surface-card);
    border-left: 1px solid var(--border-subtle);
    display: flex; flex-direction: column;
    min-height: 100vh;
  }
  .order-panel-head { padding: 22px 22px 14px; border-bottom: 1px solid var(--border-subtle); }
  .order-panel-head .eyebrow { font-family: var(--font-mono); font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-secondary); }
  .order-panel-head h2 { font-family: var(--font-display); font-size: 18px; font-weight: 700; margin: 4px 0 0; }

  .order-lines { flex: 1; overflow-y: auto; padding: 6px 22px; }
  .order-line { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-subtle); }
  .order-line:last-child { border-bottom: none; }
  .order-line-name { font-size: 14px; font-weight: 500; }
  .order-line-qty { font-family: var(--font-mono); font-size: 12.5px; color: var(--ink-secondary); margin-top: 2px; }
  .order-line-price { font-family: var(--font-mono); font-size: 14px; font-weight: 600; }

  .qty-stepper { display: flex; align-items: center; gap: 8px; }
  .qty-stepper button {
    width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--border-strong);
    background: var(--surface-card); font-size: 14px; cursor: pointer; line-height: 1;
    display: flex; align-items: center; justify-content: center;
  }

  .order-summary { padding: 16px 22px 22px; border-top: 1px dashed var(--border-strong); }
  .order-summary-row { display: flex; justify-content: space-between; font-size: 13.5px; color: var(--ink-secondary); padding: 4px 0; }
  .order-summary-row .val { font-family: var(--font-mono); }
  .order-divider { border: none; border-top: 1px dashed var(--border-strong); margin: 10px 0; }
  .order-total-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 18px; }
  .order-total-label { font-family: var(--font-display); font-weight: 700; font-size: 15px; }
  .order-total-value { font-family: var(--font-mono); font-weight: 600; font-size: 26px; }

  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; font-size: 14px; padding: 13px 18px; border-radius: var(--radius-sm); border: 1px solid transparent; cursor: pointer; width: 100%; }
  .btn-primary { background: var(--ink-primary); color: var(--ink-inverse); margin-bottom: 8px; }
  .btn-primary:hover { background: #2c2820; }
  .btn-teal { background: var(--accent-teal); color: white; margin-bottom: 8px; }
  .btn-teal:hover { background: var(--accent-teal-ink); }
  .btn-ghost { background: transparent; border-color: var(--border-strong); color: var(--ink-secondary); }
  .btn-ghost:hover { background: var(--accent-rust-soft); color: var(--accent-rust-ink); border-color: var(--accent-rust-soft); }

  .alert-toast {
    position: fixed; bottom: 24px; right: 24px; z-index: 1000;
    padding: 14px 20px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 600;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15); display: none;
  }
  .alert-toast.success { background: var(--accent-teal); color: white; }
  .alert-toast.error { background: var(--accent-rust); color: white; }

  @media (max-width: 980px) {
    .order-shell { grid-template-columns: 1fr; }
    .order-panel { border-left: none; border-top: 1px solid var(--border-subtle); }
  }
  @media (max-width: 720px) {
    .app-shell { grid-template-columns: 72px 1fr; }
    .sidebar-brand, .sidebar-link span.label, .sidebar-foot { display: none; }
    .sidebar-link { justify-content: center; }
    .order-main { padding: 18px 16px; }
  }
</style>
</head>
<body>

<div class="app-shell">
  <aside class="sidebar">
    <div class="sidebar-brand">Dall<span>o</span>l</div>
    <nav class="sidebar-nav">
      <a class="sidebar-link" href="/dashboard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg><span class="label">Dashboard</span></a>
      <a class="sidebar-link active" href="/orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14l-1.5 9h-11L5 4Z"/><path d="M9 17a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm7 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/></svg><span class="label">Orders (POS)</span></a>
      <a class="sidebar-link" href="/tables"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg><span class="label">Tables</span></a>
      <a class="sidebar-link" href="/menu-items"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5Z"/><path d="M8 9h5M8 13h8M8 17h8"/></svg><span class="label">Menu Items</span></a>
      <a class="sidebar-link" href="/reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M11 20V4M18 20v-7"/></svg><span class="label">Reports</span></a>
      <a class="sidebar-link" href="/users"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg><span class="label">Staff</span></a>
    </nav>
    <div class="sidebar-foot">v2.0 · Dallol Tech</div>
  </aside>

  <div class="main">
    <div class="order-shell">
      <!-- Left: menu catalog -->
      <div class="order-main">
        <div class="order-context">
          <div>
            <h1 id="selectedTableTitle">Select Dining Table</h1>
            <div class="meta" id="tableMetaInfo">Select a table to start an order ticket</div>
          </div>
          <div>
            <select class="table-selector" id="tableSelect" onchange="changeTable()">
              <option value="">Choose table...</option>
            </select>
          </div>
        </div>

        <div class="tab-row" id="categoriesTabs">
          <button class="tab active" onclick="filterCategory(null, this)">All Items</button>
        </div>

        <div class="menu-grid" id="menuGrid">
          <div style="font-size: 14px; color: var(--ink-secondary);">Loading menu items...</div>
        </div>
      </div>

      <!-- Right: Ticket / POS Cart -->
      <div class="order-panel">
        <div class="order-panel-head">
          <div class="eyebrow" id="ticketNumber">Ticket #New</div>
          <h2 id="ticketTable">Current Order</h2>
        </div>

        <div class="order-lines" id="cartLines">
          <div style="padding: 30px 0; text-align: center; color: var(--ink-secondary); font-size: 13.5px;">
            No items added yet.<br>Click any menu item to add.
          </div>
        </div>

        <div class="order-summary">
          <div class="order-summary-row">
            <span>Subtotal</span>
            <span class="val" id="subtotalVal">ETB 0.00</span>
          </div>
          <div class="order-summary-row">
            <span>Tax (15% VAT)</span>
            <span class="val" id="taxVal">ETB 0.00</span>
          </div>
          <hr class="order-divider">
          <div class="order-total-row">
            <span class="order-total-label">Total</span>
            <span class="order-total-value" id="totalVal">ETB 0.00</span>
          </div>

          <button class="btn btn-primary" id="placeOrderBtn" onclick="placeOrder()">
            Place Order Ticket
          </button>
          <button class="btn btn-teal" id="payBillBtn" onclick="generateAndPay()" style="display:none;">
            Generate Bill & Pay (Cash)
          </button>
          <button class="btn btn-ghost" onclick="clearCart()">
            Clear Ticket
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="alert-toast" id="toast">Notification</div>

<script>
const token = localStorage.getItem('rms_access_token');
if (!token) {
  window.location.href = '/login';
}

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `alert-toast ${isError ? 'error' : 'success'}`;
  t.style.display = 'block';
  setTimeout(() => { t.style.display = 'none'; }, 3500);
}

let allMenuItems = [];
let activeOrder = null;
let cart = {}; // { menuItemId: { item, qty } }

async function loadTables() {
  try {
    const res = await fetch('/api/v1/tables', { headers: { 'Authorization': 'Bearer ' + token } });
    const json = await res.json();
    const sel = document.getElementById('tableSelect');
    if (json.status === 'success' && Array.isArray(json.data)) {
      sel.innerHTML = '<option value="">Choose table...</option>' + json.data.map(t => {
        return `<option value="${t.id}">Table ${t.table_number} (${t.status}, cap: ${t.capacity})</option>`;
      }).join('');

      const urlParams = new URLSearchParams(window.location.search);
      const tid = urlParams.get('table');
      if (tid) {
        sel.value = tid;
        changeTable();
      }
    }
  } catch (e) {
    console.error(e);
  }
}

function changeTable() {
  const sel = document.getElementById('tableSelect');
  const tableId = sel.value;
  if (!tableId) {
    document.getElementById('selectedTableTitle').textContent = 'Select Dining Table';
    document.getElementById('tableMetaInfo').textContent = 'Select a table to start an order ticket';
    document.getElementById('ticketTable').textContent = 'Current Order';
    return;
  }
  const text = sel.options[sel.selectedIndex].text;
  document.getElementById('selectedTableTitle').textContent = text.split(' (')[0];
  document.getElementById('tableMetaInfo').textContent = `Live Service Ticket · Table #${tableId}`;
  document.getElementById('ticketTable').textContent = text.split(' (')[0];
}

async function loadCategories() {
  try {
    const res = await fetch('/api/v1/menu-categories', { headers: { 'Authorization': 'Bearer ' + token } });
    const json = await res.json();
    const row = document.getElementById('categoriesTabs');
    if (json.status === 'success' && Array.isArray(json.data)) {
      json.data.forEach(c => {
        const btn = document.createElement('button');
        btn.className = 'tab';
        btn.textContent = c.name;
        btn.onclick = () => filterCategory(c.id, btn);
        row.appendChild(btn);
      });
    }
  } catch (e) {
    console.error(e);
  }
}

async function loadMenuItems() {
  try {
    const res = await fetch('/api/v1/menu-items', { headers: { 'Authorization': 'Bearer ' + token } });
    const json = await res.json();
    if (json.status === 'success') {
      allMenuItems = json.data?.items || json.data || [];
      renderMenu(allMenuItems);
    }
  } catch (e) {
    console.error(e);
  }
}

function filterCategory(catId, btnEl) {
  document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
  btnEl.classList.add('active');
  if (!catId) {
    renderMenu(allMenuItems);
  } else {
    renderMenu(allMenuItems.filter(i => i.category_id == catId));
  }
}

function renderMenu(items) {
  const grid = document.getElementById('menuGrid');
  if (!items || items.length === 0) {
    grid.innerHTML = '<div style="color: var(--ink-secondary);">No menu items found in this section.</div>';
    return;
  }
  grid.innerHTML = items.map(item => {
    const initial = item.name.charAt(0).toUpperCase();
    return `
      <div class="menu-item-card" onclick="addToCart(${item.id})">
        <div class="menu-item-swatch">${initial}</div>
        <div class="menu-item-name">${item.name}</div>
        <div class="menu-item-desc">${item.description || 'Fresh kitchen preparation'}</div>
        <div class="menu-item-row">
          <span class="menu-item-price">ETB ${parseFloat(item.price).toFixed(2)}</span>
          <button class="add-btn" type="button" onclick="event.stopPropagation(); addToCart(${item.id})">+</button>
        </div>
      </div>
    `;
  }).join('');
}

function addToCart(itemId) {
  const item = allMenuItems.find(i => i.id == itemId);
  if (!item) return;
  if (!cart[itemId]) {
    cart[itemId] = { item, qty: 1 };
  } else {
    cart[itemId].qty++;
  }
  renderCart();
}

function updateQty(itemId, delta) {
  if (!cart[itemId]) return;
  cart[itemId].qty += delta;
  if (cart[itemId].qty <= 0) {
    delete cart[itemId];
  }
  renderCart();
}

function clearCart() {
  cart = {};
  activeOrder = null;
  document.getElementById('ticketNumber').textContent = 'Ticket #New';
  document.getElementById('placeOrderBtn').style.display = 'inline-flex';
  document.getElementById('payBillBtn').style.display = 'none';
  renderCart();
}

function renderCart() {
  const container = document.getElementById('cartLines');
  const items = Object.values(cart);
  if (items.length === 0) {
    container.innerHTML = `
      <div style="padding: 30px 0; text-align: center; color: var(--ink-secondary); font-size: 13.5px;">
        No items added yet.<br>Click any menu item to add.
      </div>
    `;
    document.getElementById('subtotalVal').textContent = 'ETB 0.00';
    document.getElementById('taxVal').textContent = 'ETB 0.00';
    document.getElementById('totalVal').textContent = 'ETB 0.00';
    return;
  }

  let subtotal = 0;
  container.innerHTML = items.map(({ item, qty }) => {
    const lineTotal = item.price * qty;
    subtotal += lineTotal;
    return `
      <div class="order-line">
        <div>
          <div class="order-line-name">${item.name}</div>
          <div class="order-line-qty">ETB ${parseFloat(item.price).toFixed(2)} ea</div>
        </div>
        <div style="display: flex; align-items: center; gap: 14px;">
          <div class="qty-stepper">
            <button onclick="updateQty(${item.id}, -1)">−</button>
            <span style="font-family: var(--font-mono); font-weight: 600; font-size: 13px;">${qty}</span>
            <button onclick="updateQty(${item.id}, 1)">+</button>
          </div>
          <div class="order-line-price">ETB ${lineTotal.toFixed(2)}</div>
        </div>
      </div>
    `;
  }).join('');

  const tax = subtotal * 0.15;
  const total = subtotal + tax;

  document.getElementById('subtotalVal').textContent = `ETB ${subtotal.toFixed(2)}`;
  document.getElementById('taxVal').textContent = `ETB ${tax.toFixed(2)}`;
  document.getElementById('totalVal').textContent = `ETB ${total.toFixed(2)}`;
}

async function placeOrder() {
  const tableId = document.getElementById('tableSelect').value;
  if (!tableId) {
    showToast('Please select a dining table first!', true);
    return;
  }
  const items = Object.values(cart);
  if (items.length === 0) {
    showToast('Please add at least one menu item to the ticket.', true);
    return;
  }

  const btn = document.getElementById('placeOrderBtn');
  btn.disabled = true;
  btn.textContent = 'Submitting...';

  try {
    // 1. Create order
    const orderRes = await fetch('/api/v1/orders', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ table_id: parseInt(tableId) })
    });
    const orderData = await orderRes.json();
    if (!orderRes.ok || orderData.status !== 'success') {
      showToast(orderData.message || 'Failed to create order', true);
      btn.disabled = false;
      btn.textContent = 'Place Order Ticket';
      return;
    }

    const orderId = orderData.data.id;
    activeOrder = orderData.data;

    // 2. Add items
    for (const { item, qty } of items) {
      await fetch(`/api/v1/orders/${orderId}/items`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
        body: JSON.stringify({ menu_item_id: item.id, quantity: qty })
      });
    }

    // 3. Mark complete to allow billing
    await fetch(`/api/v1/orders/${orderId}/complete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
    });

    document.getElementById('ticketNumber').textContent = `Ticket #${orderId} (Completed)`;
    showToast(`Order #${orderId} created & locked for billing!`);
    btn.style.display = 'none';
    document.getElementById('payBillBtn').style.display = 'inline-flex';
  } catch (err) {
    showToast('Network error processing order', true);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Place Order Ticket';
  }
}

async function generateAndPay() {
  if (!activeOrder) return;
  const payBtn = document.getElementById('payBillBtn');
  payBtn.disabled = true;
  payBtn.textContent = 'Processing Payment...';

  try {
    // Generate bill
    const billRes = await fetch(`/api/v1/orders/${activeOrder.id}/bill`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
    });
    const billData = await billRes.json();
    if (!billRes.ok || billData.status !== 'success') {
      showToast(billData.message || 'Failed to generate bill', true);
      payBtn.disabled = false;
      payBtn.textContent = 'Generate Bill & Pay (Cash)';
      return;
    }

    const billId = billData.data.id;

    // Pay bill
    const payRes = await fetch(`/api/v1/bills/${billId}/pay`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ payment_method: 'cash' })
    });
    const payResult = await payRes.json();
    if (payRes.ok && payResult.status === 'success') {
      showToast(`Bill paid successfully in Cash! Table released.`);
      setTimeout(() => {
        clearCart();
        loadTables();
      }, 1200);
    } else {
      showToast(payResult.message || 'Failed to record payment', true);
    }
  } catch (err) {
    showToast('Error recording payment', true);
  } finally {
    payBtn.disabled = false;
    payBtn.textContent = 'Generate Bill & Pay (Cash)';
  }
}

loadTables();
loadCategories();
loadMenuItems();
</script>

</body>
</html>
