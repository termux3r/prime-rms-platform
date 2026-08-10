<?= $this->extend(($isKiosk ?? false) ? 'layouts/guest' : 'layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .pos-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 0;
    min-height: calc(100vh - 72px);
  }

  .pos-menu-pane {
    padding: 20px;
    overflow-y: auto;
  }

  .pos-cart-pane {
    background: var(--surface-card);
    border-left: 1px solid var(--border-subtle);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 72px;
    height: calc(100vh - 72px);
    overflow: hidden;
  }

  .pos-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .pos-topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .pos-topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .table-select-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .table-select-wrapper label {
    font-family: var(--font-mono);
    font-size: 13px;
    font-weight: 600;
    color: var(--ink-secondary);
    white-space: nowrap;
  }

  .table-select {
    min-width: 180px;
  }

  .category-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
    overflow-x: auto;
    padding-bottom: 8px;
  }

  .category-tab {
    padding: 10px 16px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-strong);
    background: var(--surface-card);
    color: var(--ink-secondary);
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
  }

  .category-tab:hover {
    border-color: var(--accent-sulfur);
    color: var(--accent-sulfur-ink);
  }

  .category-tab.active {
    background: var(--accent-sulfur);
    border-color: var(--accent-sulfur);
    color: var(--ink-inverse);
  }

  .menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
  }

  .menu-card-pos {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    cursor: pointer;
  }

  .menu-card-pos:hover {
    transform: translateY(-3px);
    box-shadow: 0 24px 60px rgba(23,19,14,.12);
  }

  .menu-card-image {
    height: 120px;
    background:
      radial-gradient(circle at 20% 20%, rgba(181,138,18,0.28), transparent 30%),
      radial-gradient(circle at 80% 15%, rgba(43,104,95,0.18), transparent 28%),
      linear-gradient(135deg, #fef3c7, #f2ded2);
    border-bottom: 1px solid var(--border-subtle);
    position: relative;
    overflow: hidden;
  }

  .menu-card-image.has-photo {
    background-size: cover;
    background-position: center;
  }

  .menu-card-body {
    padding: 14px;
  }

  .menu-card-category {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--accent-teal-ink);
    background: var(--accent-teal-soft);
    padding: 3px 8px;
    border-radius: 999px;
    display: inline-block;
    margin-bottom: 8px;
  }

  .menu-card-name {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 6px;
  }

  .menu-card-price {
    font-family: var(--font-mono);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink-primary);
  }

  /* Invalid table selection (kiosk) */
  .table-select.is-invalid {
    border-color: var(--accent-rust);
    box-shadow: 0 0 0 3px var(--accent-rust-soft);
  }

  .menu-card-add {
    width: 100%;
    margin-top: 12px;
    padding: 10px;
    font-size: 13px;
  }

  /* Cart Panel Styles */
  .cart-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .cart-title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 700;
    margin: 0;
  }

  .cart-count {
    font-family: var(--font-mono);
    font-size: 13px;
    background: var(--accent-teal-soft);
    color: var(--accent-teal-ink);
    padding: 4px 10px;
    border-radius: 999px;
  }

  .cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
  }

  .cart-empty {
    text-align: center;
    color: var(--ink-secondary);
    padding: 40px 20px;
  }

  .cart-empty-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    opacity: 0.4;
  }

  .cart-item {
    border-bottom: 1px solid var(--border-subtle);
    padding: 16px 0;
  }

  .cart-item:last-child {
    border-bottom: none;
  }

  .cart-item-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
  }

  .cart-item-name {
    font-weight: 600;
    font-size: 14px;
    flex: 1;
  }

  .cart-item-price {
    font-family: var(--font-mono);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-primary);
    white-space: nowrap;
  }

  .cart-item-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .qty-stepper {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--border-strong);
    border-radius: 999px;
    background: var(--surface-card);
  }

  .qty-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: var(--accent-sulfur-soft);
    color: var(--accent-sulfur-ink);
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    border-radius: 50%;
    transition: background 0.15s ease;
  }

  .qty-btn:hover:not(:disabled) {
    background: var(--accent-sulfur);
    color: var(--ink-inverse);
  }

  .qty-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .qty-btn:first-child {
    border-radius: 50% 0 0 50%;
    border-right: 1px solid var(--border-strong);
  }

  .qty-btn:last-child {
    border-radius: 0 50% 50% 0;
    border-left: 1px solid var(--border-strong);
  }

  .qty-value {
    min-width: 48px;
    text-align: center;
    font-family: var(--font-mono);
    font-weight: 600;
    font-size: 15px;
    padding: 0 8px;
    border-left: 1px solid var(--border-strong);
    border-right: 1px solid var(--border-strong);
    background: var(--surface-base);
  }

  .cart-item-remove {
    background: none;
    border: none;
    color: var(--ink-faint);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    transition: color 0.15s ease, background 0.15s ease;
  }

  .cart-item-remove:hover {
    color: var(--accent-rust);
    background: var(--accent-rust-soft);
  }

  .cart-item-total {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
    font-family: var(--font-mono);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-primary);
  }

  .cart-footer {
    padding: 20px;
    border-top: 1px solid var(--border-subtle);
    background: rgba(23,19,14,0.02);
  }

  .cart-totals {
    margin-bottom: 16px;
  }

  .cart-total-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
  }

  .cart-total-row.subtotal {
    color: var(--ink-secondary);
  }

  .cart-total-row.total {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    color: var(--ink-primary);
    border-top: 1px dashed var(--border-strong);
    margin-top: 8px;
    padding-top: 16px;
  }

  .cart-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .btn-checkout {
    width: 100%;
    padding: 16px;
    font-size: 15px;
    font-weight: 700;
  }

  .btn-clear {
    width: 100%;
    padding: 14px;
  }

  @media (max-width: 991.98px) {
    .pos-shell {
      grid-template-columns: 1fr;
    }
    .pos-cart-pane {
      position: static;
      height: auto;
      border-left: none;
      border-top: 1px solid var(--border-subtle);
    }
  }

