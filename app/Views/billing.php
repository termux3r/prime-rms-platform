<?= $this->extend('layouts/app') ?>

<?= $this->section('head') ?>
<style>
  .billing-shell {
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
  }

  .billing-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .receipt-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    padding: 32px;
  }

  .receipt-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-subtle);
  }

  .receipt-brand {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 24px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--ink-primary);
    margin-bottom: 4px;
  }
  .receipt-brand span { color: var(--accent-sulfur); }

  .receipt-title {
    font-family: var(--font-mono);
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--ink-secondary);
    margin-bottom: 8px;
  }

  .receipt-meta {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--ink-secondary);
  }

  .receipt-items {
    margin-bottom: 20px;
  }

  .receipt-line {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0;
    font-family: var(--font-mono);
    font-size: 14px;
    border-bottom: 1px solid var(--border-subtle);
  }

  .receipt-line:last-child { border-bottom: none; }

  .receipt-line .item-info { display: flex; flex-direction: column; gap: 2px; }
  .receipt-line .item-name { font-weight: 500; color: var(--ink-primary); }
  .receipt-line .item-qty { font-size: 12px; color: var(--ink-secondary); }
  .receipt-line .item-price { text-align: right; white-space: nowrap; }

  .receipt-divider {
    border: none;
    border-top: 1px dashed var(--border-strong);
    margin: 16px 0;
  }

  .receipt-totals {
    font-family: var(--font-mono);
  }

  .total-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
  }

  .total-row.subtotal { color: var(--ink-secondary); }
  .total-row.tax { color: var(--ink-secondary); }
  .total-row.total {
    font-family: var(--font-display);
    font-size: 24px;
    font-weight: 700;
    color: var(--ink-primary);
    border-top: 1px dashed var(--border-strong);
    margin-top: 8px;
    padding-top: 16px;
  }

  .receipt-payment-info {
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--border-subtle);
  }

  .payment-method {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--ink-secondary);
  }

  .payment-method-label { font-weight: 600; color: var(--ink-primary); }

  .billing-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--border-subtle);
  }

  .btn-print {
    background: var(--ink-primary);
    color: var(--ink-inverse);
  }
  .btn-print:hover { background: #2c2820; }

  .payment-option:hover {
    border-color: var(--accent-sulfur) !important;
    background: var(--accent-sulfur-soft) !important;
  }
  .payment-option.selected {
    border-color: var(--accent-sulfur) !important;
    background: var(--accent-sulfur-soft) !important;
  }
  .payment-option.selected .payment-option-check {
    opacity: 1 !important;
  }
  .payment-option input[type="radio"] {
    cursor: pointer;
  }

  .loading-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--ink-secondary);
  }

  .error-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--accent-rust);
  }

  @media print {
    /* Hide all navigation and UI chrome */
    .billing-header,
    .billing-actions,
    .payment-selection,
    .sidebar,
    .topbar,
    .sidebar-overlay,
    .app-shell > aside,
    .app-shell > main > header {
      display: none !important;
    }
    
    /* Reset layout for print */
    .main-content { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    .billing-shell { padding: 0 !important; max-width: none !important; }
    
    /* Receipt card becomes the full page */
    .receipt-card {
      box-shadow: none !important;
      border: none !important;
      border-radius: 0 !important;
      padding: 0 !important;
      max-width: 100% !important;
      background: white !important;
    }
    
    /* Compact header */
    .receipt-header { 
      padding-bottom: 12px !important; 
      margin-bottom: 16px !important; 
      border-bottom: 1px solid #000 !important;
    }
    .receipt-brand { font-size: 18px !important; }
    .receipt-title { font-size: 11px !important; }
    .receipt-meta { 
      font-size: 11px !important; 
      gap: 16px !important;
      flex-wrap: nowrap !important;
      justify-content: space-between !important;
    }
    .receipt-meta span { flex: 1; text-align: center; }
    
    /* Compact items */
    .receipt-items { margin-bottom: 12px !important; }
    .receipt-line { 
      padding: 4px 0 !important; 
      font-size: 12px !important; 
      border-bottom: 1px dotted #999 !important;
    }
    .receipt-line:last-child { border-bottom: none !important; }
    .receipt-line .item-qty { font-size: 10px !important; }
    
    /* Divider */
    .receipt-divider { 
      margin: 8px 0 !important; 
      border-top: 1px dashed #333 !important;
    }
    
    /* Totals */
    .total-row { padding: 4px 0 !important; font-size: 12px !important; }
    .total-row.total { 
      font-size: 16px !important; 
      padding-top: 8px !important; 
      border-top: 1px dashed #333 !important;
    }
    
    /* Payment info (only show if paid) */
    .receipt-payment-info {
      margin-top: 16px !important;
      padding-top: 12px !important;
      border-top: 1px solid #000 !important;
    }
    .payment-method { 
      padding: 4px 0 !important; 
      font-size: 11px !important; 
    }
    
    /* Force black text on white for printing */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    body { background: white !important; color: black !important; }
    
    /* Page size for receipt printers (80mm/58mm thermal) */
    @page {
      margin: 0 !important;
      size: auto !important;
    }
  }

  @media (max-width: 640px) {
    .billing-shell { padding: 16px; }
    .receipt-card { padding: 20px; }
    .receipt-meta { flex-direction: column; gap: 4px; align-items: center; }
    .billing-actions { flex-direction: column; }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="billing-shell">
  <header class="billing-header">
    <div>
      <div class="eyebrow">Billing</div>
      <h1 class="page-title" style="margin: 0;">Receipt</h1>
      <p class="page-subtitle" style="margin: 4px 0 0;">View and print order receipt</p>
    </div>
  </header>

  <div id="billingContent">
    <!-- Loading state -->
    <div class="loading-state" id="loadingState">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--accent-sulfur)" stroke-width="2" style="margin-bottom: 16px; animation: spin 1s linear infinite;" aria-hidden="true">
        <circle cx="12" cy="12" r="10" stroke-dasharray="31.4 31.4" stroke-dashoffset="0"></circle>
      </svg>
      <p>Loading receipt...</p>
    </div>

    <!-- Error state -->
    <div class="error-state" id="errorState" hidden>
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px;" aria-hidden="true">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
      <p id="errorMessage">Failed to load receipt</p>
      <button class="btn btn-primary" type="button" id="retryBtn" style="margin-top: 16px;">Retry</button>
    </div>

    <!-- Receipt content -->
    <article class="receipt-card" id="receiptCard" hidden>
      <header class="receipt-header">
        <div class="receipt-brand">DALL<span>O</span>L</div>
        <div class="receipt-title">Restaurant Management System</div>
        <div class="receipt-meta">
          <span>Order <strong id="receiptOrderId">#--</strong></span>
          <span>Table <strong id="receiptTableNumber">--</strong></span>
          <span id="receiptDate">--</span>
        </div>
      </header>

      <div class="receipt-items" id="receiptItems">
        <!-- Lines rendered by JS -->
      </div>

      <hr class="receipt-divider">

      <div class="receipt-totals" id="receiptTotals">
        <div class="total-row subtotal">
          <span>Subtotal</span>
          <span id="receiptSubtotal">ETB 0.00</span>
        </div>
        <div class="total-row tax">
          <span>Tax (0%)</span>
          <span id="receiptTax">ETB 0.00</span>
        </div>
        <div class="total-row total">
          <span>Total</span>
          <span id="receiptTotal">ETB 0.00</span>
        </div>
      </div>

      <div class="receipt-payment-info" id="paymentInfo" hidden>
        <div class="payment-method">
          <span class="payment-method-label">Payment Method:</span>
          <span id="receiptPaymentMethod">--</span>
        </div>
        <div class="payment-method">
          <span class="payment-method-label">Paid At:</span>
          <span id="receiptPaidAt">--</span>
        </div>
      </div>

      <!-- Payment selection for unpaid bills -->
      <div class="payment-selection" id="paymentSelection" hidden>
        <h3 style="font-family: var(--font-display); font-size: 16px; font-weight: 700; margin: 0 0 16px; color: var(--ink-primary);">Record Payment</h3>
        <div class="payment-options" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
          <label class="payment-option" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid var(--border-strong); border-radius: var(--radius-md); cursor: pointer; transition: border-color 0.15s ease, background 0.15s ease;">
            <input type="radio" name="paymentMethod" value="cash" class="payment-radio" style="width: 20px; height: 20px; accent-color: var(--accent-sulfur);">
            <div style="flex: 1;">
              <div style="font-weight: 600; color: var(--ink-primary);">Cash</div>
              <div style="font-size: 12px; color: var(--ink-secondary); font-family: var(--font-mono);">Physical currency</div>
            </div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-sulfur)" stroke-width="2" style="opacity: 0; transition: opacity 0.15s ease;" class="payment-option-check">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </label>
          <label class="payment-option" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid var(--border-strong); border-radius: var(--radius-md); cursor: pointer; transition: border-color 0.15s ease, background 0.15s ease;">
            <input type="radio" name="paymentMethod" value="card" class="payment-radio" style="width: 20px; height: 20px; accent-color: var(--accent-sulfur);">
            <div style="flex: 1;">
              <div style="font-weight: 600; color: var(--ink-primary);">Card</div>
              <div style="font-size: 12px; color: var(--ink-secondary); font-family: var(--font-mono);">Credit/Debit card</div>
            </div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-sulfur)" stroke-width="2" style="opacity: 0; transition: opacity 0.15s ease;" class="payment-option-check">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </label>
          <label class="payment-option" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid var(--border-strong); border-radius: var(--radius-md); cursor: pointer; transition: border-color 0.15s ease, background 0.15s ease;">
            <input type="radio" name="paymentMethod" value="mobile" class="payment-radio" style="width: 20px; height: 20px; accent-color: var(--accent-sulfur);">
            <div style="flex: 1;">
              <div style="font-weight: 600; color: var(--ink-primary);">Mobile</div>
              <div style="font-size: 12px; color: var(--ink-secondary); font-family: var(--font-mono);">Mobile money / Digital wallet</div>
            </div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-sulfur)" stroke-width="2" style="opacity: 0; transition: opacity 0.15s ease;" class="payment-option-check">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </label>
        </div>
        <button class="btn btn-primary" type="button" id="payBtn" style="width: 100%; padding: 16px; font-size: 15px; font-weight: 700;" disabled>
          Mark as Paid
        </button>
      </div>

      <div class="billing-actions">
        <button class="btn btn-print" type="button" id="printBtn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
          </svg>
          Print Receipt
        </button>
      </div>
    </article>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/api.js') ?>"></script>
