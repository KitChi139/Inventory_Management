<?php
session_start();
include 'db_connect.php';

$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Hospital Pharmacy POS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
          <small class="text-muted">
            <?php
              if (isset($_SESSION['role'])) {
                  // Capitalize first letter for display
                  echo ucfirst($_SESSION['role']);
              } else {
                  echo 'Unauthorized';
              }
            ?>
          </small>
        </div>
      </div>
      <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
  </div>
</div>


<div class="container-fluid p-3">
<div class="row g-3">
<!-- Product Selection -->
<div class="col-lg-7">
  <div class="card p-3 mt-5">
    <div class="section-header">
      <svg class="icon"  width="25" height="25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
      </svg>
      Product Selection
    </div>
    
    <div class="d-flex gap-2 mb-3">
      <div class="input-group">
        <span class="input-group-text">
          <svg class="icon"  width="25" height="25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </span>
        <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
      </div>
      <select id="categoryFilter" class="form-select" style="max-width: 200px;">
        <option value="">All Categories</option>
        <option value="Analgesic">Analgesic</option>
        <option value="Antibiotic">Antibiotic</option>
      </select>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover product-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Brand Name</th>
            <th>Generic Name</th>
            <th>Dosage</th>
            <th>Price</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          <?php
          $products = [];
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  $products[] = $row;
                  echo "<tr data-category='" . htmlspecialchars($row['category']) . "'>";
                  echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['brand_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['generic_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['dosage']) . "</td>";
                  echo "<td>₱" . number_format($row['price'], 2) . "</td>";
                  echo "<td><button class='btn-add add-to-cart' 
                          data-id='" . $row['id'] . "'
                          data-name='" . htmlspecialchars($row['brand_name']) . "' 
                          data-dosage='" . htmlspecialchars($row['dosage']) . "'
                          data-price='" . $row['price'] . "'>+</button></td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<div class="col-lg-5">
<div class="d-flex justify-content-end mb-2 me-2">
  <button class="btn btn-outline-primary btn-sm px-2" id="btnReturnRequests" style="width: 150px;">
    <i class="bi bi-arrow-counterclockwise me-2"></i>Return Requests
  </button>
</div>

  <div class="card p-3 mb-3">
    <div class="section-header">
      <svg class="icon" fill="none" width="25" height="25" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      <span>Order Items (<span id="cartCount">0</span>)</span>
    </div>
    
    <div id="cartItems" style="min-height: 200px; max-height: 400px; overflow-y: auto;">
      <p class="text-muted text-center">Cart is empty</p>
    </div>
    
    <div class="mt-3">
      <div class="billing-row fw-bold">
        <span>Subtotal</span>
        <span id="subtotalDisplay">₱0.00</span>
      </div>
    </div>
  </div>
    <button class="btn-checkout m-0" id="checkoutBtn">
      <svg class="icon me-2" fill="none" width="25" height="25" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Send to Cashier
    </button>
  </div>
</div>
</div>
</div>



<!-- Return Requests Modal -->
<div class="modal fade" id="returnRequestsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Return & Refund Requests</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">
          Review return requests from Cashier. Verify if items are returnable by law and approve/reject accordingly.
        </p>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-primary">
              <tr>
                <th>Customer Name</th>
                <th>Transaction ID</th>
                <th>Reason</th>
                <th>Date Purchased</th>
                <th>Status</th>
                <th>Order Items</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="returnRequestsTable">
              <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>




<script>
let cart = [];

// === Add to cart ===
document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', function() {
    const id = this.dataset.id;
    const name = this.dataset.name;
    const dosage = this.dataset.dosage;
    const price = parseFloat(this.dataset.price);
    
    const existing = cart.find(item => item.id === id);
    if (existing) {
      existing.qty++;
    } else {
      cart.push({id, name, dosage, price, qty: 1});
    }
    updateCart();
  });
});

// === Update cart display ===
function updateCart() {
  const cartDiv = document.getElementById('cartItems');
  const cartCount = document.getElementById('cartCount');
  
  if (cart.length === 0) {
    cartDiv.innerHTML = '<p class="text-muted text-center">Cart is empty</p>';
    cartCount.textContent = '0';
  } else {
    cartCount.textContent = cart.length;
    cartDiv.innerHTML = cart.map((item, index) => `
      <div class="cart-item">
        <div>
          <div class="fw-semibold">${item.name}</div>
          <small class="text-muted">${item.dosage} • ₱${item.price.toFixed(2)}</small>
        </div>
        <div class="cart-item-controls">
          <button class="qty-btn" onclick="decreaseQty(${index})">−</button>
          <span class="qty-display">${item.qty}</span>
          <button class="qty-btn" onclick="increaseQty(${index})">+</button>
          <button class="btn-remove" onclick="removeItem(${index})">
            <svg class="icon" fill="none" width="25" height="25" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
          <span class="fw-semibold ms-2">₱${(item.price * item.qty).toFixed(2)}</span>
        </div>
      </div>
    `).join('');
  }

  // Update subtotal only for display
  const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
  document.getElementById('subtotalDisplay').textContent = '₱' + subtotal.toFixed(2);
}


