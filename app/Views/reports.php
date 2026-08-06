<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .reports-shell {
    padding: 20px;
  }

  .reports-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .reports-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .reports-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .report-type-toggle {
    display: flex;
    background: var(--surface-card);
    border: 1px solid var(--border-strong);
    border-radius: var(--radius-sm);
    padding: 4px;
    gap: 4px;
  }

  .report-type-btn {
    padding: 10px 16px;
    border: none;
    background: transparent;
    color: var(--ink-secondary);
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 500;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .report-type-btn:hover {
    color: var(--ink-primary);
    background: var(--surface-base);
  }

  .report-type-btn.active {
    background: var(--accent-sulfur);
    color: var(--ink-inverse);
  }

  .date-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .date-picker-wrapper label {
    font-family: var(--font-mono);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink-secondary);
    white-space: nowrap;
  }

  .date-picker-wrapper input[type="date"],
  .date-picker-wrapper input[type="month"] {
    padding: 10px 12px;
    border: 1px solid var(--border-strong);
    border-radius: var(--radius-sm);
    background: var(--surface-card);
    color: var(--ink-primary);
    font-family: var(--font-mono);
    font-size: 13px;
    min-width: 180px;
  }

  .stat-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    font-size: 32px;
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

  .top-items-panel {
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
    padding: 20px;
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
    font-size: 14px;
  }
  .data-table th,
  .data-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-subtle);
  }
  .data-table th {
    font-family: var(--font-mono);
    font-size: 11px;
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
  .data-table .qty-cell {
    font-family: var(--font-mono);
    text-align: right;
    white-space: nowrap;
  }

  .empty-table {
    padding: 40px 20px;
    text-align: center;
    color: var(--ink-secondary);
  }

  .loading-row {
    padding: 40px 20px;
    text-align: center;
    color: var(--ink-secondary);
  }

@media (max-width: 640px) {
    .reports-header { flex-direction: column; align-items: stretch; }
    .reports-header-left { justify-content: space-between; }
    .reports-header-right { justify-content: stretch; }
    .reports-header-right .btn { flex: 1; }
    .date-picker-wrapper { flex-direction: column; align-items: stretch; }
    .date-picker-wrapper input { width: 100%; }
    .stat-cards-grid { grid-template-columns: 1fr; }
    .panel-header { flex-direction: column; align-items: stretch; gap: 12px; }
    .data-table { font-size: 13px; }
    .data-table th, .data-table td { padding: 10px 12px; }
  }

  @media print {
    /* Hide all navigation and UI chrome */
    .reports-header,
    .report-type-toggle,
    .date-picker-wrapper,
    .billing-actions,
    .sidebar,
    .topbar,
    .sidebar-overlay,
    .app-shell > aside,
    .app-shell > main > header,
    #printReportBtn {
      display: none !important;
    }

    /* Reset layout for print */
    .main-content { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    .reports-shell { padding: 0 !important; max-width: none !important; }

    /* Stat cards - compact, no shadows/borders */
    .stat-cards-grid {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 12px !important;
      margin-bottom: 16px !important;
    }
    .stat-card {
      box-shadow: none !important;
      border: 1px solid #ccc !important;
      border-radius: 4px !important;
      padding: 12px !important;
      background: white !important;
      break-inside: avoid;
    }
    .stat-card::after { display: none !important; }
    .stat-card.highlight {
      background: #f5f5f5 !important;
      border-color: #ccc !important;
    }
    .stat-card.highlight .stat-value { color: #333 !important; }
    .stat-card.highlight .stat-label,
    .stat-card.highlight .stat-note { color: #666 !important; }
    .stat-top { gap: 8px !important; }
    .stat-label { font-size: 10px !important; margin-bottom: 2px !important; }
    .stat-value { font-size: 18px !important; }
    .stat-note { font-size: 9px !important; margin-top: 6px !important; }
    .chip { width: 28px !important; height: 28px !important; font-size: 12px !important; border-radius: 8px !important; }

    /* Top items panel - clean table */
    .top-items-panel {
      box-shadow: none !important;
      border: 1px solid #ccc !important;
      border-radius: 4px !important;
      overflow: visible !important;
      page-break-inside: avoid;
    }
    .panel-header {
      border-bottom: 2px solid #333 !important;
      padding: 8px 12px !important;
      margin-bottom: 0 !important;
    }
    .panel-title { font-size: 13px !important; }
    .data-table { font-size: 11px !important; width: 100% !important; }
    .data-table th,
    .data-table td {
      padding: 6px 8px !important;
      border-bottom: 1px dotted #999 !important;
    }
    .data-table th {
      background: #f0f0f0 !important;
      border-bottom: 2px solid #333 !important;
      font-size: 9px !important;
    }
    .data-table tbody tr:hover { background: transparent !important; }
    .data-table .price-cell,
    .data-table .qty-cell { font-size: 10px !important; }

    /* Force black text on white for printing */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    body { background: white !important; color: black !important; }

    /* Page setup */
    @page {
      margin: 12mm !important;
      size: auto !important;
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="reports-shell">
  <header class="reports-header">
    <div class="reports-header-left">
      <div>
        <div class="eyebrow">Reports</div>
        <h1 class="page-title" style="margin: 0;">Sales Reports</h1>
        <p class="page-subtitle" style="margin: 4px 0 0;">Daily and monthly sales performance</p>
      </div>
    </div>
    <div class="reports-header-right">
      <button class="btn btn-secondary" type="button" id="printReportBtn" aria-label="Print report">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        Print Report
      </button>
    </div>
  </header>

  <!-- Report Type Toggle -->
  <div class="report-type-toggle" role="radiogroup" aria-label="Report type">
    <button class="report-type-btn active" data-type="daily" role="radio" aria-checked="true">Daily</button>
    <button class="report-type-btn" data-type="monthly" role="radio" aria-checked="false">Monthly</button>
  </div>

  <!-- Date/Month Picker -->
  <div class="date-picker-wrapper" id="datePickerWrapper">
    <label for="dailyDatePicker">Date</label>
    <input type="date" id="dailyDatePicker" class="form-input" style="max-width: 200px;">

    <label for="monthlyPicker" hidden>Month</label>
    <input type="month" id="monthlyPicker" class="form-input" style="max-width: 200px;" hidden>
  </div>

  <!-- Stat Cards -->
  <div class="stat-cards-grid" id="statCards">
    <article class="stat-card" id="cardTotalOrders">
      <div class="stat-top">
        <div>
          <div class="stat-label">Total Orders</div>
          <div class="stat-value" id="statTotalOrders">--</div>
        </div>
        <div class="chip chip-sulfur">O</div>
      </div>
      <div class="stat-note">Orders in selected period</div>
    </article>

    <article class="stat-card highlight" id="cardTotalRevenue">
      <div class="stat-top">
        <div>
          <div class="stat-label">Total Revenue</div>
          <div class="stat-value" id="statTotalRevenue">ETB 0.00</div>
        </div>
        <div class="chip chip-sulfur">$</div>
      </div>
      <div class="stat-note">Paid bills total</div>
    </article>

    <article class="stat-card" id="cardAvgOrderValue">
      <div class="stat-top">
        <div>
          <div class="stat-label">Avg Order Value</div>
          <div class="stat-value" id="statAvgOrderValue">ETB 0.00</div>
        </div>
        <div class="chip chip-teal">Ø</div>
      </div>
      <div class="stat-note">Revenue ÷ Orders</div>
    </article>
  </div>

  <!-- Top Items Table -->
  <section class="top-items-panel" role="region" aria-label="Top selling items">
    <header class="panel-header">
      <h2 class="panel-title">Top 5 Items</h2>
    </header>
    <div class="datatable-table-wrapper" style="overflow-x: auto;">
      <table class="data-table" id="topItemsTable">
        <thead>
          <tr>
            <th style="width: 40px;">Rank</th>
            <th>Item</th>
            <th>Category</th>
            <th class="qty-cell" style="width: 100px;">Qty Sold</th>
            <th class="price-cell" style="width: 140px;">Revenue</th>
          </tr>
        </thead>
        <tbody id="topItemsBody">
          <tr class="loading-row">
            <td colspan="5">Loading top items...</td>
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
    // State
    let currentReportType = 'daily'; // 'daily' or 'monthly'
    let currentDate = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
    let currentMonth = new Date().toISOString().slice(0, 7); // YYYY-MM

    // DOM Elements
    const dailyBtn = document.querySelector('[data-type="daily"]');
    const monthlyBtn = document.querySelector('[data-type="monthly"]');
    const dailyDatePicker = document.getElementById('dailyDatePicker');
    const monthlyPicker = document.getElementById('monthlyPicker');
    const datePickerWrapper = document.getElementById('datePickerWrapper');
    const printReportBtn = document.getElementById('printReportBtn');

    // Stat card elements
    const statTotalOrders = document.getElementById('statTotalOrders');
    const statTotalRevenue = document.getElementById('statTotalRevenue');
    const statAvgOrderValue = document.getElementById('statAvgOrderValue');

    // Top items table
    const topItemsBody = document.getElementById('topItemsBody');

    // Helpers
    function formatMoney(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ETB',
        maximumFractionDigits: 2
      }).format(Number(value || 0));
    }

    function formatDate(dateStr) {
      if (!dateStr) return '--';
      const date = new Date(dateStr + 'T00:00:00');
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    }

    function formatMonth(monthStr) {
      if (!monthStr) return '--';
      const date = new Date(monthStr + '-01T00:00:00');
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long'
      });
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function showLoading() {
      topItemsBody.innerHTML = '<tr class="loading-row"><td colspan="5">Loading...</td></tr>';
    }

    function showError(message) {
      topItemsBody.innerHTML = `<tr class="empty-table"><td colspan="5" style="color: var(--accent-rust);">Failed to load: ${escapeHtml(message)}</td></tr>`;
    }

    // Update UI for report type
    function updateReportTypeUI() {
      if (currentReportType === 'daily') {
        dailyBtn.classList.add('active');
        dailyBtn.setAttribute('aria-checked', 'true');
        monthlyBtn.classList.remove('active');
        monthlyBtn.setAttribute('aria-checked', 'false');
        dailyDatePicker.hidden = false;
        dailyDatePicker.previousElementSibling.hidden = false;
        monthlyPicker.hidden = true;
        monthlyPicker.previousElementSibling.hidden = true;
      } else {
        monthlyBtn.classList.add('active');
        monthlyBtn.setAttribute('aria-checked', 'true');
        dailyBtn.classList.remove('active');
        dailyBtn.setAttribute('aria-checked', 'false');
        dailyDatePicker.hidden = true;
        dailyDatePicker.previousElementSibling.hidden = true;
        monthlyPicker.hidden = false;
        monthlyPicker.previousElementSibling.hidden = false;
      }
    }

    // Set default date/month values
    function setDefaultDateValues() {
      dailyDatePicker.value = currentDate;
      dailyDatePicker.max = currentDate; // Can't pick future dates
      monthlyPicker.value = currentMonth;
      monthlyPicker.max = currentMonth;
    }

    // Load report data
    async function loadReport() {
      showLoading();

      try {
        let endpoint, params;

        if (currentReportType === 'daily') {
          endpoint = '/reports/daily';
          params = { date: dailyDatePicker.value };
        } else {
          endpoint = '/reports/monthly';
          params = { month: monthlyPicker.value };
        }

        const response = await api.get(endpoint, params);
        const data = response.data || response;

        // Update stat cards
        statTotalOrders.textContent = data.total_orders ?? 0;
        statTotalRevenue.textContent = formatMoney(data.total_revenue ?? 0);
        statAvgOrderValue.textContent = formatMoney(data.avg_order_value ?? 0);

        // Update top items table
        const items = data.top_items || [];
        if (!items.length) {
          topItemsBody.innerHTML = '<tr class="empty-table"><td colspan="5">No items sold in this period</td></tr>';
        } else {
          topItemsBody.innerHTML = items.slice(0, 5).map((item, index) => `
            <tr>
              <td style="font-family: var(--font-mono); font-weight: 600; color: var(--ink-secondary);">${index + 1}</td>
              <td>${escapeHtml(item.name)}</td>
              <td>${escapeHtml(item.category_name || item.category || '-')}</td>
              <td class="qty-cell" style="font-family: var(--font-mono);">${item.total_quantity}</td>
              <td class="price-cell" style="font-family: var(--font-mono);">${formatMoney(item.total_revenue)}</td>
            </tr>
          `).join('');
        }

      } catch (error) {
        console.error('Failed to load report:', error);
        showError(error.message);
        toast.error('Error', 'Failed to load report: ' + error.message);
      }
    }

    // Event listeners
    dailyBtn.addEventListener('click', () => {
      if (currentReportType !== 'daily') {
        currentReportType = 'daily';
        updateReportTypeUI();
        loadReport();
      }
    });

    monthlyBtn.addEventListener('click', () => {
      if (currentReportType !== 'monthly') {
        currentReportType = 'monthly';
        updateReportTypeUI();
        loadReport();
      }
    });

    dailyDatePicker.addEventListener('change', () => {
      currentDate = dailyDatePicker.value;
      loadReport();
    });

    monthlyPicker.addEventListener('change', () => {
      currentMonth = monthlyPicker.value;
      loadReport();
    });

    printReportBtn.addEventListener('click', () => {
      window.print();
    });

    // Initialize
    setDefaultDateValues();
    updateReportTypeUI();
    loadReport();
  })();
</script>
<?= $this->endSection() ?>