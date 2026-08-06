<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .dashboard-shell {
    padding: 20px;
  }

  .dashboard-header {
    margin-bottom: 24px;
  }

  .dashboard-header .eyebrow {
    margin-bottom: 8px;
  }

  .dashboard-header .page-title {
    margin: 0 0 4px;
  }

  .dashboard-header .page-subtitle {
    margin: 0;
  }

  /* Stat Cards Grid */
  .stat-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .stat-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    padding: 20px;
    position: relative;
    overflow: hidden;
  }

  .stat-card::after {
    content: "";
    position: absolute;
    inset: auto -28px -28px auto;
    width: 108px;
    height: 108px;
    border-radius: 50%;
    background: rgba(181,138,18,0.08);
  }

  .stat-card.highlight {
    background: var(--ink-primary);
    border-color: var(--ink-primary);
  }

  .stat-card.highlight .stat-label,
  .stat-card.highlight .stat-note {
    color: var(--ink-inverse-muted);
  }

  .stat-card.highlight .stat-value {
    color: var(--accent-sulfur);
  }

  .stat-card.highlight .chip-sulfur {
    background: var(--accent-sulfur);
    color: var(--ink-inverse);
  }

  .stat-card.highlight::after {
    background: rgba(255,255,255,0.1);
  }

  .stat-top {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 14px;
    position: relative;
    z-index: 1;
  }

  .stat-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink-secondary);
    margin-bottom: 6px;
  }

  .stat-value {
    font-family: var(--font-display);
    font-size: 36px;
    line-height: 1;
    letter-spacing: -0.05em;
    margin: 0;
  }

  .stat-note {
    margin-top: 14px;
    font-size: 13px;
    line-height: 1.6;
    color: var(--ink-secondary);
    position: relative;
    z-index: 1;
  }

  .chip {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
  }
  .chip-sulfur { background: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }
  .chip-teal { background: var(--accent-teal-soft); color: var(--accent-teal-ink); }
  .chip-rust { background: var(--accent-rust-soft); color: var(--accent-rust-ink); }

  /* Floor Status Grid */
  .floor-section {
    margin-bottom: 24px;
  }

  .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .section-title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 700;
    margin: 0;
  }

  .floor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
  }

  .table-tile {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.15s ease;
    position: relative;
  }

  .table-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 24px 60px rgba(23,19,14,.12);
  }

  .table-tile.occupied {
    border-color: var(--accent-sulfur);
  }

  .table-tile .number {
    font-family: var(--font-display);
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
    color: var(--ink-primary);
  }

  .table-tile.occupied .number {
    color: var(--accent-sulfur);
  }

  .table-tile .capacity {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--ink-secondary);
    margin-bottom: 8px;
  }

  .table-tile .status-badge {
    font-size: 10px;
    padding: 3px 8px;
  }

  /* Recent Orders Panel */
  .recent-orders-panel {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
  }

  .panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
  }

  .panel-title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    margin: 0;
  }

  .data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }
  .data-table th,
  .data-table td {
    padding: 10px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-subtle);
  }
  .data-table th {
    font-family: var(--font-mono);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--ink-secondary);
    background: rgba(23,19,14,0.02);
    white-space: nowrap;
  }
  .data-table tbody tr:hover { background: rgba(181,138,18,0.03); }
  .data-table tbody tr:last-child td { border-bottom: none; }
  .data-table .price-cell {
    font-family: var(--font-mono);
    font-weight: 600;
    text-align: right;
    white-space: nowrap;
  }
  .data-table .status-cell {
    white-space: nowrap;
  }

  .badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 999px;
    font-family: var(--font-mono);
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.02em;
    white-space: nowrap;
  }
  .badge-pending {
    background: var(--accent-sulfur-soft);
    color: var(--accent-sulfur-ink);
  }
  .badge-completed {
    background: var(--accent-teal-soft);
    color: var(--accent-teal-ink);
  }
  .badge-paid {
    background: var(--accent-teal-soft);
    color: var(--accent-teal-ink);
  }
  .badge-cancelled {
    background: var(--accent-rust-soft);
    color: var(--accent-rust-ink);
  }

  .empty-state {
    padding: 32px 20px;
    text-align: center;
    color: var(--ink-secondary);
  }

  .loading-row {
    padding: 32px 20px;
    text-align: center;
    color: var(--ink-secondary);
  }

  @media (max-width: 640px) {
    .dashboard-header { margin-bottom: 16px; }
    .stat-cards-grid { grid-template-columns: 1fr; }
    .floor-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
    .table-tile .number { font-size: 24px; }
    .table-tile { padding: 12px 8px; }
    .section-header { flex-direction: column; align-items: stretch; }
    .data-table { font-size: 12px; }
    .data-table th, .data-table td { padding: 8px 10px; }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-shell">
  <header class="dashboard-header">
    <div class="eyebrow">Dashboard</div>
    <h1 class="page-title">Good to see you back.</h1>
    <p class="page-subtitle">Live operational summary for menu, tables, orders, and sales.</p>
  </header>

  <!-- Stat Cards -->
  <section class="stat-cards-grid" aria-label="Key metrics">
    <article class="stat-card">
      <div class="stat-top">
        <div>
          <div class="stat-label">Total Menu Items</div>
          <div class="stat-value" id="statTotalMenuItems">--</div>
        </div>
        <div class="chip chip-sulfur">M</div>
      </div>
      <div class="stat-note">Active items currently available for ordering.</div>
    </article>

    <article class="stat-card">
      <div class="stat-top">
        <div>
          <div class="stat-label">Total Tables</div>
          <div class="stat-value" id="statTotalTables">--</div>
        </div>
        <div class="chip chip-teal">T</div>
      </div>
      <div class="stat-note">Restaurant tables tracked in the system.</div>
    </article>

    <article class="stat-card highlight">
      <div class="stat-top">
        <div>
          <div class="stat-label">Today's Sales</div>
          <div class="stat-value" id="statTodaySales">ETB 0.00</div>
        </div>
        <div class="chip chip-sulfur">$</div>
      </div>
      <div class="stat-note">Paid bill total from the current day.</div>
    </article>

    <article class="stat-card">
      <div class="stat-top">
        <div>
          <div class="stat-label">Today's Orders</div>
          <div class="stat-value" id="statTodayOrders">--</div>
        </div>
        <div class="chip chip-rust">O</div>
      </div>
      <div class="stat-note">Orders created since midnight.</div>
    </article>
  </section>

  <!-- Floor Status Grid -->
  <section class="floor-section" aria-label="Floor status">
    <header class="section-header">
      <h2 class="section-title">Floor Status</h2>
    </header>
    <div class="floor-grid" id="floorGrid" role="list">
      <div class="loading-row" style="grid-column: 1 / -1;">Loading tables...</div>
    </div>
  </section>

  <!-- Recent Orders -->
  <section class="recent-orders-panel" aria-label="Recent orders">
    <header class="panel-header">
      <h2 class="panel-title">Recent Orders</h2>
    </header>
    <div class="datatable-table-wrapper" style="overflow-x: auto;">
      <table class="data-table" id="recentOrdersTable">
        <thead>
          <tr>
            <th style="width: 80px;">Order #</th>
            <th>Table</th>
            <th class="price-cell" style="width: 120px;">Total</th>
            <th class="status-cell" style="width: 110px;">Status</th>
            <th style="width: 140px;">Time</th>
          </tr>
        </thead>
        <tbody id="recentOrdersBody">
          <tr class="loading-row">
            <td colspan="5">Loading recent orders...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script>
  (function () {
    // DOM Elements
    const statTotalMenuItems = document.getElementById('statTotalMenuItems');
    const statTotalTables = document.getElementById('statTotalTables');
    const statTodaySales = document.getElementById('statTodaySales');
    const statTodayOrders = document.getElementById('statTodayOrders');
    const floorGrid = document.getElementById('floorGrid');
    const recentOrdersBody = document.getElementById('recentOrdersBody');

    function formatMoney(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ETB',
        maximumFractionDigits: 2
      }).format(Number(value || 0));
    }

    function formatDateTime(isoString) {
      if (!isoString) return '--';
      const date = new Date(isoString);
      return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    function getStatusBadgeClass(status) {
      const s = String(status || '').toLowerCase();
      if (s === 'pending') return 'badge-pending';
      if (s === 'completed') return 'badge-completed';
      if (s === 'paid') return 'badge-paid';
      if (s === 'cancelled') return 'badge-cancelled';
      return 'badge-pending';
    }

    function getStatusLabel(status) {
      const s = String(status || '').toLowerCase();
      return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // Render floor grid
    function renderFloorGrid(tables) {
      if (!tables.length) {
        floorGrid.innerHTML = '<div class="empty-state" style="grid-column: 1 / -1;">No tables configured</div>';
        return;
      }

      floorGrid.innerHTML = tables.map(table => {
        const isOccupied = table.status === 'occupied';
        return `
          <article class="table-tile ${isOccupied ? 'occupied' : ''}" role="listitem">
            <span class="number">${escapeHtml(table.table_number)}</span>
            <span class="capacity">${table.capacity} seats</span>
            <span class="status-badge ${isOccupied ? 'badge-occupied' : 'badge-available'}">
              <span class="dot"></span>
              ${isOccupied ? 'Occupied' : 'Available'}
            </span>
          </article>
        `;
      }).join('');
    }

    // Render recent orders
    function renderRecentOrders(orders) {
      if (!orders.length) {
        recentOrdersBody.innerHTML = '<tr class="empty-state"><td colspan="5">No recent orders</td></tr>';
        return;
      }

      recentOrdersBody.innerHTML = orders.map(order => {
        const statusClass = getStatusBadgeClass(order.status);
        return `
          <tr>
            <td><code style="font-family: var(--font-mono); font-size: 12px;">#${order.id}</code></td>
            <td>${escapeHtml(order.table_id)}</td>
            <td class="price-cell" style="font-family: var(--font-mono);">${formatMoney(order.total_amount)}</td>
            <td class="status-cell"><span class="badge ${statusClass}">${getStatusLabel(order.status)}</span></td>
            <td>${formatDateTime(order.created_at)}</td>
          </tr>
        `;
      }).join('');
    }

    // Load all dashboard data
    async function loadDashboard() {
      try {
        // Fetch summary stats
        const summaryResponse = await api.get('/dashboard/summary');
        const summary = summaryResponse.data || summaryResponse;

        statTotalMenuItems.textContent = summary.total_menu_items ?? 0;
        statTotalTables.textContent = summary.total_tables ?? 0;
        statTodaySales.textContent = formatMoney(summary.today_sales ?? 0);
        statTodayOrders.textContent = summary.today_orders ?? 0;

        // Fetch tables for floor grid
        const tablesResponse = await api.get('/tables');
        const tables = tablesResponse.data || [];
        renderFloorGrid(tables);

        // Fetch recent orders (limit 5, most recent first)
        const ordersResponse = await api.get('/orders', { per_page: 5, page: 1 });
        const ordersData = ordersResponse.data || ordersResponse;
        const recentOrders = Array.isArray(ordersData) ? ordersData : (ordersData.orders || []);
        renderRecentOrders(recentOrders);

      } catch (error) {
        console.error('Failed to load dashboard:', error);
        // Keep loading states on error
      }
    }

    // Initialize
    loadDashboard();

    // Optional: auto-refresh every 30 seconds
    setInterval(loadDashboard, 30000);
  })();
</script>
<?= $this->endSection() ?>