// date and time
  function updateDateTime() {
  const now = new Date();
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const formattedDate = now.toLocaleDateString('en-PH', options);
  const formattedTime = now.toLocaleTimeString('en-PH');
  document.getElementById('datetime').innerHTML = `${formattedDate} — ${formattedTime}`;
}
setInterval(updateDateTime, 1000);
updateDateTime();


// === Quantity controls ===
function increaseQty(index) { cart[index].qty++; updateCart(); }
function decreaseQty(index) { if (cart[index].qty > 1) cart[index].qty--; updateCart(); }
function removeItem(index) { cart.splice(index, 1); updateCart(); }

// === Send to cashier ===
document.getElementById('checkoutBtn').addEventListener('click', async function() {
  if (cart.length === 0) {
    alert('Cart is empty!');
    return;
  }

  // Send order to backend
  const response = await fetch('send_to_cashier.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({cart})
  });

  const result = await response.json();

  if (result.success) {
    alert('✅ Order successfully sent to Cashier!');
    cart = [];
    updateCart();
  } else {
    alert('❌ Failed to send order: ' + result.message);
  }
});



// === Show Return Requests Modal ===
document.getElementById('btnReturnRequests').addEventListener('click', () => {
  const modal = new bootstrap.Modal(document.getElementById('returnRequestsModal'));
  modal.show();
  loadReturnRequests();
});

// === Fetch Return Requests ===
function loadReturnRequests() {
  fetch('fetch_return_requests.php')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('returnRequestsTable');
      if (!data.success || data.requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No return requests found.</td></tr>';
        return;
      }

      tbody.innerHTML = data.requests.map(req => {
  const purchaseDate = new Date(req.completed_time);
  const daysElapsed = (Date.now() - purchaseDate.getTime()) / (1000 * 60 * 60 * 24);
  const stillReturnable = daysElapsed <= 7;
  const statusBadge = stillReturnable
    ? `<span class="badge bg-success">Returnable</span>`
    : `<span class="badge bg-secondary">Expired</span>`;

  let orderDetailsHTML = '-';
  if (req.order_data) {
    try {
      const items = JSON.parse(req.order_data);
      orderDetailsHTML = items.map(i => `
        <div class="small">
          ${i.name} (${i.qty}x) - ₱${(i.price * i.qty).toFixed(2)}
        </div>
      `).join('');
    } catch (e) { orderDetailsHTML = '-'; }
  }

  return `
  <tr data-id="${req.id}">
    <td>${req.customer_name}</td>
    <td>${req.transaction_id}</td>
    <td>${req.reason}</td>
    <td>${req.completed_time || '-'}</td>
    <td>${statusBadge}</td>
    <td>${orderDetailsHTML}</td>
    <td class="text-nowrap">
      <div class="d-inline-flex gap-1">
        <button class="btn btn-success btn-xs p-1 px-3" 
                style="font-size: 0.80rem;"
                onclick="updateRequest('${req.id}', 'Approved')" 
                ${!stillReturnable ? 'disabled' : ''}>
          Approve
        </button>
        <button class="btn btn-danger btn-xs p-1 px-3" 
                style="font-size: 0.80rem;"
                onclick="updateRequest('${req.id}', 'Rejected')" 
                ${!stillReturnable ? 'disabled' : ''}>
          Decline
        </button>
      </div>
    </td>
  </tr>
`;

}).join('');

    })
    .catch(err => {
      console.error(err);
      document.getElementById('returnRequestsTable').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data.</td></tr>';
    });
}

// === Update Request Status ===
function updateRequest(id, status) {
  if (!confirm(`Are you sure you want to ${status.toLowerCase()} this return request?`)) return;

  fetch('update_return_status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ id: id, action: status })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Remove the row from the table visually
      const row = document.querySelector(`#returnRequestsTable tr[data-id='${id}']`);
      if (row) row.remove();
    } else {
      alert('❌ Failed to update request: ' + data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('❌ Network error. Could not update request.');
  });


}

// === Category filter ===
document.getElementById('categoryFilter').addEventListener('change', function() {
  const selected = this.value.toLowerCase();
  const rows = document.querySelectorAll('#productTableBody tr');

  rows.forEach(row => {
    const category = row.dataset.category.toLowerCase();
    if (selected === '' || category === selected) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

// === Combine with search filter ===
document.getElementById('searchInput').addEventListener('input', function() {
  const search = this.value.toLowerCase();
  const selected = document.getElementById('categoryFilter').value.toLowerCase();
  const rows = document.querySelectorAll('#productTableBody tr');

  rows.forEach(row => {
    const category = row.dataset.category.toLowerCase();
    const brand = row.cells[1].textContent.toLowerCase();
    const generic = row.cells[2].textContent.toLowerCase();
    const dosage = row.cells[3].textContent.toLowerCase();

    const matchesSearch = brand.includes(search) || generic.includes(search) || dosage.includes(search);
    const matchesCategory = (selected === '' || category === selected);

    if (matchesSearch && matchesCategory) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});




</script>

</body>
</html>