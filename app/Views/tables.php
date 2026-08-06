<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .modal-overlay[hidden] { display: none !important; }

  .tables-shell {
    padding: 20px;
  }

  .tables-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .tables-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .tables-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .table-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
  }

  .table-tile {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    padding: 24px 20px;
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
    font-size: 48px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 8px;
    color: var(--ink-primary);
  }

  .table-tile.occupied .number {
    color: var(--accent-sulfur);
  }

  .table-tile .capacity {
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--ink-secondary);
    margin-bottom: 16px;
  }

  .table-tile .status-badge {
    margin-bottom: 16px;
  }

  .table-tile-actions {
    display: flex;
    gap: 8px;
    width: 100%;
    justify-content: center;
    padding-top: 12px;
    border-top: 1px solid var(--border-subtle);
  }

  .table-tile .icon-btn {
    width: 36px;
    height: 36px;
  }

  .empty-state-grid {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--ink-secondary);
  }

  .empty-state-grid svg {
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    opacity: 0.4;
  }

  @media (max-width: 640px) {
    .tables-header { flex-direction: column; align-items: stretch; }
    .tables-header-left { justify-content: space-between; }
    .tables-header-right { justify-content: stretch; }
    .tables-header-right .btn { flex: 1; }
    .table-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
    .table-tile .number { font-size: 36px; }
    .table-tile { padding: 20px 16px; }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="tables-shell">
  <header class="tables-header">
    <div class="tables-header-left">
      <div>
        <div class="eyebrow">Tables</div>
        <h1 class="page-title" style="margin: 0;">Floor Management</h1>
        <p class="page-subtitle" style="margin: 4px 0 0;">Visual floor layout with table status and quick actions</p>
      </div>
    </div>
    <div class="tables-header-right">
      <button class="btn btn-secondary" type="button" id="reloadTablesBtn" aria-label="Reload tables">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
      </button>
      <button class="btn btn-primary" type="button" id="addTableBtn">Add Table</button>
    </div>
  </header>

  <section class="table-grid" id="tableGrid" role="list" aria-label="Restaurant tables">
    <!-- Table tiles rendered by JS -->
    <div class="empty-state-grid">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
      </svg>
      <p>Loading tables...</p>
    </div>
  </section>
</div>

<!-- Add/Edit Table Modal -->
<div class="modal-overlay" id="tableModal" role="dialog" aria-modal="true" aria-labelledby="tableModalTitle" hidden>
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="tableModalTitle">Add Table</h2>
      <button class="modal-close" id="tableModalClose" aria-label="Close">&times;</button>
    </div>
    <form id="tableForm" class="modal-body">
      <input type="hidden" name="id" id="tableId">
      <div class="form-group">
        <label class="form-label" for="tableNumber">Table Number *</label>
        <input type="text" class="form-input" name="table_number" id="tableNumber" required placeholder="e.g. 1, A1, VIP-1" maxlength="20">
      </div>
      <div class="form-group">
        <label class="form-label" for="tableCapacity">Capacity *</label>
        <input type="number" class="form-input" name="capacity" id="tableCapacity" min="1" max="50" required placeholder="4" value="4">
      </div>
      <div class="form-group">
        <label class="form-label" for="tableStatus">Status</label>
        <select class="form-select" name="status" id="tableStatus">
          <option value="available">Available</option>
          <option value="occupied">Occupied</option>
        </select>
      </div>
    </form>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="tableModalCancel">Cancel</button>
      <button class="btn btn-primary" type="button" id="tableModalSave" form="tableForm">Save Table</button>
    </div>
  </div>
</div>

<!-- Delete Confirmation uses shared confirmModal -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script>
  (function () {
    const tableGrid = document.getElementById('tableGrid');
    const addTableBtn = document.getElementById('addTableBtn');
    const reloadTablesBtn = document.getElementById('reloadTablesBtn');

    // Modal elements
    const tableModal = document.getElementById('tableModal');
    const tableForm = document.getElementById('tableForm');
    const tableModalTitle = document.getElementById('tableModalTitle');
    const tableId = document.getElementById('tableId');
    const tableNumber = document.getElementById('tableNumber');
    const tableCapacity = document.getElementById('tableCapacity');
    const tableStatus = document.getElementById('tableStatus');
    const tableModalClose = document.getElementById('tableModalClose');
    const tableModalCancel = document.getElementById('tableModalCancel');
    const tableModalSave = document.getElementById('tableModalSave');

    let allTables = [];

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function getStatusBadge(status) {
      const isOccupied = status === 'occupied';
      return `
        <span class="status-badge ${isOccupied ? 'badge-occupied' : 'badge-available'}">
          <span class="dot"></span>
          ${isOccupied ? 'Occupied' : 'Available'}
        </span>
      `;
    }

    function renderTableTile(table) {
      const isOccupied = table.status === 'occupied';
      return `
        <article class="table-tile ${isOccupied ? 'occupied' : ''}" data-table-id="${table.id}" role="listitem">
          <span class="number">${escapeHtml(table.table_number)}</span>
          <span class="capacity">${table.capacity} seats</span>
          ${getStatusBadge(table.status)}
          <div class="table-tile-actions">
            <button class="icon-btn" data-action="toggle-status" data-table-id="${table.id}" aria-label="${isOccupied ? 'Mark available' : 'Mark occupied'}">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                ${isOccupied ? '<path d="M9 12l2 2 4-4"></path>' : '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'}
              </svg>
            </button>
            <button class="icon-btn" data-action="edit" data-table-id="${table.id}" aria-label="Edit table">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="icon-btn danger" data-action="delete" data-table-id="${table.id}" aria-label="Delete table">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </article>
      `;
    }

    function renderTableGrid() {
      if (!allTables.length) {
        tableGrid.innerHTML = `
          <div class="empty-state-grid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
            </svg>
            <p>No tables found</p>
            <p style="font-size: 12px; margin-top: 4px;">Click "Add Table" to create your first table</p>
          </div>
        `;
        return;
      }

      tableGrid.innerHTML = allTables.map(renderTableTile).join('');

      // Bind action buttons
      tableGrid.querySelectorAll('[data-action="toggle-status"]').forEach(btn => {
        btn.addEventListener('click', () => toggleTableStatus(Number(btn.dataset.tableId)));
      });
      tableGrid.querySelectorAll('[data-action="edit"]').forEach(btn => {
        btn.addEventListener('click', () => openEditModal(Number(btn.dataset.tableId)));
      });
      tableGrid.querySelectorAll('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', () => deleteTable(Number(btn.dataset.tableId)));
      });
    }

    async function loadTables() {
      try {
        tableGrid.innerHTML = `
          <div class="empty-state-grid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
            </svg>
            <p>Loading tables...</p>
          </div>
        `;
        const data = await api.get('/tables');
        allTables = data.data || [];
        renderTableGrid();
      } catch (error) {
        console.error('Failed to load tables:', error);
        tableGrid.innerHTML = `
          <div class="empty-state-grid" style="color: var(--accent-rust);">
            <p>Failed to load tables</p>
            <p style="font-size: 12px; margin-top: 4px;">${escapeHtml(error.message)}</p>
          </div>
        `;
        toast.error('Error', 'Failed to load tables: ' + error.message);
      }
    }

    // Modal functions
    function openAddModal() {
      tableForm.reset();
      tableId.value = '';
      tableNumber.value = '';
      tableCapacity.value = '4';
      tableStatus.value = 'available';
      tableModalTitle.textContent = 'Add Table';
      tableModal.hidden = false;
      document.body.style.overflow = 'hidden';
      tableNumber.focus();
    }

    function openEditModal(id) {
      const table = allTables.find(t => String(t.id) === String(id));
      if (!table) return;

      tableId.value = table.id;
      tableNumber.value = table.table_number;
      tableCapacity.value = table.capacity;
      tableStatus.value = table.status;
      tableModalTitle.textContent = 'Edit Table';
      tableModal.hidden = false;
      document.body.style.overflow = 'hidden';
      tableNumber.focus();
    }

    function closeTableModal() {
      tableModal.hidden = true;
      document.body.style.overflow = '';
    }

    async function saveTable() {
      if (!tableForm.checkValidity()) {
        tableForm.reportValidity();
        return;
      }

      const id = tableId.value;
      const data = {
        table_number: tableNumber.value.trim(),
        capacity: Number(tableCapacity.value),
        status: tableStatus.value
      };

      try {
        tableModalSave.disabled = true;
        tableModalSave.textContent = 'Saving...';

        if (id) {
          await api.put(`/tables/${id}`, data);
          toast.success('Success', 'Table updated');
        } else {
          await api.post('/tables', data);
          toast.success('Success', 'Table created');
        }
        closeTableModal();
        await loadTables();
      } catch (error) {
        console.error('Failed to save table:', error);
        toast.error('Error', error.message);
      } finally {
        tableModalSave.disabled = false;
        tableModalSave.textContent = 'Save Table';
      }
    }

    async function toggleTableStatus(id) {
      const table = allTables.find(t => String(t.id) === String(id));
      if (!table) return;

      const newStatus = table.status === 'occupied' ? 'available' : 'occupied';
      const actionText = newStatus === 'occupied' ? 'mark as occupied' : 'mark as available';

      try {
        await api.patch(`/tables/${id}/status`, { status: newStatus });
        toast.success('Updated', `Table ${escapeHtml(table.table_number)} ${actionText}`);
        await loadTables();
      } catch (error) {
        console.error('Failed to toggle status:', error);
        toast.error('Error', error.message);
      }
    }

    async function deleteTable(id) {
      const table = allTables.find(t => String(t.id) === String(id));
      if (!table) return;

      const confirmed = await confirmModal.open({
        title: 'Delete Table',
        message: `Delete table "${escapeHtml(table.table_number)}"? This cannot be undone.`,
        confirmLabel: 'Delete table',
        cancelLabel: 'Keep table',
        variant: 'destructive'
      });

      if (!confirmed) return;

      try {
        await api.delete(`/tables/${id}`);
        toast.success('Deleted', 'Table deleted');
        await loadTables();
      } catch (error) {
        console.error('Failed to delete table:', error);
        if (error.status === 409) {
          toast.error('Cannot Delete', error.message);
        } else {
          toast.error('Error', 'Failed to delete table: ' + error.message);
        }
      }
    }

    // Event listeners
    addTableBtn.addEventListener('click', openAddModal);
    reloadTablesBtn.addEventListener('click', loadTables);
    tableModalClose.addEventListener('click', closeTableModal);
    tableModalCancel.addEventListener('click', closeTableModal);
    tableModalSave.addEventListener('click', saveTable);
    tableModal.addEventListener('click', (e) => { if (e.target === tableModal) closeTableModal(); });

    // Keyboard: Escape to close modal
    document.addEventListener('keydown', (e) => {
      if (!tableModal.hidden && e.key === 'Escape') closeTableModal();
    });

    // Initialize
    loadTables();
  })();
</script>
<?= $this->endSection() ?>