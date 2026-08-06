<div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle" hidden style="z-index: 11000; display: none;">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="confirmModalTitle">Confirm Action</h2>
      <button class="modal-close" id="confirmModalClose" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" id="confirmModalBody">
      <p id="confirmModalMessage">Are you sure you want to proceed?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" id="confirmModalCancel">Cancel</button>
      <button class="btn btn-rust" type="button" id="confirmModalConfirm">Confirm</button>
    </div>
  </div>
</div>

<script>
  (function () {
    const modal = document.getElementById('confirmModal');
    const closeBtn = document.getElementById('confirmModalClose');
    const cancelBtn = document.getElementById('confirmModalCancel');
    const confirmBtn = document.getElementById('confirmModalConfirm');
    const titleEl = document.getElementById('confirmModalTitle');
    const messageEl = document.getElementById('confirmModalMessage');
    const confirmLabelEl = document.getElementById('confirmModalConfirm');
    const cancelLabelEl = document.getElementById('confirmModalCancel');

    let resolvePromise = null;

    // Defensive: ensure modal is hidden on initialization
    modal.hidden = true;
    modal.style.display = 'none';
    document.body.style.overflow = '';

    function openModal(options) {
      return new Promise((resolve) => {
        resolvePromise = resolve;

        titleEl.textContent = options.title || 'Confirm Action';
        messageEl.textContent = options.message || 'Are you sure you want to proceed?';
        confirmLabelEl.textContent = options.confirmLabel || 'Confirm';
        cancelLabelEl.textContent = options.cancelLabel || 'Cancel';

        confirmBtn.className = 'btn ' + (options.variant === 'destructive' ? 'btn-rust' : options.variant === 'primary' ? 'btn-primary' : 'btn-teal');
        confirmBtn.focus();

        modal.hidden = false;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      });
    }

    function closeModal(result) {
      modal.hidden = true;
      modal.style.display = 'none';
      document.body.style.overflow = '';
      if (resolvePromise) {
        resolvePromise(result);
        resolvePromise = null;
      }
    }

    closeBtn.addEventListener('click', () => closeModal(false));
    cancelBtn.addEventListener('click', () => closeModal(false));
    confirmBtn.addEventListener('click', () => closeModal(true));

    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal(false);
    });

    document.addEventListener('keydown', (e) => {
      if (!modal.hidden && e.key === 'Escape') closeModal(false);
    });

    window.confirmModal = { open: openModal };
  })();
</script>