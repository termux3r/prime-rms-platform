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
  <div class="login-shell">
    <?= $this->renderSection('content') ?>
  </div>
  <div id="toastContainer" class="toast-container" aria-live="polite"></div>
  <?= $this->renderSection('scripts') ?>
</body>
</html>