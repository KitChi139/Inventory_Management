<?php
session_start();
include 'db_connect.php';

$sql = "SELECT o.*, u.employee_id 
        FROM orders_requested o
        LEFT JOIN users u ON o.pharmacist_id = u.id
        WHERE o.status = 'Pending'
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hospital Pharmacy POS - Cashier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="header-section">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h5 class="fw-bold fs-3 mb-0">Hospital Pharmacy Name</h5>
      <small class="text-muted">Process Payment</small>
    </div>

    <div class="d-flex align-items-center gap-3">
      <div class="text-end">
        <div id="datetime" class="text-muted small"></div>
      </div>
      <div class="user-info d-flex align-items-center gap-2">
        <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
          <i class="bi bi-person-fill"></i>
        </div>
        <div>
          <div class="fw-semibold">
            <?= htmlspecialchars($_SESSION['employee_name'] ?? 'Unknown User') ?>
          </div>
          <small class="text-muted"><?= ucfirst($_SESSION['role'] ?? 'Unauthorized') ?></small>
        </div>
      </div>
      <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
  </div>
</div>

<div class="container-fluid p-3">
  <div class="row g-3">
    <!-- Orders from Pharmacists -->
    <div class="col-lg-7">
      <div class="d-flex mb-3 mt-1 toggle-container">
        <button id="salesToggle" class="btn btn-primary me-2" style="width: 15rem">Sales</button>
        <button id="returnsToggle" class="btn btn-outline-primary" style="width: 15rem">Returns</button>
      </div>

      <div class="card pending-orders-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <svg class="me-2" fill="none" width="25" height="25" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293
                    c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 
                    2 2 0 000-4zm-8 2a2 2 0 11-4 0 
                    2 2 0 014 0z"/>
            </svg>
            <h5 class="fw-bold mb-0">Pending Orders</h5>
          </div>
          <span class="badge bg-primary rounded-pill"><?= $result->num_rows ?? 0 ?></span>
        </div>

        <div class="table-responsive table-container">
          <table class="table align-middle table-hover table-borderless modern-table">
            <thead class="table-light border-bottom border-2">
              <tr>
                <th style="width: 17%;">Order #</th>
                <th style="width: 63%;">Items</th>
                <th style="width: 20%;" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                  $items = json_decode($row['order_data'], true);
                ?>
                  <tr class="border-bottom">
                    <td><span class="order-id">#<?= $row['id'] ?></span></td>
                    <td>
                      <div class="order-items">
                        <?php foreach ($items as $item): ?>
                          <div class="item-entry">
                            <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="item-qty">×<?= $item['qty'] ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </td>
                    <td class="text-center">
  <button class="btn btn-sm btn-primary load-order-btn me-1"
    data-id="<?= $row['id'] ?>"
    data-transaction-id="<?= htmlspecialchars($row['transaction_id'] ?? '') ?>"
    data-items='<?= htmlspecialchars(json_encode($items), ENT_QUOTES) ?>'>
    Load
  </button>
  <button class="btn btn-sm btn-danger delete-order-btn me-1"
    data-id="<?= $row['id'] ?>">
    Delete
  </button>
</td>

                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">
                    No pending orders available
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Payment Section -->
    <div class="col-lg-5">
      <div class="card p-4 shadow-sm border-0 payment-card mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="fw-bold d-flex align-items-center mb-0">
            <i class="bi bi-credit-card me-2 text-dark fs-5"></i> Process Payment
          </h5>
        </div>

        <div id="orderDetails" class="order-details-box text-muted text-center py-3">
          Select an order to process.
        </div>

        <div class="mt-4">
          <button id="completePaymentBtn" class="btn btn-primary w-100 py-2 fw-semibold">
            Complete Payment
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Receipt Popup -->
<div id="receiptPopup">
  <div id="receiptContent">
    <span class="closeBtn" onclick="closeReceipt()">×</span>
    <h3>Hospital Pharmacy</h3>
    <hr>
    <div id="receiptDetails"></div>
    <button id="printReceiptBtn" onclick="printReceipt()">Print Receipt</button>
  </div>
