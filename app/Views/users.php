<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .users-shell {
    padding: 20px;
  }

  .users-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .users-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .users-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
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
  .data-table .actions {
    display: flex;
    gap: 8px;
    white-space: nowrap;
  }

  .icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-strong);
    background: var(--surface-card);
    color: var(--ink-secondary);
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
  }
  .icon-btn:hover { background: var(--accent-sulfur-soft); border-color: var(--accent-sulfur-soft); color: var(--accent-sulfur-ink); }
  .icon-btn:active { transform: scale(0.98); }
  .icon-btn.danger:hover { background: var(--accent-rust-soft); border-color: var(--accent-rust-soft); color: var(--accent-rust-ink); }

  .badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-family: var(--font-mono);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.02em;
    white-space: nowrap;
  }
  .badge-admin {
    background: var(--accent-teal-soft);
    color: var(--accent-teal-ink);
  }
  .badge-cashier {
    background: var(--accent-sulfur-soft);
    color: var(--accent-sulfur-ink);
  }
  .badge-active {
    background: var(--accent-teal-soft);
    color: var(--accent-teal-ink);
  }
  .badge-inactive {
    background: #EDEAE1;
    color: var(--ink-secondary);
  }

  /* Add/Edit User Modal */
  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(23,19,14,0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 10000;
    animation: fade-in 0.2s ease;
  }
  @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

  .modal {
    background: var(--surface-card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-panel);
    max-width: 520px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    animation: modal-in 0.25s ease;
  }
  @keyframes modal-in { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }

  .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-subtle);
  }
  .modal-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; margin: 0; }
  .modal-close { background: none; border: none; color: var(--ink-secondary); cursor: pointer; padding: 8px; line-height: 1; }
  .modal-close:hover { color: var(--ink-primary); }

  .modal-body { padding: 24px; }

  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid var(--border-subtle);
    background: rgba(23,19,14,0.02);
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
  }

  .form-group { margin-bottom: 20px; }
  .form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--ink-primary);
  }
  .form-input,
  .form-select {
    width: 100%;
    padding: 12px 14px;
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink-primary);
    background: var(--surface-card);
    border: 1px solid var(--border-strong);
    border-radius: var(--radius-sm);
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .form-input:focus,
  .form-select:focus {
    border-color: var(--accent-sulfur);
    box-shadow: 0 0 0 3px var(--accent-sulfur-soft);
    outline: none;
  }
  .form-input::placeholder { color: var(--ink-faint); }

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
    .users-header { flex-direction: column; align-items: stretch; }
    .users-header-left { justify-content: space-between; }
    .users-header-right { justify-content: stretch; }
    .users-header-right .btn { flex: 1; }
    .data-table { font-size: 13px; }
    .data-table th, .data-table td { padding: 10px 12px; }
    .modal { margin: 10px; max-height: calc(100vh - 20px); }
  }

  @media print {
    /* Hide all navigation and UI chrome */
    .users-header,
    .users-header-right,
    .sidebar,
    .topbar,
    .sidebar-overlay,
    .app-shell > aside,
    .app-shell > main > header,
    .data-table .actions,
    #addUserBtn {
      display: none !important;
    }

    /* Reset layout for print */
    .main-content { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    .users-shell { padding: 0 !important; max-width: none !important; }

    /* Table - clean print version */
    .surface-card {
      box-shadow: none !important;
      border: 1px solid #ccc !important;
      border-radius: 0 !important;
      padding: 0 !important;
    }

    .data-table {
      font-size: 11px !important;
      width: 100% !important;
    }
    .data-table th,
    .data-table td {
      padding: 6px 8px !important;
      border-bottom: 1px dotted #999 !important;
    }
    .data-table th {
      background: #f0f0f0 !important;
      border-bottom: 2px solid #333 !important;
      font-size: 9px !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .data-table tbody tr:hover { background: transparent !important; }

    /* Badges - ensure colors print */
    .badge {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .badge-admin { background: #d1e8e4 !important; color: #1e4a43 !important; border: 1px solid #2b685f !important; }
    .badge-cashier { background: #f3e8c9 !important; color: #7a5c0c !important; border: 1px solid #b58a12 !important; }
    .badge-active { background: #dae9e5 !important; color: #1e4a43 !important; border: 1px solid #2b685f !important; }
    .badge-inactive { background: #edeae1 !important; color: #756f60 !important; border: 1px solid #cfc7b2 !important; }

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
<div class="users-shell">
  <header class="users-header">
    <div class="users-header-left">
      <div>
        <div class="eyebrow">Staff</div>
        <h1 class="page-title" style="margin: 0;">User Management</h1>
        <p class="page-subtitle" style="margin: 4px 0 0;">Manage staff accounts, roles, and access</p>
      </div>
    </div>
    <div class="users-header-right">
      <button class="btn btn-primary" type="button" id="addUserBtn">Add User</button>
    </div>
  </header>

  <!-- DataTable container -->
  <div class="surface-card" id="usersTable">
    <!-- DataTable rendered by JS -->
    <div class="empty-table" style="padding: 60px 20px;">
      Loading users...
    </div>
  </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal-overlay" id="userModal" role="dialog" aria-modal="true" aria-labelledby="userModalTitle" hidden>
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="userModalTitle">Add User</h2>
      <button class="modal-close" id="userModalClose" aria-label="Close">&times;</button>
    </div>
    <form id="userForm" class="modal-body">
      <input type="hidden" name="id" id="userId">
      <div class="form-group">
        <label class="form-label" for="userName">Full Name *</label>
        <input type="text" class="form-input" name="name" id="userName" required placeholder="e.g. Yonas Asfaw" maxlength="100">
      </div>
      <div class="form-group">
        <label class="form-label" for="userUsername">Username *</label>
        <input type="text" class="form-input" name="username" id="userUsername" required placeholder="e.g. yonas.cashier" maxlength="50" pattern="[a-zA-Z0-9._-]+">
      </div>
      <div class="form-group">
        <label class="form-label" for="userEmail">Email *</label>
        <input type="email" class="form-input" name="email" id="userEmail" required placeholder="e.g. yonas@restaurant.com" maxlength="100">
      </div>
      <div class="form-group" id="passwordGroup">
        <label class="form-label" for="userPassword">Password *</label>
        <input type="password" class="form-input" name="password" id="userPassword" required placeholder="Min 8 characters" minlength="8" autocomplete="new-password">
      </div>
      <div class="form-group">
        <label class="form-label" for="userRole">Role *</label>
        <select class="form-select" name="role" id="userRole" required>
          <option value="cashier">Cashier</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="userStatus">Status</label>
        <select class="form-select" name="status" id="userStatus">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </form>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="userModalCancel">Cancel</button>
      <button class="btn btn-primary" type="button" id="userModalSave" form="userForm">Save User</button>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal-overlay" id="resetPasswordModal" role="dialog" aria-modal="true" aria-labelledby="resetPasswordModalTitle" hidden>
  <div class="modal" style="max-width: 420px;">
    <div class="modal-header">
      <h2 class="modal-title" id="resetPasswordModalTitle">Reset Password</h2>
      <button class="modal-close" id="resetPasswordModalClose" aria-label="Close">&times;</button>
    </div>
    <form id="resetPasswordForm" class="modal-body">
      <input type="hidden" name="user_id" id="resetUserId">
      <p style="color: var(--ink-secondary); margin: 0 0 20px;">Enter a new password for <strong id="resetUserName"></strong>. This will immediately replace their current password.</p>
      <div class="form-group">
        <label class="form-label" for="resetPassword">New Password *</label>
        <input type="password" class="form-input" name="password" id="resetPassword" required placeholder="Min 8 characters" minlength="8" autocomplete="new-password">
      </div>
      <div class="form-group">
        <label class="form-label" for="resetPasswordConfirm">Confirm Password *</label>
        <input type="password" class="form-input" name="password_confirm" id="resetPasswordConfirm" required placeholder="Re-enter password" minlength="8" autocomplete="new-password">
      </div>
    </form>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="resetPasswordModalCancel">Cancel</button>
      <button class="btn btn-primary" type="button" id="resetPasswordModalSave" form="resetPasswordForm">Reset Password</button>
    </div>
  </div>
</div>

<!-- Status Toggle Confirmation uses shared confirmModal -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable.js') ?>"></script>
<script>
  (function () {
    // Initialize DataTable for users
    const usersTable = new DataTable({
      endpoint: '/users',
      container: 'usersTable',
      columns: [
        { key: 'name', label: 'Name' },
        { key: 'username', label: 'Username', render: (v) => `<code style="font-family: var(--font-mono); font-size: 13px;">${escapeHtml(v)}</code>` },
        { key: 'email', label: 'Email' },
        { key: 'role', label: 'Role', render: renderRoleBadge },
        { key: 'status', label: 'Status', render: renderStatusBadge }
      ],
      rowActions: [
        { id: 'edit', title: 'Edit', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>', handler: editUser },
        { id: 'toggle-status', title: 'Toggle Status', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>', handler: toggleUserStatus },
        { id: 'reset-password', title: 'Reset Password', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>', handler: resetUserPassword }
      ],
      toolbarActions: [
        { id: 'addUser', label: 'Add User', class: 'btn-primary', onClick: openAddUserModal }
      ],
      filtersConfig: {
        status: {
          label: 'Status',
          type: 'select',
          options: [
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' }
          ]
        },
        role: {
          label: 'Role',
          type: 'select',
          options: [
            { value: 'admin', label: 'Admin' },
            { value: 'cashier', label: 'Cashier' }
          ]
        }
      },
      emptyMessage: 'No users found',
      loadingMessage: 'Loading users...',
      errorMessage: 'Failed to load users'
    });

    // Helper functions
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function renderRoleBadge(value) {
      const role = String(value || '').toLowerCase();
      return `<span class="badge ${role === 'admin' ? 'badge-admin' : 'badge-cashier'}">${escapeHtml(role.charAt(0).toUpperCase() + role.slice(1))}</span>`;
    }

    function renderStatusBadge(value) {
      const status = String(value || '').toLowerCase();
      return `<span class="badge ${status === 'active' ? 'badge-active' : 'badge-inactive'}">${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}</span>`;
    }

    // Modal DOM elements
    const userModal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const userModalTitle = document.getElementById('userModalTitle');
    const userId = document.getElementById('userId');
    const userName = document.getElementById('userName');
    const userUsername = document.getElementById('userUsername');
    const userEmail = document.getElementById('userEmail');
    const userPassword = document.getElementById('userPassword');
    const userRole = document.getElementById('userRole');
    const userStatus = document.getElementById('userStatus');
    const passwordGroup = document.getElementById('passwordGroup');
    const userModalClose = document.getElementById('userModalClose');
    const userModalCancel = document.getElementById('userModalCancel');
    const userModalSave = document.getElementById('userModalSave');

    function closeUserModal() {
      userModal.hidden = true;
      document.body.style.overflow = '';
      userForm.reset();
    }

    function openAddUserModal() {
      userForm.reset();
      userId.value = '';
      userModalTitle.textContent = 'Add User';
      passwordGroup.style.display = 'block';
      userPassword.required = true;
      userModal.hidden = false;
      document.body.style.overflow = 'hidden';
      userName.focus();
    }

    async function editUser(id) {
      try {
        const response = await api.get(`/users/${id}`);
        const user = response.data || response;

        userId.value = user.id;
        userName.value = user.name;
        userUsername.value = user.username;
        userEmail.value = user.email;
        userRole.value = user.role;
        userStatus.value = user.status;
        userModalTitle.textContent = 'Edit User';
        passwordGroup.style.display = 'none';
        userPassword.required = false;

        userModal.hidden = false;
        document.body.style.overflow = 'hidden';
        userName.focus();
      } catch (error) {
        console.error('Failed to load user:', error);
        toast.error('Error', 'Failed to load user: ' + error.message);
      }
    }

    async function saveUser() {
      if (!userForm.checkValidity()) {
        userForm.reportValidity();
        return;
      }

      const id = userId.value;
      const data = {
        name: userName.value.trim(),
        username: userUsername.value.trim(),
        email: userEmail.value.trim(),
        role: userRole.value,
        status: userStatus.value
      };

      // Only include password for new users or if provided
      const password = userPassword.value;
      if (!id || password) {
        data.password = password;
      }

      try {
        userModalSave.disabled = true;
        userModalSave.textContent = 'Saving...';

        if (id) {
          await api.put(`/users/${id}`, data);
          toast.success('Success', 'User updated');
        } else {
          await api.post('/users', data);
          toast.success('Success', 'User created');
        }
        closeUserModal();
        usersTable.refresh();
      } catch (error) {
        console.error('Failed to save user:', error);
        toast.error('Error', error.message);
      } finally {
        userModalSave.disabled = false;
        userModalSave.textContent = 'Save User';
      }
    }

    // Event listeners for user modal
    userModalClose.addEventListener('click', closeUserModal);
    userModalCancel.addEventListener('click', closeUserModal);
    userModalSave.addEventListener('click', saveUser);
    userModal.addEventListener('click', (e) => { if (e.target === userModal) closeUserModal(); });

    // Escape key to close modal
    document.addEventListener('keydown', (e) => {
      if (!userModal.hidden && e.key === 'Escape') closeUserModal();
    });

    // Toggle user status (active/inactive)
    async function toggleUserStatus(id) {
      try {
        // Get current user to determine new status
        const response = await api.get(`/users/${id}`);
        const user = response.data || response;
        const newStatus = user.status === 'active' ? 'inactive' : 'active';

        const confirmed = await confirmModal.open({
          title: `${newStatus === 'active' ? 'Activate' : 'Deactivate'} User`,
          message: `Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} "${escapeHtml(user.name)}"?`,
          confirmLabel: newStatus === 'active' ? 'Activate' : 'Deactivate',
          cancelLabel: 'Cancel',
          variant: newStatus === 'inactive' ? 'destructive' : 'primary'
        });

        if (!confirmed) return;

        await api.patch(`/users/${id}/status`, { status: newStatus });
        toast.success('Success', `User ${newStatus === 'active' ? 'activated' : 'deactivated'}`);
        usersTable.refresh();
      } catch (error) {
        console.error('Failed to toggle user status:', error);
        toast.error('Error', error.message);
      }
    }

    // Reset Password Modal DOM elements
    const resetPasswordModal = document.getElementById('resetPasswordModal');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const resetUserId = document.getElementById('resetUserId');
    const resetUserName = document.getElementById('resetUserName');
    const resetPassword = document.getElementById('resetPassword');
    const resetPasswordConfirm = document.getElementById('resetPasswordConfirm');
    const resetPasswordModalClose = document.getElementById('resetPasswordModalClose');
    const resetPasswordModalCancel = document.getElementById('resetPasswordModalCancel');
    const resetPasswordModalSave = document.getElementById('resetPasswordModalSave');

    function closeResetPasswordModal() {
      resetPasswordModal.hidden = true;
      document.body.style.overflow = '';
      resetPasswordForm.reset();
    }

    async function resetUserPassword(id) {
      try {
        // Get user details first
        const response = await api.get(`/users/${id}`);
        const user = response.data || response;

        resetUserId.value = user.id;
        resetUserName.textContent = user.name;
        resetPassword.value = '';
        resetPasswordConfirm.value = '';

        resetPasswordModal.hidden = false;
        document.body.style.overflow = 'hidden';
        resetPassword.focus();
      } catch (error) {
        console.error('Failed to load user:', error);
        toast.error('Error', 'Failed to load user: ' + error.message);
      }
    }

    async function saveResetPassword() {
      if (!resetPasswordForm.checkValidity()) {
        resetPasswordForm.reportValidity();
        return;
      }

      if (resetPassword.value !== resetPasswordConfirm.value) {
        toast.error('Error', 'Passwords do not match');
        return;
      }

      const userId = resetUserId.value;
      const password = resetPassword.value;

      try {
        resetPasswordModalSave.disabled = true;
        resetPasswordModalSave.textContent = 'Resetting...';

        await api.post(`/users/${userId}/reset-password`, { password });
        toast.success('Success', 'Password has been reset');
        closeResetPasswordModal();
      } catch (error) {
        console.error('Failed to reset password:', error);
        toast.error('Error', error.message);
      } finally {
        resetPasswordModalSave.disabled = false;
        resetPasswordModalSave.textContent = 'Reset Password';
      }
    }

    // Event listeners for reset password modal
    resetPasswordModalClose.addEventListener('click', closeResetPasswordModal);
    resetPasswordModalCancel.addEventListener('click', closeResetPasswordModal);
    resetPasswordModalSave.addEventListener('click', saveResetPassword);
    resetPasswordModal.addEventListener('click', (e) => { if (e.target === resetPasswordModal) closeResetPasswordModal(); });

    // Escape key to close modals
    document.addEventListener('keydown', (e) => {
      if (!userModal.hidden && e.key === 'Escape') closeUserModal();
      if (!resetPasswordModal.hidden && e.key === 'Escape') closeResetPasswordModal();
    });

    // Expose for global access from inline handlers
    window.usersTable = usersTable;
    window.openAddUserModal = openAddUserModal;
    window.editUser = editUser;
    window.toggleUserStatus = toggleUserStatus;
    window.resetUserPassword = resetUserPassword;
  })();
</script>
<?= $this->endSection() ?>