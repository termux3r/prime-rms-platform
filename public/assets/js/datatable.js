class DataTable {
  constructor(options) {
    this.options = {
      endpoint: options.endpoint,
      container: options.container,
      columns: options.columns,
      searchable: options.searchable ?? true,
      filterable: options.filterable ?? true,
      paginate: options.paginate ?? true,
      perPage: options.perPage ?? 20,
      page: 1,
      totalPages: 1,
      totalItems: 0,
      filters: options.initialFilters || {},
      searchDebounce: options.searchDebounce ?? 300,
      onRowClick: options.onRowClick,
      rowActions: options.rowActions || [],
      emptyMessage: options.emptyMessage || 'No data available',
      loadingMessage: options.loadingMessage || 'Loading...',
      errorMessage: options.errorMessage || 'Failed to load data',
      transformRow: options.transformRow,
      toolbarActions: options.toolbarActions || [],
      filtersConfig: options.filtersConfig || {},
      ...options
    };

    this.searchTimeout = null;
    this.abortController = null;

    this.render();
    this.bindEvents();
    this.load();
  }

  render() {
    const container = document.getElementById(this.options.container);
    if (!container) return;

    container.innerHTML = `
      <div class="datatable-wrapper">
        ${this.renderToolbar()}
        <div class="datatable-table-wrapper">
          <table class="data-table" id="${this.options.container}-table">
            <thead>${this.renderHeader()}</thead>
            <tbody id="${this.options.container}-body"></tbody>
          </table>
        </div>
        ${this.options.paginate ? this.renderPagination() : ''}
      </div>
    `;
  }

  renderToolbar() {
    const { searchable, filterable } = this.options;
    let html = '<div class="toolbar">';

    if (searchable) {
      html += `
        <div class="toolbar-search">
          <input type="search" class="form-input" id="${this.options.container}-search"
            placeholder="Search..." aria-label="Search">
        </div>
      `;
    }

    if (filterable && Object.keys(this.options.filtersConfig).length) {
      html += '<div class="toolbar-filters">';
      for (const [key, config] of Object.entries(this.options.filtersConfig)) {
        if (config.type === 'select') {
          html += `
            <select class="form-select" id="${this.options.container}-filter-${key}" aria-label="${config.label}">
              <option value="">All ${config.label}</option>
              ${config.options.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
            </select>
          `;
        }
      }
      html += '</div>';
    }

    if (this.options.toolbarActions.length) {
      html += '<div class="toolbar-actions">';
      for (const action of this.options.toolbarActions) {
        html += `<button class="btn ${action.class || 'btn-primary'}" id="${this.options.container}-action-${action.id}" type="button">${action.label}</button>`;
      }
      html += '</div>';
    }

    html += '</div>';
    return html;
  }

  renderHeader() {
    const actionCol = this.options.rowActions.length ? '<th style="width: 80px;">Actions</th>' : '';
    return `<tr>${this.options.columns.map(col => `<th${col.width ? ` style="width:${col.width}"` : ''}>${col.label}</th>`).join('')}${actionCol}</tr>`;
  }

  renderPagination() {
    return `
      <div class="pagination" id="${this.options.container}-pagination">
        <button class="page-link" id="${this.options.container}-prev" disabled aria-label="Previous">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <span class="page-info" id="${this.options.container}-page-info">Page 1 of 1</span>
        <button class="page-link" id="${this.options.container}-next" disabled aria-label="Next">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </div>
    `;
  }

  bindEvents() {
    const searchInput = document.getElementById(`${this.options.container}-search`);
    if (searchInput) {
      searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    }

    for (const key of Object.keys(this.options.filtersConfig)) {
      const select = document.getElementById(`${this.options.container}-filter-${key}`);
      if (select) {
        select.addEventListener('change', (e) => this.handleFilter(key, e.target.value));
      }
    }

    for (const action of this.options.toolbarActions) {
      const btn = document.getElementById(`${this.options.container}-action-${action.id}`);
      if (btn && action.onClick) {
        btn.addEventListener('click', action.onClick.bind(this));
      }
    }

    const prevBtn = document.getElementById(`${this.options.container}-prev`);
    const nextBtn = document.getElementById(`${this.options.container}-next`);
    if (prevBtn) prevBtn.addEventListener('click', () => this.goToPage(this.options.page - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => this.goToPage(this.options.page + 1));

    const tbody = document.getElementById(`${this.options.container}-body`);
    if (tbody && this.options.rowActions.length) {
      tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (btn) {
          const rowId = btn.closest('tr')?.dataset.id;
          const action = btn.dataset.action;
          const actionConfig = this.options.rowActions.find(a => a.id === action);
          if (actionConfig && rowId) {
            actionConfig.handler(rowId, btn);
          }
        }
      });
    }

    if (tbody && this.options.onRowClick) {
      tbody.addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (tr && !e.target.closest('[data-action]') && tr.dataset.id) {
          this.options.onRowClick(tr.dataset.id, tr);
        }
      });
    }
  }

  handleSearch(value) {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
      if (value.trim() === '') {
        delete this.options.filters.search;
      } else {
        this.options.filters.search = value.trim();
      }
      this.options.page = 1;
      this.load();
    }, this.options.searchDebounce);
  }

  handleFilter(key, value) {
    if (value === '') {
      delete this.options.filters[key];
    } else {
      this.options.filters[key] = value;
    }
    this.options.page = 1;
    this.load();
  }

  goToPage(page) {
    if (page < 1 || page > this.options.totalPages) return;
    this.options.page = page;
    this.load();
  }

  async load() {
    const tbody = document.getElementById(`${this.options.container}-body`);
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="${this.options.columns.length + (this.options.rowActions.length ? 1 : 0)}" class="empty-state" style="padding:40px;text-align:center;">${this.options.loadingMessage}</td></tr>`;
    this.updatePaginationState(true);

    if (this.abortController) this.abortController.abort();
    this.abortController = new AbortController();

    try {
      const params = { page: this.options.page, per_page: this.options.perPage, ...this.options.filters };
      const data = await window.api.get(this.options.endpoint, params);

      const items = data.data?.items || data.data || [];
      const pager = data.data?.pager || {};

      this.options.totalPages = pager.pageCount || 1;
      this.options.totalItems = pager.total || items.length;

      this.renderRows(items);
      this.updatePaginationState(false);
    } catch (error) {
      if (error.name !== 'AbortError') {
        tbody.innerHTML = `<tr><td colspan="${this.options.columns.length + (this.options.rowActions.length ? 1 : 0)}" class="error-state" style="padding:40px;text-align:center;">${this.options.errorMessage}</td></tr>`;
        if (window.toast) window.toast.error('Error', error.message);
        this.updatePaginationState(false);
      }
    }
  }

  renderRows(items) {
    const tbody = document.getElementById(`${this.options.container}-body`);
    if (!tbody) return;

    if (!items.length) {
      const colCount = this.options.columns.length + (this.options.rowActions.length ? 1 : 0);
      tbody.innerHTML = `<tr><td colspan="${colCount}" class="empty-state" style="padding:40px;text-align:center;">${this.options.emptyMessage}</td></tr>`;
      return;
    }

    tbody.innerHTML = items.map(item => {
      const row = this.options.transformRow ? this.options.transformRow(item) : item;
      const cells = this.options.columns.map(col => {
        let value = row[col.key];
        if (col.render) value = col.render(value, row);
        return `<td${col.class ? ` class="${col.class}"` : ''}>${value}</td>`;
      }).join('');

      const actions = this.options.rowActions.length ? `
        <td class="actions">
          ${this.options.rowActions.map(a => `
            <button class="icon-btn ${a.danger ? 'danger' : ''}" data-action="${a.id}" title="${a.title}" aria-label="${a.title}">
              ${a.icon}
            </button>
          `).join('')}
        </td>
      ` : '';

      return `<tr data-id="${row.id}">${cells}${actions}</tr>`;
    }).join('');
  }

  updatePaginationState(loading) {
    const prevBtn = document.getElementById(`${this.options.container}-prev`);
    const nextBtn = document.getElementById(`${this.options.container}-next`);
    const pageInfo = document.getElementById(`${this.options.container}-page-info`);

    if (prevBtn) prevBtn.disabled = loading || this.options.page <= 1;
    if (nextBtn) nextBtn.disabled = loading || this.options.page >= this.options.totalPages;
    if (pageInfo) pageInfo.textContent = `Page ${this.options.page} of ${this.options.totalPages} (${this.options.totalItems} items)`;
  }

  refresh() {
    this.load();
  }

  setFilters(filters) {
    this.options.filters = { ...this.options.filters, ...filters };
    for (const [key, value] of Object.entries(filters)) {
      const select = document.getElementById(`${this.options.container}-filter-${key}`);
      if (select) select.value = value;
    }
    this.options.page = 1;
    this.load();
  }
}

window.DataTable = DataTable;