<script>
  (function () {
    // Get bill ID from URL query param: /billing?bill=123 or /billing?order=456
    const urlParams = new URLSearchParams(window.location.search);
    const billId = urlParams.get('bill');
    const orderId = urlParams.get('order');

    // DOM Elements
    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const errorMessage = document.getElementById('errorMessage');
    const retryBtn = document.getElementById('retryBtn');
    const receiptCard = document.getElementById('receiptCard');
    const receiptItems = document.getElementById('receiptItems');
    const receiptOrderId = document.getElementById('receiptOrderId');
    const receiptTableNumber = document.getElementById('receiptTableNumber');
    const receiptDate = document.getElementById('receiptDate');
    const receiptSubtotal = document.getElementById('receiptSubtotal');
    const receiptTax = document.getElementById('receiptTax');
    const receiptTotal = document.getElementById('receiptTotal');
    const paymentInfo = document.getElementById('paymentInfo');
    const receiptPaymentMethod = document.getElementById('receiptPaymentMethod');
    const receiptPaidAt = document.getElementById('receiptPaidAt');
    const paymentSelection = document.getElementById('paymentSelection');
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentRadios = document.querySelectorAll('.payment-radio');
    const payBtn = document.getElementById('payBtn');
    const printBtn = document.getElementById('printBtn');

    let currentBill = null;
    let selectedPaymentMethod = null;

    function formatMoney(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ETB',
        maximumFractionDigits: 2
      }).format(Number(value || 0));
    }

    function formatDateTime(isoString) {
      if (!isoString) return '--';
      const date = new Date(isoString);
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function showLoading() {
      loadingState.hidden = false;
      errorState.hidden = true;
      receiptCard.hidden = true;
    }

    function showError(message) {
      loadingState.hidden = true;
      errorState.hidden = false;
      errorMessage.textContent = message;
      receiptCard.hidden = true;
    }

    function showReceipt() {
      loadingState.hidden = true;
      errorState.hidden = true;
      receiptCard.hidden = false;
    }

    function renderReceipt(data) {
      const bill = data.bill;
      const order = data.order;
      const items = data.items || [];

      receiptOrderId.textContent = '#' + order.id;
      receiptTableNumber.textContent = order.table_id || '--';
      receiptDate.textContent = formatDateTime(bill.created_at);

      // Render items
      receiptItems.innerHTML = items.map(item => {
        const qty = Number(item.quantity);
        const price = Number(item.unit_price);
        const subtotal = qty * price;
        return `
          <div class="receipt-line">
            <div class="item-info">
              <span class="item-name">${escapeHtml(item.menu_item_name || item.name || 'Item')}</span>
              <span class="item-qty">${qty} × ${formatMoney(price)}</span>
            </div>
            <span class="item-price">${formatMoney(subtotal)}</span>
          </div>
        `;
      }).join('');

      // Totals
      const totalAmount = Number(bill.total_amount);
      receiptSubtotal.textContent = formatMoney(totalAmount);
      receiptTax.textContent = formatMoney(0); // No tax for now
      receiptTotal.textContent = formatMoney(totalAmount);

      // Payment info
      if (bill.payment_status === 'paid') {
        paymentInfo.hidden = false;
        paymentSelection.hidden = true;
        receiptPaymentMethod.textContent = bill.payment_method ? bill.payment_method.charAt(0).toUpperCase() + bill.payment_method.slice(1) : '--';
        receiptPaidAt.textContent = formatDateTime(bill.paid_at);
      } else {
        paymentInfo.hidden = true;
        paymentSelection.hidden = false;
      }

      showReceipt();
    }

    async function loadReceipt() {
      showLoading();

      try {
        let billData;

        if (billId) {
          // Load by bill ID
          billData = await api.get(`/bills/${billId}`);
        } else if (orderId) {
          // Load by order ID - need to find bill for this order
          // First try to get the bill via receipt endpoint which might work
          billData = await api.get(`/bills/receipt?order_id=${orderId}`).catch(() => null);
          
          if (!billData) {
            // Try to find bill through orders endpoint or generate one
            const orderData = await api.get(`/orders/${orderId}`);
            const order = orderData.data || orderData;
            
            if (order.status === 'completed') {
              // Try to generate bill
              const generateData = await api.post(`/orders/${orderId}/bill`);
              billData = generateData;
            } else {
              throw new Error('Order is not completed. Cannot generate receipt.');
            }
          }
        } else {
          throw new Error('No bill or order ID specified');
        }

        const bill = billData.data || billData;
        
        // Now get full receipt data
        const receiptData = await api.get(`/bills/${bill.id}/receipt`);
        currentBill = receiptData.data || receiptData;
        renderReceipt(currentBill);

      } catch (error) {
        console.error('Failed to load receipt:', error);
        showError(error.message || 'Failed to load receipt');
      }
    }

    // Payment option selection
    paymentOptions.forEach(option => {
      option.addEventListener('click', () => {
        const radio = option.querySelector('.payment-radio');
        paymentRadios.forEach(r => r.checked = false);
        radio.checked = true;
        
        paymentOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        
        selectedPaymentMethod = radio.value;
        payBtn.disabled = false;
      });
    });

    // Also handle radio change directly
    paymentRadios.forEach(radio => {
      radio.addEventListener('change', () => {
        paymentOptions.forEach(o => o.classList.remove('selected'));
        radio.closest('.payment-option').classList.add('selected');
        selectedPaymentMethod = radio.value;
        payBtn.disabled = false;
      });
    });

    // Pay button handler
    payBtn.addEventListener('click', async () => {
      if (!selectedPaymentMethod || !currentBill) return;

      payBtn.disabled = true;
      payBtn.textContent = 'Processing...';

      try {
        const result = await api.post(`/bills/${currentBill.bill.id}/pay`, {
          payment_method: selectedPaymentMethod
        });

        const bill = result.data || result;
        
        toast.success('Payment Recorded', `Payment of ${formatMoney(bill.total_amount)} via ${selectedPaymentMethod} recorded`);

        // Update UI to show paid state
        currentBill.bill.payment_status = 'paid';
        currentBill.bill.payment_method = selectedPaymentMethod;
        currentBill.bill.paid_at = new Date().toISOString();
        
        renderReceipt(currentBill);
        
        // Redirect after short delay
        setTimeout(() => {
          window.location.href = '/dashboard';
        }, 2000);

      } catch (error) {
        console.error('Payment failed:', error);
        toast.error('Payment Failed', error.message);
        payBtn.disabled = false;
        payBtn.textContent = 'Mark as Paid';
      }
    });

    // Print button
    printBtn.addEventListener('click', () => {
      window.print();
    });

    // Retry button
    retryBtn.addEventListener('click', loadReceipt);

    // Initialize
    loadReceipt();
  })();
</script>
<?= $this->endSection() ?>