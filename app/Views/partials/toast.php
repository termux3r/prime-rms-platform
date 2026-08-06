<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="false"></div>

<script>
  (function () {
    const container = document.getElementById('toastContainer');

    function showToast(options) {
      const toast = document.createElement('div');
      toast.className = 'toast ' + (options.type || 'info');
      toast.role = 'alert';
      toast.ariaLive = 'assertive';

      const icons = {
        success: '<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent-teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        error: '<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent-rust)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        warning: '<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent-sulfur)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        info: '<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent-teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
      };

      toast.innerHTML = icons[options.type || 'info'] +
        '<div class="toast-content">' +
          '<div class="toast-title">' + escapeHtml(options.title || '') + '</div>' +
          (options.message ? '<div class="toast-message">' + escapeHtml(options.message) + '</div>' : '') +
        '</div>' +
        '<button class="toast-close" aria-label="Dismiss">&times;</button>';

      container.appendChild(toast);

      const closeBtn = toast.querySelector('.toast-close');
      closeBtn.addEventListener('click', () => removeToast(toast));

      const duration = options.duration || 3000;
      const timer = setTimeout(() => removeToast(toast), duration);

      toast.addEventListener('mouseenter', () => clearTimeout(timer));
      toast.addEventListener('mouseleave', () => setTimeout(() => removeToast(toast), 1000));

      return toast;
    }

    function removeToast(toast) {
      toast.style.animation = 'toast-out 0.2s ease forwards';
      toast.addEventListener('animationend', () => toast.remove());
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    window.toast = {
      success: (title, message, duration) => showToast({ type: 'success', title, message, duration }),
      error: (title, message, duration) => showToast({ type: 'error', title, message, duration }),
      warning: (title, message, duration) => showToast({ type: 'warning', title, message, duration }),
      info: (title, message, duration) => showToast({ type: 'info', title, message, duration })
    };
  })();
</script>

<style>
  @keyframes toast-out {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(20px); }
  }
</style>