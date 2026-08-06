<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .category-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
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
  .menu-table-image {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    background:
      radial-gradient(circle at 20% 20%, rgba(181,138,18,0.28), transparent 30%),
      radial-gradient(circle at 80% 15%, rgba(43,104,95,0.18), transparent 28%),
      linear-gradient(135deg, #fef3c7, #f2ded2);
  }
  .price-cell { font-family: var(--font-mono); font-weight: 600; white-space: nowrap; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
  .modal-overlay[hidden] { display: none !important; }
</style>
<div class="content-header">
  <div>
    <div class="eyebrow">Menu</div>
    <h1 class="page-title">Menu Management</h1>
    <p class="page-subtitle">Manage categories and menu items. Changes reflect immediately on the POS.</p>
  </div>
  <div class="content-actions">
    <button class="btn btn-secondary" type="button" id="manageCategoriesBtn">Manage Categories</button>
  </div>
</div>

<div class="card" style="margin-bottom: 20px;">
  <div class="category-tabs" id="categoryTabs" role="tablist" aria-label="Menu categories">
    <button class="category-tab active" data-category-id="" role="tab" aria-selected="true">All Items</button>
  </div>
</div>

<div class="card" id="menuItemsTable"></div>

<!-- Add/Edit Category Modal -->
<div class="modal-overlay" id="categoryModal" role="dialog" aria-modal="true" aria-labelledby="categoryModalTitle" hidden>
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="categoryModalTitle">Add Category</h2>
      <button class="modal-close" id="categoryModalClose" aria-label="Close" data-bs-dismiss="modal">&times;</button>
    </div>
    <form id="categoryForm" class="modal-body">
      <input type="hidden" name="id" id="categoryId">
      <div class="form-group">
        <label class="form-label" for="categoryName">Name *</label>
        <input type="text" class="form-input" name="name" id="categoryName" required placeholder="e.g. Appetizers, Main Course, Beverages">
      </div>
      <div class="form-group">
        <label class="form-label" for="categoryDescription">Description</label>
        <textarea class="form-textarea" name="description" id="categoryDescription" rows="3" placeholder="Optional description"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label" for="categoryStatus">Status</label>
        <select class="form-select" name="status" id="categoryStatus">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </form>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="categoryModalCancel" data-bs-dismiss="modal">Cancel</button>
      <button class="btn btn-primary" type="button" id="categoryModalSave" form="categoryForm">Save Category</button>
    </div>
  </div>
</div>

<!-- Manage Categories Modal -->
<div class="modal-overlay" id="manageCategoriesModal" role="dialog" aria-modal="true" aria-labelledby="manageCategoriesTitle" hidden>
  <div class="modal" style="max-width: 720px;">
    <div class="modal-header">
      <h2 class="modal-title" id="manageCategoriesTitle">Manage Categories</h2>
      <button class="modal-close" id="manageCategoriesClose" aria-label="Close" data-bs-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="manage-categories-toolbar">
        <div class="content-actions">
          <button class="btn btn-primary" type="button" id="manageAddCategoryBtn">Add Category</button>
        </div>
      </div>
      <table class="table table-hover manage-categories-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Status</th>
            <th style="width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody id="manageCategoriesTbody">
          <tr class="empty-row"><td colspan="4">Loading categories...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="manageCategoriesCancel" data-bs-dismiss="modal">Close</button>
    </div>
  </div>
</div>

<!-- Add/Edit Menu Item Modal -->
<div class="modal-overlay" id="itemModal" role="dialog" aria-modal="true" aria-labelledby="itemModalTitle" hidden>
  <div class="modal" style="max-width: 600px;">
    <div class="modal-header">
      <h2 class="modal-title" id="itemModalTitle">Add Menu Item</h2>
      <button class="modal-close" id="itemModalClose" aria-label="Close" data-bs-dismiss="modal">&times;</button>
    </div>
    <form id="itemForm" class="modal-body" enctype="multipart/form-data">
      <input type="hidden" name="id" id="itemId">
      <div class="form-group">
        <label class="form-label" for="itemName">Name *</label>
        <input type="text" class="form-input" name="name" id="itemName" required placeholder="e.g. Grilled Salmon">
      </div>
      <div class="form-group">
        <label class="form-label" for="itemCategoryId">Category *</label>
        <select class="form-select" name="category_id" id="itemCategoryId" required>
          <option value="">Select category</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="itemPrice">Price (ETB) *</label>
        <input type="number" class="form-input" name="price" id="itemPrice" step="0.01" min="0" required placeholder="0.00">
      </div>
      <div class="form-group">
        <label class="form-label" for="itemDescription">Description</label>
        <textarea class="form-textarea" name="description" id="itemDescription" rows="3" placeholder="Ingredients, preparation notes..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label" for="itemImage">Image</label>
        <input type="file" class="form-input" name="image" id="itemImage" accept="image/jpeg,image/png,image/webp">
        <div class="image-preview" id="itemImagePreview" style="margin-top: 10px; display: none;">
          <img id="itemImagePreviewImg" src="" alt="Preview" style="max-width: 150px; max-height: 150px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="itemStatus">Status</label>
        <select class="form-select" name="status" id="itemStatus">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </form>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="itemModalCancel" data-bs-dismiss="modal">Cancel</button>
      <button class="btn btn-primary" type="button" id="itemModalSave" form="itemForm">Save Item</button>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable.js') ?>"></script>
<script>
  (function () {
    let categoriesDataTable = null;
    let itemsDataTable = null;
    let allCategories = [];

    const categoryTabs = document.getElementById('categoryTabs');
    const menuItemsTable = document.getElementById('menuItemsTable');

    // Category modal elements
    const categoryModal = document.getElementById('categoryModal');
    const categoryForm = document.getElementById('categoryForm');
    const categoryModalTitle = document.getElementById('categoryModalTitle');
    const categoryId = document.getElementById('categoryId');
    const categoryName = document.getElementById('categoryName');
    const categoryDescription = document.getElementById('categoryDescription');
    const categoryStatus = document.getElementById('categoryStatus');
    const closeCategoryBtn = document.getElementById('categoryModalClose');
    const categoryModalCancel = document.getElementById('categoryModalCancel');
    const categoryModalSave = document.getElementById('categoryModalSave');

    // Manage Categories modal elements
    const manageCategoriesBtn = document.getElementById('manageCategoriesBtn');
    const manageCategoriesModal = document.getElementById('manageCategoriesModal');
    const manageCategoriesClose = document.getElementById('manageCategoriesClose');
    const manageCategoriesCancel = document.getElementById('manageCategoriesCancel');
    const manageAddCategoryBtn = document.getElementById('manageAddCategoryBtn');
    const manageCategoriesTbody = document.getElementById('manageCategoriesTbody');

    // Item modal elements
    const itemModal = document.getElementById('itemModal');
    const itemForm = document.getElementById('itemForm');
    const itemModalTitle = document.getElementById('itemModalTitle');
    const itemId = document.getElementById('itemId');
    const itemName = document.getElementById('itemName');
    const itemCategoryId = document.getElementById('itemCategoryId');
    const itemPrice = document.getElementById('itemPrice');
    const itemDescription = document.getElementById('itemDescription');
    const itemImage = document.getElementById('itemImage');
    const itemImagePreview = document.getElementById('itemImagePreview');
    const itemImagePreviewImg = document.getElementById('itemImagePreviewImg');
    const itemStatus = document.getElementById('itemStatus');
    const itemModalClose = document.getElementById('itemModalClose');
    const itemModalCancel = document.getElementById('itemModalCancel');
    const itemModalSave = document.getElementById('itemModalSave');

    // Initialize
    async function init() {
      await loadCategories();
      initItemsTable();
      bindEvents();
    }

    async function loadCategories() {
      try {
        console.log('Fetching categories from /menu-categories...');
        const data = await api.get('/menu-categories', { per_page: 100, status: 'active' });
        console.log('Full API response:', data);
        console.log('data.data:', data.data);
        console.log('data.data.categories:', data.data?.categories);
        
        allCategories = data.data?.categories || data.data || [];
        console.log('Loaded categories (after parsing):', allCategories);

        // Populate category tabs
        renderCategoryTabs();

        // Populate category select in item modal
        populateCategoryDropdown();
      } catch (error) {
        console.error('Failed to load categories:', error);
        console.error('Error status:', error.status);
        console.error('Error data:', error.data);
        toast.error('Failed to load categories', error.message);
        // Fallback: try without status filter
        try {
          console.log('Trying fallback without status filter...');
          const fallbackData = await api.get('/menu-categories', { per_page: 100 });
          console.log('Fallback API response:', fallbackData);
          allCategories = fallbackData.data?.categories || fallbackData.data || [];
          console.log('Loaded categories (fallback):', allCategories);
          renderCategoryTabs();
          populateCategoryDropdown();
        } catch (fallbackError) {
          console.error('Fallback also failed:', fallbackError);
        }
      }
    }

    function populateCategoryDropdown() {
      console.log('populateCategoryDropdown called');
      console.log('allCategories:', allCategories);
      console.log('itemCategoryId element:', itemCategoryId);
      
      if (!itemCategoryId) {
        console.error('ERROR: itemCategoryId element not found!');
        return;
      }
      
      const optionsHtml = allCategories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
      console.log('Generated options HTML:', optionsHtml);
      
      itemCategoryId.innerHTML = '<option value="">Select category</option>' + optionsHtml;
      console.log('Dropdown populated, new innerHTML:', itemCategoryId.innerHTML);
    }

    function renderCategoryTabs() {
      const tabsHtml = allCategories.map(cat => `
        <button class="category-tab" data-category-id="${cat.id}" role="tab" aria-selected="false">
          ${escapeHtml(cat.name)}
        </button>
      `).join('');

      categoryTabs.innerHTML = `
        <button class="category-tab active" data-category-id="" role="tab" aria-selected="true">All Items</button>
        ${tabsHtml}
      `;
    }

    function initItemsTable() {
      itemsDataTable = new DataTable({
        endpoint: '/menu-items',
        container: 'menuItemsTable',
        columns: [
          { key: 'image', label: '', width: '60px', render: renderImage },
          { key: 'name', label: 'Name', render: renderName },
          { key: 'category_name', label: 'Category' },
          { key: 'price', label: 'Price', class: 'price-cell', render: renderPrice },
          { key: 'status', label: 'Status', render: renderStatusBadge }
        ],
        rowActions: [
          { id: 'edit', title: 'Edit', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>', handler: editItem },
          { id: 'delete', title: 'Delete', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>', danger: true, handler: deleteItem }
        ],
        toolbarActions: [
          { id: 'addCategory', label: 'Add Category', class: 'btn-secondary', onClick: () => openCategoryModal() },
          { id: 'addItem', label: 'Add Item', class: 'btn-primary', onClick: () => openItemModal() }
        ],
        filtersConfig: {
          status: {
            label: 'Status',
            type: 'select',
            options: [
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' }
            ]
          }
        },
        transformRow: (item) => ({
          ...item,
          category_name: item.category_name || item.category || '-'
        }),
        emptyMessage: 'No menu items found',
        loadingMessage: 'Loading menu items...',
        errorMessage: 'Failed to load menu items'
      });
    }

    function renderImage(value, row) {
      if (!value) {
        return '<div class="menu-table-image"></div>';
      }
      const url = value.startsWith('http') ? value : '/' + value.replace(/^\//, '');
      return `<img class="menu-table-image" src="${escapeHtml(url)}" alt="">`;
    }

    function renderName(value, row) {
      return `<strong>${escapeHtml(value)}</strong>`;
    }

    function renderPrice(value) {
      const num = Number(value || 0);
      return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'ETB', maximumFractionDigits: 2 }).format(num);
    }

    function renderStatusBadge(value) {
      const active = String(value || '').toLowerCase() === 'active';
      return `<span class="status-badge ${active ? 'badge-active' : 'badge-inactive'}"><span class="dot"></span>${active ? 'Active' : 'Inactive'}</span>`;
    }

    function bindEvents() {
      // Category tabs
      categoryTabs.addEventListener('click', (e) => {
        const tab = e.target.closest('.category-tab');
        if (!tab) return;
        const catId = tab.dataset.categoryId;
        categoryTabs.querySelectorAll('.category-tab').forEach(t => {
          t.classList.remove('active');
          t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        itemsDataTable.setFilters({ category_id: catId });
      });

      // Category modal
      closeCategoryBtn.addEventListener('click', closeCategoryModal);
      categoryModalCancel.addEventListener('click', closeCategoryModal);
      categoryModalSave.addEventListener('click', saveCategory);
      categoryModal.addEventListener('click', (e) => { if (e.target === categoryModal) closeCategoryModal(); });

      // Manage Categories modal
      manageCategoriesBtn.addEventListener('click', openManageCategoriesModal);
      manageCategoriesClose.addEventListener('click', closeManageCategoriesModal);
      manageCategoriesCancel.addEventListener('click', closeManageCategoriesModal);
      manageAddCategoryBtn.addEventListener('click', () => openCategoryModal());
      manageCategoriesModal.addEventListener('click', (e) => {
        if (e.target === manageCategoriesModal) closeManageCategoriesModal();
      });
      manageCategoriesTbody.addEventListener('click', (e) => {
        const editBtn = e.target.closest('[data-cat-edit]');
        const delBtn = e.target.closest('[data-cat-delete]');
        if (editBtn) {
          const id = editBtn.dataset.catEdit;
          const cat = allCategories.find(c => String(c.id) === String(id));
          if (cat) openCategoryModal(cat);
        }
        if (delBtn) {
          deleteCategory(delBtn.dataset.catDelete);
        }
      });

      // Item modal
      itemModalClose.addEventListener('click', closeItemModal);
      itemModalCancel.addEventListener('click', closeItemModal);
      itemModalSave.addEventListener('click', saveItem);
      itemModal.addEventListener('click', (e) => { if (e.target === itemModal) closeItemModal(); });

      // Image preview
      itemImage.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (ev) => {
            itemImagePreviewImg.src = ev.target.result;
            itemImagePreview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else {
          itemImagePreview.style.display = 'none';
        }
      });
    }

    // Category modal functions
    function openCategoryModal(category = null) {
      categoryForm.reset();
      categoryId.value = '';
      categoryName.value = '';
      categoryDescription.value = '';
      categoryStatus.value = 'active';

      if (category) {
        categoryModalTitle.textContent = 'Edit Category';
        categoryId.value = category.id;
        categoryName.value = category.name;
        categoryDescription.value = category.description || '';
        categoryStatus.value = category.status || 'active';
      } else {
        categoryModalTitle.textContent = 'Add Category';
      }

      categoryModal.hidden = false;
      document.body.style.overflow = 'hidden';
      categoryName.focus();
    }

    function closeCategoryModal() {
      categoryModal.hidden = true;
      document.body.style.overflow = '';
    }

    // Manage Categories modal functions
    async function openManageCategoriesModal() {
      manageCategoriesModal.hidden = false;
      document.body.style.overflow = 'hidden';
      await loadManageCategories();
    }

    function closeManageCategoriesModal() {
      manageCategoriesModal.hidden = true;
      document.body.style.overflow = '';
    }

    async function loadManageCategories() {
      manageCategoriesTbody.innerHTML = '<tr class="empty-row"><td colspan="4">Loading categories...</td></tr>';
      try {
        const data = await api.get('/menu-categories', { per_page: 100 });
        allCategories = data.data?.categories || data.data || [];
        renderManageCategories();
      } catch (error) {
        manageCategoriesTbody.innerHTML = '<tr class="empty-row"><td colspan="4">Failed to load categories</td></tr>';
        toast.error('Error', error.message);
      }
    }

    function renderManageCategories() {
      if (!allCategories.length) {
        manageCategoriesTbody.innerHTML = '<tr class="empty-row"><td colspan="4">No categories found</td></tr>';
        return;
      }
      manageCategoriesTbody.innerHTML = allCategories.map(cat => `
        <tr>
          <td><strong>${escapeHtml(cat.name)}</strong></td>
          <td>${escapeHtml(cat.description || '-')}</td>
          <td>${renderStatusBadge(cat.status)}</td>
          <td>
            <div class="row-actions">
              <button type="button" class="btn-icon" data-cat-edit="${cat.id}" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
              <button type="button" class="btn-icon btn-icon-danger" data-cat-delete="${cat.id}" title="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
            </div>
          </td>
        </tr>
      `).join('');
    }

    async function deleteCategory(id) {
      const confirmed = await confirmModal.open({
        title: 'Delete Category',
        message: 'Delete this category? Associated menu items may be affected.',
        confirmLabel: 'Delete category',
        cancelLabel: 'Keep category',
        variant: 'destructive'
      });

      if (!confirmed) return;

      try {
        await api.delete(`/menu-categories/${id}`);
        toast.success('Success', 'Category deleted');
        await loadCategories();
        await loadManageCategories();
        itemsDataTable.refresh();
      } catch (error) {
        toast.error('Error', error.message);
      }
    }

    function closeItemModal() {
      itemModal.hidden = true;
      document.body.style.overflow = '';
    }

    // Escape key handlers for modals
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (!categoryModal.hidden) closeCategoryModal();
        if (!itemModal.hidden) closeItemModal();
      }
    });

    async function saveCategory() {
      if (!categoryForm.checkValidity()) {
        categoryForm.reportValidity();
        return;
      }

      const id = categoryId.value;
      const data = {
        name: categoryName.value.trim(),
        description: categoryDescription.value.trim(),
        status: categoryStatus.value
      };

      try {
        if (id) {
          await api.put(`/menu-categories/${id}`, data);
          toast.success('Success', 'Category updated successfully!');
        } else {
          await api.post('/menu-categories', data);
          toast.success('Success', 'Category added successfully!');
        }
        closeCategoryModal();
        await loadCategories();
        itemsDataTable.refresh();
      } catch (error) {
        toast.error('Error', error.message);
      }
    }

    // Item modal functions
    function openItemModal(item = null) {
      itemForm.reset();
      itemId.value = '';
      itemName.value = '';
      itemCategoryId.value = '';
      itemPrice.value = '';
      itemDescription.value = '';
      itemStatus.value = 'active';
      itemImagePreview.style.display = 'none';

      // Ensure categories are loaded in dropdown - ALWAYS populate if empty
      console.log('openItemModal called, checking dropdown...');
      if (itemCategoryId.options.length <= 1) {
        console.log('Dropdown empty, populating from allCategories...');
        populateCategoryDropdown();
      }

      if (item) {
        itemModalTitle.textContent = 'Edit Menu Item';
        itemId.value = item.id;
        itemName.value = item.name;
        itemCategoryId.value = item.category_id || '';
        itemPrice.value = item.price;
        itemDescription.value = item.description || '';
        itemStatus.value = item.status || 'active';
        if (item.image) {
          const url = item.image.startsWith('http') ? item.image : '/' + item.image.replace(/^\//, '');
          itemImagePreviewImg.src = url;
          itemImagePreview.style.display = 'block';
        }
      } else {
        itemModalTitle.textContent = 'Add Menu Item';
      }

      itemModal.hidden = false;
      document.body.style.overflow = 'hidden';
      itemName.focus();
    }

    async function saveItem() {
      if (!itemForm.checkValidity()) {
        itemForm.reportValidity();
        return;
      }

      const id = itemId.value;
      const formData = new FormData();
      formData.append('name', itemName.value.trim());
      formData.append('category_id', itemCategoryId.value);
      formData.append('price', itemPrice.value);
      formData.append('description', itemDescription.value.trim());
      formData.append('status', itemStatus.value);
      if (itemImage.files[0]) formData.append('image', itemImage.files[0]);

      try {
        if (id) {
          await api.put(`/menu-items/${id}`, formData);
          toast.success('Success', 'Menu item updated successfully!');
        } else {
          await api.post('/menu-items', formData);
          toast.success('Success', 'Menu item added successfully!');
        }
        closeItemModal();
        itemsDataTable.refresh();
      } catch (error) {
        toast.error('Error', error.message);
      }
    }

    async function editItem(id) {
      try {
        const data = await api.get(`/menu-items/${id}`);
        const item = data.data || data;
        openItemModal(item);
      } catch (error) {
        toast.error('Error', error.message);
      }
    }

    async function deleteItem(id) {
      const confirmed = await confirmModal.open({
        title: 'Delete Menu Item',
        message: 'Delete this menu item? This cannot be undone.',
        confirmLabel: 'Delete item',
        cancelLabel: 'Keep item',
        variant: 'destructive'
      });

      if (!confirmed) return;

      try {
        await api.delete(`/menu-items/${id}`);
        toast.success('Success', 'Menu item deleted');
        itemsDataTable.refresh();
      } catch (error) {
        toast.error('Error', error.message);
      }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    init();
  })();
</script>
<?= $this->endSection() ?>