<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dallol RMS — Order') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;700;900&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
  <style>
    body { margin: 0; background: var(--surface-base); }

    .kiosk-shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .kiosk-main {
      flex: 1;
      min-width: 0;
    }

    .kiosk-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 12px 24px;
      border-top: 1px solid var(--border-subtle);
      background: var(--surface-card);
      font-family: var(--font-mono);
      font-size: 12px;
      color: var(--ink-secondary);
    }

    .staff-login-link {
      color: var(--ink-secondary);
      text-decoration: none;
      transition: color .15s ease;
    }
    .staff-login-link:hover { color: var(--accent-sulfur-ink); }

    /* POS panes sized for the chrome-less kiosk screen */
    .pos-shell {
      min-height: calc(100vh - 49px);
    }
    .pos-cart-pane {
      top: 0;
      height: calc(100vh - 49px);
    }
  </style>
  <?= $this->renderSection('head') ?>
</head>
<body>
  <div class="kiosk-shell">
    <main class="kiosk-main" role="main">
      <?= $this->renderSection('content') ?>
    </main>

    <footer class="kiosk-footer" role="contentinfo">
      <span>Dallol Restaurant Management</span>
      <a class="staff-login-link" href="<?= base_url('login') ?>">Staff Login</a>
    </footer>
  </div>

  <div id="toastContainer" class="toast-container" aria-live="polite"></div>
  <?= $this->include('partials/toast') ?>
  <?= $this->include('partials/modal_confirm') ?>
  <?= $this->renderSection('scripts') ?>
</body>
</html>