</div>

<script>
document.getElementById('salesToggle').addEventListener('click', () => window.location.href = 'cashier_dashboard.php');
document.getElementById('returnsToggle').addEventListener('click', () => window.location.href = 'cashier_returns.php');

let currentOrder = null;

document.querySelectorAll('.load-order-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const items = JSON.parse(btn.dataset.items);
    const transactionId = btn.dataset.transactionId || '—';
    currentOrder = { id: parseInt(btn.dataset.id), items, transactionId };

    // Ensure price exists
    items.forEach(i => { i.price = parseFloat(i.price || 0); });

    const subtotal = items.reduce((sum, i) => sum + (i.price * i.qty), 0);
    const vat = subtotal * 0.12;
    const vatableSales = subtotal - vat;
    const total = subtotal;

    const orderDiv = document.getElementById('orderDetails');
    orderDiv.innerHTML = `
      <h6 class="fw-bold mb-2">Order #${currentOrder.id}</h6>
      <div style="font-family: monospace; line-height: 1.6;">
        ${items.map(i => `
          <div class="d-flex justify-content-between">
            <span>${i.name} (${i.qty}x)</span>
            <span>₱${(i.price * i.qty).toFixed(2)}</span>
          </div>
        `).join('')}
      </div>
      <hr class="my-2">
      <div class="d-flex justify-content-between"><strong>Vatable Sales:</strong> <span>₱${vatableSales.toFixed(2)}</span></div>
      <div class="d-flex justify-content-between"><strong>VAT (12%):</strong> <span>₱${vat.toFixed(2)}</span></div>
      <div class="d-flex justify-content-between mb-2"><strong>Subtotal:</strong> <span>₱${subtotal.toFixed(2)}</span></div>
      <div class="mt-2">
        <label class="fw-bold">Discount:</label>
        <select id="discountPercent" class="form-control form-control-sm">
          <option value="0">None</option>
          <option value="20">Senior (20%)</option>
          <option value="30">PWD (30%)</option>
        </select>
      </div>
      <div class="mt-2">
        <label class="fw-bold">Tendered Amount:</label>
        <input type="number" id="tendered" class="form-control form-control-sm mb-2">
      </div>
      <div class="d-flex justify-content-between"><strong>Total:</strong><span id="totalDisplay">₱${subtotal.toFixed(2)}</span></div>
      <div class="d-flex justify-content-between"><strong>Change:</strong><span id="changeDisplay">₱0.00</span></div>
    `;

    document.getElementById('discountPercent').addEventListener('change', updateTotals);
    document.getElementById('tendered').addEventListener('input', updateChange);

    function updateTotals() {
      const discount = parseFloat(document.getElementById('discountPercent').value) || 0;
      const discountAmount = subtotal * (discount / 100);
      const discountedTotal = subtotal - discountAmount;
      document.getElementById('totalDisplay').textContent = `₱${discountedTotal.toFixed(2)}`;
      updateChange();
    }

    function updateChange() {
      const totalText = document.getElementById('totalDisplay').textContent.replace('₱', '').trim();
      const totalToPay = parseFloat(totalText) || 0;
      const tendered = parseFloat(document.getElementById('tendered').value) || 0;
      const change = Math.max(0, tendered - totalToPay);
      document.getElementById('changeDisplay').textContent = `₱${change.toFixed(2)}`;
    }

  });
});

// --- DELETE ORDER BUTTON ---
document.querySelectorAll('.delete-order-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const orderId = btn.dataset.id;

    if (!confirm(`Are you sure you want to delete Order #${orderId}?`)) return;

    fetch('delete_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(orderId)
    })
    .then(res => res.text())
    .then(text => {
      console.log('Delete Response:', text);
      try {
        const data = JSON.parse(text);
        if (data.success) {
          alert('✅ Order deleted successfully.');
          location.reload();
        } else {
          alert('❌ Failed: ' + (data.message || 'Unknown error.'));
        }
      } catch (e) {
        alert('❌ Invalid server response. Check console.');
        console.error(text);
      }
    })
    .catch(err => {
      console.error(err);
      alert('❌ Network or server error.');
    });
  });
});

