<div class="sidebar-inner">
  <div class="sidebar-brand">
    <div class="brand-mark">DALL<span>O</span>L</div>
    <div class="sidebar-tagline">Restaurant Management System</div>
  </div>

  <nav class="sidebar-nav" aria-label="Primary">
    <a class="nav-link <?= (current_url(true)->getSegment(1) === 'dashboard' || current_url(true)->getSegment(1) === '') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
      <span>Dashboard</span>
      <small>Summary</small>
    </a>
    <a class="nav-link <?= current_url(true)->getSegment(1) === 'order' ? 'active' : '' ?>" href="<?= base_url('order') ?>">
      <span>Orders</span>
      <small>POS</small>
    </a>
    <a class="nav-link <?= current_url(true)->getSegment(1) === 'menu' ? 'active' : '' ?>" href="<?= base_url('menu') ?>">
      <span>Menu</span>
      <small>Catalog</small>
    </a>
    <a class="nav-link <?= current_url(true)->getSegment(1) === 'tables' ? 'active' : '' ?>" href="<?= base_url('tables') ?>">
      <span>Tables</span>
      <small>Floor</small>
    </a>
    <a class="nav-link <?= current_url(true)->getSegment(1) === 'kitchen' ? 'active' : '' ?>" href="<?= base_url('kitchen') ?>">
      <span>Kitchen</span>
      <small>KDS</small>
    </a>
    <a class="nav-link <?= current_url(true)->getSegment(1) === 'reports' ? 'active' : '' ?>" href="<?= base_url('reports') ?>">
      <span>Reports</span>
      <small>Sales</small>
    </a>
    <a class="nav-link <?= current_url(true)->getSegment(1) === 'users' ? 'active' : '' ?>" href="<?= base_url('users') ?>">
      <span>Staff</span>
      <small>Users</small>
    </a>
  </nav>

  <div class="sidebar-foot">
    <div class="fw-semibold" style="color: var(--ink-inverse);">Dallol Tech</div>
    <div style="color: var(--ink-inverse-muted);">Internal system</div>
  </div>
</div>

<style>
  .sidebar {
    width: 248px;
    background: var(--surface-sidebar);
    color: var(--ink-inverse);
    box-shadow: 12px 0 30px rgba(23,19,14,.14);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 100;
    overflow-y: auto;
  }

  .sidebar-inner {
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    height: 100%;
    gap: 24px;
  }

  .sidebar::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at 22% 18%, rgba(181,138,18,0.30), transparent 38%),
      radial-gradient(circle at 80% 14%, rgba(43,104,95,0.28), transparent 34%),
      radial-gradient(circle at 58% 82%, rgba(168,69,29,0.22), transparent 38%);
    filter: blur(2px);
    pointer-events: none;
  }
  .sidebar > * { position: relative; z-index: 1; }

  .brand-mark {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 22px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }
  .brand-mark span { color: var(--accent-sulfur); }

  .sidebar-tagline {
    font-size: 12px;
    color: var(--ink-inverse-muted);
    margin-top: 4px;
    font-family: var(--font-mono);
    letter-spacing: 0.02em;
    text-transform: uppercase;
  }

  .sidebar-nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }

  .nav-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-radius: var(--radius-sm);
    color: var(--ink-inverse-muted);
    font-size: 14px;
    transition: background .15s ease, color .15s ease;
    text-decoration: none;
  }
  .nav-link:hover,
  .nav-link.active {
    background: var(--surface-sidebar-active);
    color: var(--ink-inverse);
  }
  .nav-link small {
    font-family: var(--font-mono);
    font-size: 11px;
    opacity: 0.7;
  }

  .sidebar-foot {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid rgba(242,239,231,0.08);
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--ink-inverse-muted);
    line-height: 1.6;
  }

  .main-content {
    margin-left: 248px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .topbar {
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: blur(18px);
    background: rgba(242, 239, 231, 0.86);
    border-bottom: 1px solid var(--border-subtle);
    padding: 16px 24px;
  }

  .content-area {
    flex: 1;
    padding: 24px;
    min-width: 0;
  }

  @media (max-width: 991.98px) {
    .sidebar { transform: translateX(-100%); transition: transform 0.25s ease; }
    .sidebar.open { transform: translateX(0); }
    .main-content { margin-left: 0; }
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(23,19,14,0.5); z-index: 99; }
    .sidebar-overlay.open { display: block; }
  }

  @media (max-width: 640px) {
    .content-area { padding: 16px; }
  }
</style>