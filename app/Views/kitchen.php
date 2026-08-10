<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .kitchen-shell {
    padding: 20px;
    min-height: calc(100vh - 72px);
  }

  .kitchen-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .kitchen-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .kitchen-title svg {
    color: var(--accent-sulfur);
  }

  .kitchen-stats {
    display: flex;
    gap: 8px;
  }

  .stat-badge {
    padding: 6px 12px;
    border-radius: 999px;
    font-family: var(--font-mono);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink-inverse);
  }

  .stat-preparing { background: var(--accent-sulfur); }
  .stat-ready { background: var(--accent-teal); }
  .stat-served { background: var(--ink-secondary); }
  .stat-all { background: var(--ink-primary); }

  .orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 16px;
  }

  .order-ticket {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
  }

  .order-ticket:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px rgba(23,19,14,.12);
  }

  .order-ticket-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
    background: var(--surface-elevated);
  }

  .order-ticket-id {
    font-family: var(--font-mono);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-primary);
  }

  .order-ticket-table {
    font-size: 13px;
    color: var(--ink-secondary);
  }

  .order-ticket-status {
    padding: 4px 12px;
    border-radius: 999px;
    font-family: var(--font-mono);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .status-pending { background: var(--ink-faint); color: var(--ink-secondary); }
  .status-preparing { background: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }
  .status-ready { background: var(--accent-teal-soft); color: var(--accent-teal-ink); }
  .status-served { background: var(--ink-secondary-soft); color: var(--ink-secondary); }
  .status-completed { background: var(--accent-teal); color: var(--ink-inverse); }

  .order-ticket-items {
    padding: 16px 20px;
    max-height: 300px;
    overflow-y: auto;
  }

  .ticket-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-subtle);
  }

  .ticket-item:last-child {
    border-bottom: none;
  }

  .ticket-item-thumb {
    flex: 0 0 auto;
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 8px;
    background: var(--surface-raised, #f5f5f5);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: var(--ink-muted, #7a828a);
  }

  .ticket-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .ticket-item-thumb svg {
    width: 22px;
    height: 22px;
  }

  .ticket-item-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-left: 0;
  }

  .ticket-item-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--ink-primary);
  }

  .ticket-item-qty {
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--ink-secondary);
  }

  .ticket-item-notes {
    font-size: 12px;
    font-style: italic;
    color: var(--ink-muted, #7a828a);
  }

  .ticket-item-price {
    font-family: var(--font-mono);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-primary);
  }

  .order-ticket-footer {
    display: flex;
    gap: 8px;
    padding: 16px 20px;
    border-top: 1px solid var(--border-subtle);
    background: var(--surface-elevated);
  }

  .kitchen-btn {
    flex: 1;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: var(--radius-md);
    transition: all 0.15s ease;
  }

  .kitchen-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .btn-start { background: var(--accent-sulfur); color: var(--ink-inverse); border-color: var(--accent-sulfur); }
  .btn-start:hover:not(:disabled) { background: var(--accent-sulfur-ink); border-color: var(--accent-sulfur-ink); }

  .btn-ready { background: var(--accent-teal); color: var(--ink-inverse); border-color: var(--accent-teal); }
  .btn-ready:hover:not(:disabled) { background: var(--accent-teal-ink); border-color: var(--accent-teal-ink); }

  .btn-serve { background: var(--ink-primary); color: var(--ink-inverse); border-color: var(--ink-primary); }
  .btn-serve:hover:not(:disabled) { background: var(--ink-secondary); border-color: var(--ink-secondary); }

  .empty-kitchen {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--ink-secondary);
  }

  .empty-kitchen svg {
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    opacity: 0.4;
  }

  .kitchen-loading {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: var(--ink-secondary);
  }

  .refresh-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 999px;
    font-size: 12px;
    color: var(--ink-secondary);
  }

  .refresh-indicator.spinning svg {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  @media (max-width: 640px) {
    .orders-grid {
      grid-template-columns: 1fr;
    }
    .kitchen-title h1 {
      font-size: 20px;
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="kitchen-shell">
  <header class="kitchen-header">
    <div class="kitchen-title">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2a2 2 0 0 1 2 2v4a2 2 0 0 1-4 0V4a2 2 0 0 1 2-2z"></path>
        <path d="M2 10h20"></path>
        <path d="M6 14v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4"></path>
        <path d="M6 14H6.01"></path>
        <path d="M10 14h.01"></path>
        <path d="M14 14h.01"></path>
        <path d="M18 14h.01"></path>
      </svg>
      <div>
        <div class="eyebrow">Kitchen</div>
        <h1 class="page-title" style="margin: 0;">Display System</h1>
      </div>
    </div>
    <div class="kitchen-stats" id="kitchenStats"></div>
  </header>

  <section class="orders-grid" id="ordersGrid" role="list" aria-label="Active orders">
    <div class="kitchen-loading" id="loadingState">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="animation: spin 1s linear infinite; margin-bottom: 12px;">
        <path d="M23 4v6h-6"></path>
        <path d="M1 20v-6h6"></path>
        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
      </svg>
      <p>Loading active orders...</p>
    </div>
    <div class="empty-kitchen" id="emptyState" hidden>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M12 2a2 2 0 0 1 2 2v4a2 2 0 0 1-4 0V4a2 2 0 0 1 2-2z"></path>
        <path d="M2 10h20"></path>
        <path d="M6 14v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4"></path>
      </svg>
      <p>No active orders</p>
      <p style="font-size: 12px; margin-top: 4px;">Orders will appear here when placed</p>
    </div>
  </section>

  <div class="refresh-indicator" id="refreshIndicator" style="margin-top: 16px; display: flex; justify-content: center;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M23 4v6h-6"></path>
      <path d="M1 20v-6h6"></path>
      <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
    </svg>
    <span id="refreshText">Auto-refresh: 15s</span>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script>
  (function () {
    const ordersGrid = document.getElementById('ordersGrid');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const kitchenStats = document.getElementById('kitchenStats');
    const refreshText = document.getElementById('refreshText');
    const refreshIndicator = document.getElementById('refreshIndicator');

    let refreshInterval = null;
    const REFRESH_MS = 15000;
    let countdown = REFRESH_MS / 1000;

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function formatMoney(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ETB',
        maximumFractionDigits: 2
      }).format(Number(value || 0));
    }

    function getStatusClass(status) {
      return 'status-' + status;
    }

    function getStatusLabel(status) {
      return status.charAt(0).toUpperCase() + status.slice(1);
    }

    function getActionButtons(order) {
      const status = order.status;
      const buttons = [];

      if (status === 'pending') {
        buttons.push(`
          <button class="btn kitchen-btn btn-start" data-action="start" data-order-id="${order.id}" data-next-status="preparing">
            Start Cooking
          </button>
        `);
      }
      if (status === 'preparing') {
        buttons.push(`
          <button class="btn kitchen-btn btn-ready" data-action="ready" data-order-id="${order.id}" data-next-status="ready">
            Mark Ready
          </button>
        `);
      }
      if (status === 'ready') {
        buttons.push(`
          <button class="btn kitchen-btn btn-serve" data-action="serve" data-order-id="${order.id}" data-next-status="served">
            Mark Served
          </button>
        `);
      }
      if (status === 'served') {
        buttons.push(`
          <button class="btn kitchen-btn btn-serve" data-action="complete" data-order-id="${order.id}" data-next-status="completed">
            Complete
          </button>
        `);
      }

      return buttons.join('');
    }

    function renderOrderCard(order) {
      const itemsHtml = (order.items || []).map(item => {
        const name = item.menu_item_name || item.name || 'Item';
        const qty = item.quantity ?? 0;
        const price = item.unit_price ?? item.price ?? 0;
        const subtotal = item.subtotal ?? (price * qty);
        const notes = (item.special_instructions || item.notes || '').trim();
        const image = item.menu_item_image || item.image || '';

        const imageUrl = image
          ? (image.startsWith('http') ? image : '/' + image.replace(/^\//, ''))
          : '';

        const thumbHtml = imageUrl
          ? `<div class="ticket-item-thumb" aria-hidden="true"><img src="${escapeHtml(imageUrl)}" alt="" loading="lazy"></div>`
          : `<div class="ticket-item-thumb" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" role="presentation">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <path d="M21 15l-5-5L5 21"></path>
              </svg>
            </div>`;

        return `
          <div class="ticket-item">
            ${thumbHtml}
            <div class="ticket-item-info">
              <span class="ticket-item-name">${escapeHtml(name)}</span>
              <span class="ticket-item-qty">Qty: ${qty} × ${formatMoney(price)}</span>
              ${notes ? `<span class="ticket-item-notes">${escapeHtml(notes)}</span>` : ''}
            </div>
            <span class="ticket-item-price">${formatMoney(subtotal)}</span>
          </div>
        `;
      }).join('');

      return `
        <article class="order-ticket" data-order-id="${order.id}" role="listitem">
          <header class="order-ticket-header">
            <div>
              <span class="order-ticket-id">Order #${order.id}</span>
              <span class="order-ticket-table">Table ${escapeHtml(order.table_number || order.table_id)}</span>
            </div>
            <span class="order-ticket-status ${getStatusClass(order.status)}">${getStatusLabel(order.status)}</span>
          </header>
          <div class="order-ticket-items">
            ${itemsHtml}
          </div>
          <footer class="order-ticket-footer">
            ${getActionButtons(order)}
          </footer>
        </article>
      `;
    }

    function updateStats(orders) {
      const counts = { pending: 0, preparing: 0, ready: 0, served: 0 };
      orders.forEach(o => { if (counts.hasOwnProperty(o.status)) counts[o.status]++; });

      kitchenStats.innerHTML = `
        <span class="stat-badge stat-all">${orders.length} Total</span>
        <span class="stat-badge stat-preparing">${counts.preparing} Preparing</span>
        <span class="stat-badge stat-ready">${counts.ready} Ready</span>
        <span class="stat-badge stat-served">${counts.served} Served</span>
      `;
    }

    async function loadOrders() {
      loadingState.hidden = false;
      emptyState.hidden = true;
      ordersGrid.querySelectorAll('.order-ticket').forEach(el => el.remove());

      try {
        const data = await api.get('/orders', { status: 'pending,preparing,ready,served', per_page: 100 });
        const orders = data.data?.orders || data.data || [];

        loadingState.hidden = true;

        if (!orders.length) {
          emptyState.hidden = false;
          return;
        }

        emptyState.hidden = true;
        console.log('[KDS] orders payload:', orders);
        orders.forEach(order => {
          console.log('[KDS] rendering order', order.id, 'items key =', Array.isArray(order.items) ? order.items.length : order.items, 'keys =', order.items && Array.isArray(order.items) ? Object.keys(order.items[0] || {}) : 'N/A');
          const cardHtml = renderOrderCard(order);
          ordersGrid.insertAdjacentHTML('beforeend', cardHtml);
        });

        updateStats(orders);
        bindActionButtons();
      } catch (error) {
        console.error('Failed to load orders:', error);
        loadingState.hidden = true;
        loadingState.innerHTML = `
          <p style="color: var(--accent-rust);">Failed to load orders</p>
          <p style="font-size: 12px; margin-top: 4px;">${escapeHtml(error.message)}</p>
        `;
        loadingState.hidden = false;
      }
    }

    async function updateOrderStatus(orderId, newStatus, button) {
      button.disabled = true;
      const originalText = button.textContent;
      button.textContent = 'Updating...';

      try {
        const data = await api.patch(`/orders/${orderId}/status`, { status: newStatus });
        const order = data.data || data;

        // Update UI optimistically or re-render
        await loadOrders();
        if (window.toast) {
          window.toast.success('Updated', `Order #${orderId} marked as ${getStatusLabel(newStatus)}`);
        }
      } catch (error) {
        console.error('Failed to update status:', error);
        if (window.toast) {
          window.toast.error('Error', error.message);
        }
        button.disabled = false;
        button.textContent = originalText;
      }
    }

    function bindActionButtons() {
      ordersGrid.querySelectorAll('[data-action]').forEach(btn => {
        btn.removeEventListener('click', handleActionClick);
        btn.addEventListener('click', handleActionClick);
      });
    }

    function handleActionClick(e) {
      const btn = e.currentTarget;
      const action = btn.dataset.action;
      const orderId = btn.dataset.orderId;
      const nextStatus = btn.dataset.nextStatus;

      if (!orderId || !nextStatus) return;

      updateOrderStatus(orderId, nextStatus, btn);
    }

    function startCountdown() {
      countdown = REFRESH_MS / 1000;
      refreshText.textContent = `Auto-refresh: ${countdown}s`;
      refreshIndicator.classList.add('spinning');

      const tick = setInterval(() => {
        countdown--;
        refreshText.textContent = `Auto-refresh: ${countdown}s`;
        if (countdown <= 0) {
          clearInterval(tick);
          refreshIndicator.classList.remove('spinning');
        }
      }, 1000);
    }

    function scheduleRefresh() {
      if (refreshInterval) clearInterval(refreshInterval);
      refreshInterval = setInterval(() => {
        loadOrders();
        startCountdown();
      }, REFRESH_MS);
    }

    // Initialize
    loadOrders();
    startCountdown();
    scheduleRefresh();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
      if (refreshInterval) clearInterval(refreshInterval);
    });
  })();
</script>
<?= $this->endSection() ?>