document.getElementById('completePaymentBtn').addEventListener('click', () => {
  if (!currentOrder) return alert('Please load an order first.');

  const totalText = document.getElementById('totalDisplay').textContent.replace('₱', '').trim();
  const totalToPay = parseFloat(totalText) || 0;
  const tendered = parseFloat(document.getElementById('tendered').value) || 0;
  const change = tendered - totalToPay;
  if (tendered < totalToPay) return alert('Insufficient payment.');

  const subtotal = currentOrder.items.reduce((sum, i) => sum + (i.price * i.qty), 0);
  const vat_12 = subtotal * 0.12;
  const vatable_sales = subtotal - vat_12;
  const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
  const discount = subtotal * (discountPercent / 100);

  fetch('process_payment.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      order_id: currentOrder.id,
      total: totalToPay,
      tendered: tendered,
      change: change,
      vatable_sales: vatable_sales,
      vat_12: vat_12,
      discount: discount
    })
  })
  .then(res => res.text())
  .then(text => {
    console.log('Server Response:', text);
    return JSON.parse(text);
  })
  .then(data => {
    if (data.success) {
      showReceipt(currentOrder, totalToPay, tendered, change, data.transaction_id);
    } else {
      alert('❌ Failed: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error(err);
    alert('❌ Network or server error. Check console.');
  });
});

function showReceipt(order, total, tendered, change, txnId) {
  const now = new Date();
  const date = now.toLocaleString();
  const itemsHTML = order.items.map(i => `
    <div class="d-flex justify-content-between">
      <span>${i.name} (${i.qty}x)</span>
      <span>₱${(i.price * i.qty).toFixed(2)}</span>
    </div>
  `).join('');

  document.getElementById('receiptDetails').innerHTML = `
    <div style="font-family: monospace; line-height: 1.5;">
      <div class="d-flex justify-content-between"><strong>Date:</strong><span>${date}</span></div>
      <div class="d-flex justify-content-between"><strong>Transaction ID:</strong><span>${txnId}</span></div>
      <div class="d-flex justify-content-between"><strong>Order #:</strong><span>${order.id}</span></div>
      <hr style="margin: 6px 0;">${itemsHTML}
      <hr style="margin: 6px 0;">
      <div class="d-flex justify-content-between"><strong>Total:</strong><span>₱${total.toFixed(2)}</span></div>
      <div class="d-flex justify-content-between"><strong>Tendered:</strong><span>₱${tendered.toFixed(2)}</span></div>
      <div class="d-flex justify-content-between"><strong>Change:</strong><span>₱${change.toFixed(2)}</span></div>
      <hr style="margin: 6px 0;">
      <div class="d-flex justify-content-between"><strong>Cashier:</strong><span><?= htmlspecialchars($_SESSION['employee_name'] ?? 'Cashier') ?></span></div>
      <div class="text-center mt-2"><em>Thank you for your purchase!</em></div>
    </div>
  `;
  document.getElementById('receiptPopup').style.display = 'flex';
}

function closeReceipt() {
  document.getElementById('receiptPopup').style.display = 'none';
  location.reload();
}

function printReceipt() {
  const receiptContent = document.getElementById('receiptContent').innerHTML;
  const printWindow = window.open('', '', 'width=400,height=600');
  printWindow.document.write('<html><head><title>Receipt</title></head><body>');
  printWindow.document.write(receiptContent);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.print();
}

function updateDateTime() {
  const now = new Date();
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const formattedDate = now.toLocaleDateString('en-PH', options);
  const formattedTime = now.toLocaleTimeString('en-PH');
  document.getElementById('datetime').innerHTML = `${formattedDate} — ${formattedTime}`;
}
setInterval(updateDateTime, 1000);
updateDateTime();
</script>
</body>
</html>