@media (max-width: 640px) {
    .pos-shell { flex-direction: column; }
    .menu-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
    .pos-topbar { flex-direction: column; align-items: stretch; }
    .pos-topbar-left { justify-content: space-between; }
    .table-select { flex: 1; min-width: 0; }
  }

  /* Cashier station (staff only): fill the content area */
  .billing-stage {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 104px);
  }

  .billing-stage .billing-queue {
    flex: 1;
    margin: 0;
    display: flex;
    flex-direction: column;
  }

  .billing-stage .bq-body {
    flex: 1;
    overflow-y: auto;
  }

  /* Billing queue */
  .billing-queue {
    margin: 24px 0 0;
    padding: 20px;
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
  }

  .bq-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .bq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }

  .bq-card {
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    padding: 16px;
    background: var(--surface-raised);
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .bq-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .bq-order-id { font-weight: 700; color: var(--ink-primary); }
  .bq-table { color: var(--ink-secondary); font-size: 13px; }
  .bq-items { font-size: 13px; color: var(--ink-secondary); }
  .bq-total { font-family: var(--font-mono); font-size: 15px; font-weight: 600; }

  .bq-empty {
    color: var(--ink-secondary);
    padding: 24px 0;
    text-align: center;
  }

  /* Billing modal */
  .billing-modal { max-width: 520px; width: 100%; }

  .bm-meta {
    display: flex;
    gap: 20px;
    justify-content: space-between;
    font-family: var(--font-mono);
    font-size: 14px;
    margin-bottom: 16px;
    color: var(--ink-secondary);
  }

  .bm-items { margin-bottom: 12px; }

  .bm-line {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    font-family: var(--font-mono);
    font-size: 14px;
    border-bottom: 1px solid var(--border-subtle);
  }
  .bm-line:last-child { border-bottom: none; }
  .bm-line .bm-line-info { display: flex; flex-direction: column; gap: 2px; }
  .bm-line .bm-line-name { font-weight: 500; color: var(--ink-primary); }
  .bm-line .bm-line-qty { font-size: 12px; color: var(--ink-secondary); }
  .bm-line .bm-line-price { white-space: nowrap; }

  .bm-totals { font-family: var(--font-mono); }
  .bm-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 14px;
  }
  .bm-row.total {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    border-top: 1px dashed var(--border-strong);
    margin-top: 8px;
    padding-top: 12px;
  }

  .bm-payment { margin-top: 20px; }
  .bm-payment-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-primary);
    margin-bottom: 12px;
  }
  .bm-methods { display: flex; gap: 10px; }
  .bm-method {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    border: 2px solid var(--border-strong);
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: border-color .15s ease, background .15s ease;
  }
  .bm-method input { margin-bottom: 6px; accent-color: var(--accent-sulfur); }
  .bm-method:has(input:checked) {
    border-color: var(--accent-sulfur);
    background: var(--accent-sulfur-soft, rgba(181,138,18,.12));
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ===== Kiosk ordering (public) ===== -->
<?php if ($isKiosk ?? false): ?>
<div class="pos-shell">
  <!-- Menu Pane -->
  <section class="pos-menu-pane" role="region" aria-label="Menu items">
    <div class="pos-topbar">
      <div class="pos-topbar-left">
        <div>
          <div class="eyebrow"><?= ($isKiosk ?? false) ? 'Welcome' : 'Point of Sale' ?></div>
          <h1 class="page-title" style="margin: 0;"><?= ($isKiosk ?? false) ? 'Order at your table' : 'New Order' ?></h1>
        </div>
        <div class="table-select-wrapper">
          <label for="tableSelect">Table</label>
          <select class="form-select table-select" id="tableSelect" aria-label="Select table">
            <option value="">Select a table...</option>
          </select>
        </div>
      </div>
      <?php if (! ($isKiosk ?? false)): ?>
      <div class="pos-topbar-right">
        <span class="status-badge badge-available" id="connectionBadge">
          <span class="dot"></span> Online
        </span>
        <button class="btn btn-secondary" type="button" id="reloadMenuBtn" aria-label="Reload menu">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div class="category-tabs" id="categoryTabs" role="tablist" aria-label="Menu categories">
      <button class="category-tab active" data-category-id="" role="tab" aria-selected="true">All</button>
    </div>

    <div class="menu-grid" id="menuGrid" role="list">
      <!-- Menu cards rendered by JS -->
    </div>
  </section>

  <!-- Cart Pane -->
  <aside class="pos-cart-pane" role="region" aria-label="Order cart">
    <header class="cart-header">
      <h2 class="cart-title">Order</h2>
      <span class="cart-count" id="cartCount">0 items</span>
    </header>

    <div class="cart-items" id="cartItems">
      <div class="cart-empty">
        <svg class="cart-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        <p>Cart is empty</p>
        <p style="font-size: 12px; margin-top: 4px;">Add items from the menu</p>
      </div>
    </div>

    <footer class="cart-footer">
      <div class="cart-totals" id="cartTotals" style="display: none;">
        <div class="cart-total-row subtotal">
          <span>Subtotal</span>
          <span id="subtotalValue">ETB 0.00</span>
        </div>
        <div class="cart-total-row total">
          <span>Total</span>
          <span id="totalValue">ETB 0.00</span>
        </div>
      </div>

      <div class="cart-actions">
        <button class="btn btn-primary btn-checkout" id="checkoutBtn" type="button" disabled>Checkout</button>
        <button class="btn btn-secondary btn-clear" id="clearCartBtn" type="button" disabled>Clear cart</button>
      </div>
    </footer>
  </aside>
</div>
<?php endif; ?>

<!-- ===== Cashier station (staff only): billing queue, full width/height ===== -->
<?php if (! ($isKiosk ?? false)): ?>
<div class="billing-stage">
<section class="billing-queue" id="billingQueue" aria-label="Orders ready for payment">
  <header class="bq-header">
    <div>
      <div class="eyebrow">Billing</div>
      <h2 class="page-title" style="margin: 0;">Orders Ready for Payment</h2>
      <p class="page-subtitle" style="margin: 4px 0 0;">Served or completed orders awaiting checkout</p>
    </div>
    <button class="btn btn-secondary" type="button" id="bqRefreshBtn">Refresh</button>
  </header>
  <div class="bq-body" id="bqBody">
    <div class="bq-empty" id="bqEmpty">
      <p>No orders awaiting payment.</p>
    </div>
    <div class="bq-grid" id="bqGrid"></div>
  </div>
</section>

<!-- Billing modal -->
<div class="modal-overlay" id="billingModal" role="dialog" aria-modal="true" aria-labelledby="billingModalTitle" hidden style="z-index: 12000; display: none;">
  <div class="modal billing-modal">
    <div class="modal-header">
      <h2 class="modal-title" id="billingModalTitle">Order Payment</h2>
      <button class="modal-close" id="billingModalClose" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="bm-meta">
        <span>Order <strong id="bmOrderId">#--</strong></span>
        <span>Table <strong id="bmTableNumber">--</strong></span>
      </div>
      <div class="bm-items" id="bmItems">
        <!-- Lines rendered by JS -->
      </div>
      <hr class="receipt-divider">
      <div class="bm-totals">
        <div class="bm-row">
          <span>Subtotal</span>
          <span id="bmSubtotal">ETB 0.00</span>
        </div>
        <div class="bm-row">
          <span>Tax (15%)</span>
          <span id="bmTax">ETB 0.00</span>
        </div>
        <div class="bm-row total">
          <span>Grand Total</span>
          <span id="bmGrandTotal">ETB 0.00</span>
        </div>
      </div>
      <div class="bm-payment">
        <h3 class="bm-payment-title">Payment Method</h3>
        <div class="bm-methods">
          <label class="bm-method">
            <input type="radio" name="bmMethod" value="cash" checked>
            <span>Cash</span>
          </label>
          <label class="bm-method">
            <input type="radio" name="bmMethod" value="card">
            <span>Card</span>
          </label>
          <label class="bm-method">
            <input type="radio" name="bmMethod" value="mobile">
            <span>Mobile</span>
          </label>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="bmCancelBtn">Cancel</button>
      <button class="btn btn-primary" type="button" id="bmConfirmBtn">Confirm Payment</button>
    </div>
  </div>
</div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script>
  (function () {
    // State
    const isKiosk = <?= ($isKiosk ?? false) ? 'true' : 'false' ?>;
    let allMenuItems = [];
    let allCategories = [];
    let allTables = [];
    let currentCategoryId = '';
    let currentOrderId = null;
    let cart = []; // { menu_item_id, name, price, quantity, category_name }

    // DOM Elements
    const tableSelect = document.getElementById('tableSelect');
    const categoryTabs = document.getElementById('categoryTabs');
    const menuGrid = document.getElementById('menuGrid');
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotals = document.getElementById('cartTotals');
    const subtotalValue = document.getElementById('subtotalValue');
    const totalValue = document.getElementById('totalValue');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const reloadMenuBtn = document.getElementById('reloadMenuBtn');
    const connectionBadge = document.getElementById('connectionBadge');

    // Helpers
    function formatMoney(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ETB',
        maximumFractionDigits: 2
      }).format(Number(value || 0));
    }

    function getImageUrl(image) {
      if (!image) return '';
      return image.startsWith('http') ? image : '/' + image.replace(/^\//, '');
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function setConnectionStatus(online) {
      if (!connectionBadge) return;
      connectionBadge.className = 'status-badge ' + (online ? 'badge-available' : 'badge-occupied');
      connectionBadge.querySelector('.status-text')?.remove();
      connectionBadge.innerHTML = '<span class="dot"></span> ' + (online ? 'Online' : 'Offline');
    }

    // ===== Kiosk POS (menu grid + cart) — staff cashier view hides this =====
    if (isKiosk) {
    // Load data
    async function loadInitialData() {
      try {
        setConnectionStatus(false);

        // Load categories
        const catData = await api.get('/menu-categories', { per_page: 100, status: 'active' });
        allCategories = catData.data?.categories || catData.data || [];
        renderCategoryTabs();

        // Load tables (available only for new orders)
        const tableData = await api.get('/tables');
        allTables = (tableData.data || []).filter(t => t.status === 'available');
        populateTableSelect();

        // Load menu items (active only)
        await loadMenuItems();

        setConnectionStatus(true);
      } catch (error) {
        console.error('Failed to load initial data:', error);
        setConnectionStatus(false);
        toast.error('Error', 'Failed to load menu data: ' + error.message);
      }
    }

    async function loadMenuItems() {
      try {
        menuGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--ink-secondary);">Loading menu...</div>';
        const data = await api.get('/menu-items', { status: 'active', per_page: 200 });
        allMenuItems = data.data?.items || data.data || [];
        renderMenuGrid();
      } catch (error) {
        console.error('Failed to load menu items:', error);
        menuGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--accent-rust);">Failed to load menu items</div>';
        toast.error('Error', 'Failed to load menu items: ' + error.message);
      }
    }

    // Render category tabs
    function renderCategoryTabs() {
      const tabsHtml = allCategories.map(cat => `
        <button class="category-tab" data-category-id="${cat.id}" role="tab" aria-selected="false">
          ${escapeHtml(cat.name)}
        </button>
      `).join('');

      categoryTabs.innerHTML = `
        <button class="category-tab active" data-category-id="" role="tab" aria-selected="true">All</button>
        ${tabsHtml}
      `;

      // Bind click events
      categoryTabs.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', () => {
          const catId = tab.dataset.categoryId;
          categoryTabs.querySelectorAll('.category-tab').forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
          });
          tab.classList.add('active');
          tab.setAttribute('aria-selected', 'true');
          currentCategoryId = catId;
          renderMenuGrid();
        });
      });
    }

    // Populate table select
    function populateTableSelect() {
      tableSelect.innerHTML = '<option value="">Select a table...</option>' +
        allTables.map(t => `<option value="${t.id}">Table ${escapeHtml(t.table_number)} (${t.capacity} seats)</option>`).join('');
    }

    // Render menu grid
    function renderMenuGrid() {
      let filteredItems = allMenuItems;
      if (currentCategoryId) {
        filteredItems = allMenuItems.filter(item => String(item.category_id) === currentCategoryId);
      }

      if (!filteredItems.length) {
        menuGrid.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--ink-secondary);">
            <p>No items in this category</p>
          </div>
        `;
        return;
      }

      menuGrid.innerHTML = filteredItems.map(item => {
        const imageUrl = getImageUrl(item.image);
        const category = allCategories.find(c => c.id === item.category_id);
        const categoryName = category ? category.name : 'Uncategorized';

        return `
          <article class="menu-card-pos" data-item-id="${item.id}" role="listitem">
            <div class="menu-card-image ${imageUrl ? 'has-photo' : ''}" ${imageUrl ? `style="background-image: url('${escapeHtml(imageUrl)}');"` : ''}></div>
            <div class="menu-card-body">
              <span class="menu-card-category">${escapeHtml(categoryName)}</span>
              <h3 class="menu-card-name">${escapeHtml(item.name)}</h3>
              <div class="menu-card-price">${formatMoney(item.price)}</div>
              <button class="btn btn-primary menu-card-add" type="button" data-add-item="${item.id}">Add to Order</button>
            </div>
          </article>
        `;
      }).join('');

      // Bind add buttons
      menuGrid.querySelectorAll('[data-add-item]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const itemId = Number(btn.dataset.addItem);
          addToCart(itemId);
        });
      });
    }

    // Cart Management
    function addToCart(itemId) {
      // API returns item.id as string; normalize to number for comparison
      const item = allMenuItems.find(i => Number(i.id) === itemId);
      if (!item) return;

      // Check if table selected
      if (!tableSelect.value) {
        toast.warning('Select Table', 'Please select a table before adding items');
        tableSelect.focus();
        return;
      }

      // Create order if first item
      if (!currentOrderId) {
        createOrderForTable(Number(tableSelect.value));
        // The order creation will handle adding the item via callback
        // We'll add it after order is created
        pendingItemToAdd = itemId;
        return;
      }

      // Add to existing order
      addItemToExistingOrder(itemId);
    }

    let pendingItemToAdd = null;

    async function createOrderForTable(tableId) {
      try {
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Creating order...';

        const data = await api.post('/orders', { table_id: tableId });
        const order = data.data || data;
        currentOrderId = order.id;

        // Update UI
        tableSelect.disabled = true;
        tableSelect.querySelector(`option[value="${tableId}"]`).selected = true;
        clearCartBtn.disabled = false;

        // Add pending item if any
        if (pendingItemToAdd) {
          await addItemToExistingOrder(pendingItemToAdd);
          pendingItemToAdd = null;
        }

        toast.success('Order Created', `Order #${currentOrderId} started for table ${tableSelect.selectedOptions[0]?.text || tableId}`);
      } catch (error) {
        console.error('Failed to create order:', error);
        toast.error('Error', 'Failed to create order: ' + error.message);
      } finally {
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Checkout';
      }
    }

    async function addItemToExistingOrder(itemId) {
      const item = allMenuItems.find(i => Number(i.id) === itemId);
      if (!item) return;

      try {
        const data = await api.post(`/orders/${currentOrderId}/items`, {
          menu_item_id: itemId,
          quantity: 1
        });

        const order = data.data || data;
        // Update cart from server response
        syncCartFromOrder(order);
        toast.success('Added', `${escapeHtml(item.name)} added to order`);
      } catch (error) {
        console.error('Failed to add item:', error);
        // Handle specific errors
        if (error.status === 409) {
          toast.error('Cannot Add', error.message);
        } else if (error.status === 422) {
          toast.error('Invalid', error.message);
        } else {
          toast.error('Error', 'Failed to add item: ' + error.message);
        }
      }
    }

    function syncCartFromOrder(order) {
      // Rebuild cart from order items
      cart = (order.items || []).map(oi => ({
        id: oi.id,
        menu_item_id: oi.menu_item_id,
        name: oi.menu_item_name || oi.name,
        price: Number(oi.unit_price || oi.price),
        quantity: Number(oi.quantity)
      }));
      renderCart();
    }

    async function updateQuantity(itemId, delta) {
      const line = cart.find(c => Number(c.id) === itemId);
      if (!line) return;

      const newQty = line.quantity + delta;
      if (newQty <= 0) {
        await removeFromCart(itemId);
        return;
      }

      try {
        const data = await api.put(`/orders/${currentOrderId}/items/${itemId}`, {
          quantity: newQty
        });
        const order = data.data || data;
        syncCartFromOrder(order);
      } catch (error) {
        console.error('Failed to update quantity:', error);
        toast.error('Error', 'Failed to update quantity: ' + error.message);
      }
    }

    async function removeFromCart(itemId) {
      const line = cart.find(c => Number(c.id) === itemId);
      if (!line) return;

      try {
        await api.delete(`/orders/${currentOrderId}/items/${itemId}`);
        // Reload order to get updated state
        const data = await api.get(`/orders/${currentOrderId}`);
        const order = data.data || data;
        syncCartFromOrder(order);
        toast.success('Removed', `${escapeHtml(line.name)} removed from order`);
      } catch (error) {
        console.error('Failed to remove item:', error);
        toast.error('Error', 'Failed to remove item: ' + error.message);
      }
    }

    function renderCart() {
      const totalItems = cart.reduce((sum, line) => sum + line.quantity, 0);
      const subtotal = cart.reduce((sum, line) => sum + line.price * line.quantity, 0);

      cartCount.textContent = totalItems + (totalItems === 1 ? ' item' : ' items');

      if (!cart.length) {
        cartItems.innerHTML = `
          <div class="cart-empty">
            <svg class="cart-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <p>Cart is empty</p>
            <p style="font-size: 12px; margin-top: 4px;">Add items from the menu</p>
          </div>
        `;
        cartTotals.style.display = 'none';
        checkoutBtn.disabled = true;
        clearCartBtn.disabled = true;
        return;
      }

      cartTotals.style.display = 'block';
      subtotalValue.textContent = formatMoney(subtotal);
      totalValue.textContent = formatMoney(subtotal);
      checkoutBtn.disabled = false;
      clearCartBtn.disabled = false;

      cartItems.innerHTML = cart.map(line => {
        const lineTotal = line.price * line.quantity;
        return `
          <div class="cart-item" data-line-id="${line.id}">
            <div class="cart-item-header">
              <span class="cart-item-name">${escapeHtml(line.name)}</span>
              <span class="cart-item-price">${formatMoney(line.price)}</span>
            </div>
            <div class="cart-item-controls">
              <div class="qty-stepper">
                <button class="qty-btn" type="button" data-decrease="${line.id}" ${line.quantity <= 1 ? 'disabled' : ''} aria-label="Decrease quantity">−</button>
                <span class="qty-value">${line.quantity}</span>
                <button class="qty-btn" type="button" data-increase="${line.id}" aria-label="Increase quantity">+</button>
              </div>
              <button class="cart-item-remove" type="button" data-remove="${line.id}" aria-label="Remove ${escapeHtml(line.name)}">Remove</button>
            </div>
            <div class="cart-item-total">${formatMoney(lineTotal)}</div>
          </div>
        `;
      }).join('');

      // Bind quantity and remove buttons
      cartItems.querySelectorAll('[data-increase]').forEach(btn => {
        btn.addEventListener('click', () => updateQuantity(Number(btn.dataset.increase), 1));
      });
      cartItems.querySelectorAll('[data-decrease]').forEach(btn => {
        btn.addEventListener('click', () => updateQuantity(Number(btn.dataset.decrease), -1));
      });
      cartItems.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', () => removeFromCart(Number(btn.dataset.remove)));
      });
    }

    async function clearCart() {
      if (!currentOrderId) return;

      const confirmed = await confirmModal.open({
        title: 'Clear Cart',
        message: 'Remove all items from this order? The order will be cancelled.',
        confirmLabel: 'Clear cart',
        cancelLabel: 'Keep items',
        variant: 'destructive'
      });

      if (!confirmed) return;

      try {
        await api.post(`/orders/${currentOrderId}/cancel`, { reason: 'Cleared by cashier' });
        resetOrder();
        toast.success('Cleared', 'Cart cleared and order cancelled');
      } catch (error) {
        console.error('Failed to clear cart:', error);
        toast.error('Error', 'Failed to clear cart: ' + error.message);
      }
    }

    async function checkout() {
      if (!tableSelect.value) {
        tableSelect.classList.add('is-invalid');
        toast.warning('Select Table', 'Please select a table before checking out');
        tableSelect.focus();
        return;
      }
      if (!currentOrderId || !cart.length) return;

      try {
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Processing...';

        // Complete the order
        const data = await api.post(`/orders/${currentOrderId}/complete`);
        const order = data.data || data;

        toast.success('Order Complete', `Order #${currentOrderId} sent to kitchen. Total: ${formatMoney(order.total_amount)}`);
        resetOrder();
      } catch (error) {
        console.error('Failed to complete order:', error);
        if (error.status === 409) {
          toast.error('Cannot Complete', error.message);
        } else {
          toast.error('Error', 'Failed to complete order: ' + error.message);
        }
      } finally {
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Checkout';
      }
    }

    function resetOrder() {
      currentOrderId = null;
      cart = [];
      pendingItemToAdd = null;
      tableSelect.disabled = false;
      tableSelect.value = '';
      renderCart();
    }

    // Event Listeners
    tableSelect.addEventListener('change', () => {
      // If table changed and no order yet, that's fine
      // If order exists, table is locked anyway
      tableSelect.classList.remove('is-invalid');
    });

    clearCartBtn.addEventListener('click', clearCart);
    checkoutBtn.addEventListener('click', checkout);
    if (reloadMenuBtn) reloadMenuBtn.addEventListener('click', loadMenuItems);

    loadInitialData();
    } // end kiosk POS block

    // ===== Cashier billing queue + payment modal (staff only) =====
    if (!isKiosk) {
      // ===== Billing queue + payment modal (staff only) =====
      const billingQueue = document.getElementById('billingQueue');
    const bqBody = document.getElementById('bqBody');
    const bqEmpty = document.getElementById('bqEmpty');
    const bqGrid = document.getElementById('bqGrid');
    const bqRefreshBtn = document.getElementById('bqRefreshBtn');

    const billingModal = document.getElementById('billingModal');
    const bmCloseBtn = document.getElementById('billingModalClose');
    const bmCancelBtn = document.getElementById('bmCancelBtn');
    const bmConfirmBtn = document.getElementById('bmConfirmBtn');
    const bmOrderId = document.getElementById('bmOrderId');
    const bmTableNumber = document.getElementById('bmTableNumber');
    const bmItems = document.getElementById('bmItems');
    const bmSubtotal = document.getElementById('bmSubtotal');
    const bmTax = document.getElementById('bmTax');
    const bmGrandTotal = document.getElementById('bmGrandTotal');

    let billingOrderId = null;

    function openBillingModal() {
      billingModal.hidden = false;
      billingModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeBillingModal() {
      billingModal.hidden = true;
      billingModal.style.display = 'none';
      document.body.style.overflow = '';
      billingOrderId = null;
    }

    async function loadBillingQueue() {
      try {
        const data = await api.get('/orders', { status: 'served,completed', per_page: 100 });
        const orders = data.data?.orders || data.data || [];

        bqGrid.innerHTML = '';
        if (!orders.length) {
          bqEmpty.hidden = false;
          bqGrid.hidden = true;
          return;
        }

        bqEmpty.hidden = true;
        bqGrid.hidden = false;

        orders.forEach(order => {
          const itemCount = (order.items || []).reduce((s, it) => s + Number(it.quantity || 0), 0);
          const card = document.createElement('div');
          card.className = 'bq-card';
          card.innerHTML = `
            <div class="bq-card-top">
              <span class="bq-order-id">Order #${escapeHtml(order.id)}</span>
              <span class="bq-table">Table ${escapeHtml(order.table_number || order.table_id)}</span>
            </div>
            <div class="bq-items">${itemCount} item${itemCount === 1 ? '' : 's'} · ${escapeHtml(order.status)}</div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <span class="bq-total">${formatMoney(order.total_amount)}</span>
              <button class="btn btn-primary" type="button" data-pay-order="${escapeHtml(order.id)}">Pay</button>
            </div>
          `;
          bqGrid.appendChild(card);
        });

        bqGrid.querySelectorAll('[data-pay-order]').forEach(btn => {
          btn.addEventListener('click', () => openBillForOrder(btn.dataset.payOrder));
        });
      } catch (error) {
        console.error('Failed to load billing queue:', error);
        toast.error('Error', 'Failed to load billing queue: ' + error.message);
      }
    }

    async function openBillForOrder(orderId) {
      try {
        billingOrderId = orderId;
        const data = await api.get(`/orders/${orderId}/bill`);
        const summary = data.data || data;

        bmOrderId.textContent = '#' + summary.order_id;
        bmTableNumber.textContent = summary.table_id || '--';

        bmItems.innerHTML = (summary.items || []).map(item => {
          const qty = Number(item.quantity);
          const price = Number(item.unit_price);
          return `
            <div class="bm-line">
              <div class="bm-line-info">
                <span class="bm-line-name">${escapeHtml(item.menu_item_name || item.name || 'Item')}</span>
                <span class="bm-line-qty">${qty} × ${formatMoney(price)}</span>
              </div>
              <span class="bm-line-price">${formatMoney(qty * price)}</span>
            </div>
          `;
        }).join('');

        bmSubtotal.textContent = formatMoney(summary.subtotal);
        bmTax.textContent = formatMoney(summary.tax);
        bmGrandTotal.textContent = formatMoney(summary.grand_total);

        openBillingModal();
      } catch (error) {
        console.error('Failed to load bill:', error);
        toast.error('Error', 'Failed to load bill: ' + error.message);
      }
    }

    async function confirmPayment() {
      if (!billingOrderId) return;

      const methodEl = document.querySelector('input[name="bmMethod"]:checked');
      const method = methodEl ? methodEl.value : 'cash';

      bmConfirmBtn.disabled = true;
      bmConfirmBtn.textContent = 'Processing...';

      try {
        const data = await api.post(`/orders/${billingOrderId}/pay`, { payment_method: method });
        const result = data.data || data;
        const paid = result.bill || result;
        toast.success('Payment Complete', `Order #${billingOrderId} paid: ${formatMoney(paid.total_amount)} (${method})`);

        closeBillingModal();
        await loadBillingQueue();
      } catch (error) {
        console.error('Payment failed:', error);
        toast.error('Payment Failed', error.message);
      } finally {
        bmConfirmBtn.disabled = false;
        bmConfirmBtn.textContent = 'Confirm Payment';
      }
    }

    bqRefreshBtn.addEventListener('click', loadBillingQueue);
    bmCloseBtn.addEventListener('click', closeBillingModal);
    bmCancelBtn.addEventListener('click', closeBillingModal);
    bmConfirmBtn.addEventListener('click', confirmPayment);
    billingModal.addEventListener('click', (e) => {
      if (e.target === billingModal) closeBillingModal();
    });
    document.addEventListener('keydown', (e) => {
      if (!billingModal.hidden && e.key === 'Escape') closeBillingModal();
    });

    loadBillingQueue();
    } // end staff-only billing block
  })();
</script>
<?= $this->endSection() ?>