<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dallol RMS') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;700;900&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
      <?= $this->include('partials/sidebar') ?>
    </aside>

    <main class="main-content" id="mainContent" role="main">
      <header class="topbar" role="banner">
        <?= $this->include('partials/topbar') ?>
      </header>

      <div class="content-area" id="contentArea">
        <?= $this->renderSection('content') ?>
      </div>
    </main>
  </div>

  <div id="toastContainer" class="toast-container" aria-live="polite"></div>
  <?= $this->include('partials/toast') ?>
  <?= $this->include('partials/modal_confirm') ?>
  <?= $this->renderSection('scripts') ?>
</body>
</html>