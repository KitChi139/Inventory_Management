<?php
session_start();
require 'db_connect.php';

// ---------- HELPERS ----------
function flash($type, $msg) {
  $_SESSION['flash'][] = ['type'=>$type, 'msg'=>$msg];
}
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];

// ---------- HANDLE POST (Add / Edit / Delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  try {
    if ($action === 'add_item') {
      // Inputs
      $name       = trim($_POST['item_name'] ?? '');
      $category   = trim($_POST['category'] ?? '');
      $unit       = trim($_POST['unit'] ?? '');
      $sku        = trim($_POST['sku'] ?? '');
      $quantity   = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
      $expiration = $_POST['expiration'] ?? null;
      if ($expiration === '') $expiration = null;

      if ($name === '' || $category === '' || $quantity < 0) {
        throw new Exception('Invalid input for Add.');
      }

      $conn->begin_transaction();

      // Find/create category
      $stmt = $conn->prepare("SELECT Category_ID FROM categories WHERE Category_Name = ?");
      $stmt->bind_param('s', $category);
      $stmt->execute();
      $stmt->bind_result($catId);
      $exists = $stmt->fetch();
      $stmt->close();
      if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO categories (Category_Name) VALUES (?)");
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $catId = $stmt->insert_id;
        $stmt->close();
      }

      // Find/create product (by name + category)
      $stmt = $conn->prepare("SELECT ProductID, Unit FROM products WHERE ProductName = ? AND Category_ID = ?");
      $stmt->bind_param('si', $name, $catId);
      $stmt->execute();
      $stmt->bind_result($productId, $existingUnit);
      $pExists = $stmt->fetch();
      $stmt->close();

      if (!$pExists) {
        $stmt = $conn->prepare("INSERT INTO products (ProductName, Category_ID, Unit) VALUES (?, ?, ?)");
        $stmt->bind_param('sis', $name, $catId, $unit);
        $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();
      } else if ($unit !== '' && $unit !== $existingUnit) {
        $stmt = $conn->prepare("UPDATE products SET Unit = ? WHERE ProductID = ?");
        $stmt->bind_param('si', $unit, $productId);
        $stmt->execute();
        $stmt->close();
      }

      // Insert inventory (SKU optional -> NULLIF to avoid UNIQUE '' issue)
      $status = ($quantity === 0) ? 'Out of Stock' : (($quantity < 5) ? 'Low Stock' : 'In Stock');
      $stmt = $conn->prepare("
        INSERT INTO inventory (ProductID, SKU, Quantity, ExpirationDate, Status)
        VALUES (?, NULLIF(?, ''), ?, ?, ?)
      ");
      $stmt->bind_param('isiss', $productId, $sku, $quantity, $expiration, $status);
      $stmt->execute();
      $stmt->close();

      $conn->commit();
      flash('success', 'Item added successfully.');

    } elseif ($action === 'update_item') {
      $inventoryId = (int)($_POST['inventory_id'] ?? 0);
      $name        = trim($_POST['item_name'] ?? '');
      $category    = trim($_POST['category'] ?? '');
      $unit        = trim($_POST['unit'] ?? '');
      $sku         = trim($_POST['sku'] ?? '');
      $quantity    = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
      $expiration  = $_POST['expiration'] ?? null;
      if ($expiration === '') $expiration = null;

      if ($inventoryId <= 0 || $name === '' || $category === '' || $quantity < 0) {
        throw new Exception('Invalid input for Update.');
      }

      $conn->begin_transaction();

      // Get ProductID from inventory
      $stmt = $conn->prepare("SELECT ProductID FROM inventory WHERE InventoryID = ?");
      $stmt->bind_param('i', $inventoryId);
      $stmt->execute();
      $stmt->bind_result($productId);
      if (!$stmt->fetch()) { $stmt->close(); throw new Exception('Inventory row not found.'); }
      $stmt->close();

      // Find/create category
      $stmt = $conn->prepare("SELECT Category_ID FROM categories WHERE Category_Name = ?");
      $stmt->bind_param('s', $category);
      $stmt->execute();
      $stmt->bind_result($catId);
      $cExists = $stmt->fetch();
      $stmt->close();
      if (!$cExists) {
        $stmt = $conn->prepare("INSERT INTO categories (Category_Name) VALUES (?)");
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $catId = $stmt->insert_id;
        $stmt->close();
      }

      // Update product fields
      $stmt = $conn->prepare("UPDATE products SET ProductName = ?, Category_ID = ?, Unit = ? WHERE ProductID = ?");
      $stmt->bind_param('sisi', $name, $catId, $unit, $productId);
      $stmt->execute();
      $stmt->close();

      // Update inventory (SKU via NULLIF)
      $status = ($quantity === 0) ? 'Out of Stock' : (($quantity < 5) ? 'Low Stock' : 'In Stock');
      $stmt = $conn->prepare("
        UPDATE inventory
           SET SKU = NULLIF(?, ''),
               Quantity = ?,
               ExpirationDate = ?,
               Status = ?
         WHERE InventoryID = ?
      ");
      $stmt->bind_param('sissi', $sku, $quantity, $expiration, $status, $inventoryId);
      $stmt->execute();
      $stmt->close();

      $conn->commit();
      flash('success', 'Item updated successfully.');

    } elseif ($action === 'delete_item') {
      $inventoryId = (int)($_POST['inventory_id'] ?? 0);
      if ($inventoryId <= 0) throw new Exception('Invalid inventory id.');

      $stmt = $conn->prepare("DELETE FROM inventory WHERE InventoryID = ?");
      $stmt->bind_param('i', $inventoryId);
      $stmt->execute();
      $affected = $stmt->affected_rows;
      $stmt->close();

      if ($affected > 0) flash('success', 'Inventory row deleted.');
      else flash('warning', 'Row not found or already deleted.');
    }

  } catch (Throwable $e) {
    try { $conn->rollback(); } catch (Throwable $ignore) {}
    flash('error', 'Error: ' . $e->getMessage());
  }

  // PRG: redirect back to self to avoid resubmits
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

// ---------- FETCH INVENTORY + STATS ----------
$inventory = [];
try {
  $sql = "
    SELECT 
      i.InventoryID,
      i.ProductID,
      i.SKU,
      i.Quantity,
      i.ExpirationDate,
      i.Status AS InventoryStatus,
      p.ProductName,
      p.Unit,
      c.Category_Name
    FROM inventory i
    JOIN products p ON p.ProductID = i.ProductID
    LEFT JOIN categories c ON c.Category_ID = p.Category_ID
    ORDER BY p.ProductName
  ";
  $res = $conn->query($sql);
  while ($row = $res->fetch_assoc()) {
    $q = (int)($row['Quantity'] ?? 0);
    $row['status'] = 'In Stock';
    $row['status_class'] = 'status-ok';
    if     ($q === 0) { $row['status']='Out of Stock'; $row['status_class']='status-out'; }
    elseif ($q < 5)   { $row['status']='Low Stock';   $row['status_class']='status-low'; }
    $inventory[] = $row;
  }
} catch (Throwable $e) {
  $inventory = [];
}

$total_items = count($inventory);
$low_stock   = count(array_filter($inventory, fn($i) => $i['status'] === 'Low Stock'));

$pending_requests = 0;
try {
  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM requests WHERE status = 'Pending'");
  $stmt->execute();
  $stmt->bind_result($pending_requests);
  $stmt->fetch();
  $stmt->close();
} catch (Throwable $e) {
  $pending_requests = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Inventory — Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="inventory.css" />
<style>
/* (minimal safe styles; keep your inventory.css) */
.status-ok { color:#12805c; font-weight:600; } .status-low { color:#b48a00; font-weight:600; } .status-out { color:#c5162e; font-weight:600; }
.quick-request { padding:14px; }
.qr-table { width:100%; border-collapse:collapse; margin-top:8px;} .qr-table th, .qr-table td { padding:8px; border-bottom:1px solid #eee; }
.qr-actions { display:flex; gap:8px; margin-top:12px; }
.modal { position:fixed; inset:0; display:none; background:rgba(0,0,0,.35); align-items:center; justify-content:center; z-index:1000; }
.modal .modal-content { background:#fff; width:min(560px, 92vw); border-radius:12px; padding:16px; }
.modal label { display:block; font-size:.9rem; margin-top:8px; }
.modal input[type="text"], .modal input[type="number"], .modal input[type="date"] { width:100%; padding:8px; border:1px solid #ddd; border-radius:8px; }
.filter-dropdown { position:absolute; background:#fff; border:1px solid #eee; border-radius:10px; padding:10px; right:0; top:44px; width:260px; box-shadow:0 8px 26px rgba(0,0,0,.15);}
.hidden { display:none; } .search-wrapper { display:flex; gap:8px; align-items:center; position:relative; }
.search-wrapper input[type="search"] { padding:8px 10px; border:1px solid #ddd; border-radius:8px; min-width:260px; }
.clear-btn { background:#f2f2f2; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; }


.alerts { margin-bottom:12px; }
.alert { padding:10px 12px; border-radius:8px; margin-bottom:8px; }
.alert.success { background:#e8fff4; color:#0d6b4d; }
.alert.error { background:#ffe9e9; color:#a10f1d; }
.alert.warning { background:#fff7e5; color:#8a6a00; }
</style>
</head>
<body>

<aside class="sidebar" aria-label="Primary">
  <div class="profile">
    <div class="icon" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
    <button class="toggle" aria-expanded="true" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
  </div>
  <h3 class="title">Navigation</h3>
  <nav>
    <div class="navbar">
      <ul class="menu">
        <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
        <li class="active"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
        <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
        <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
        <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
        <li><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
        <li><i class="fa-solid fa-users"></i><span>Users</span></li>
        <li><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
      </ul>
    </div>
  </nav>
</aside>

<main class="main">
  <header class="topbar">
    <div class="top-left"><h2>Inventory</h2></div>
    <div class="top-right">
      <button class="icon-btn" title="Notifications" aria-label="Notifications"><i class="fa-solid fa-bell bell"></i></button>
      <a href="#" class="btn add-item"><i class="fa-solid fa-plus"></i> Add Item</a>
    </div>
  </header>

  <!-- flash alerts -->
  <div class="alerts">
    <?php foreach ($_SESSION['flash'] as $f): ?>
      <div class="alert <?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
    <?php endforeach; $_SESSION['flash']=[]; ?>
  </div>

  <section class="cards">
    <div class="card"><h4>Total Items</h4><p><?= (int)$total_items ?></p></div>
    <div class="card red"><h4>Low Stock Alerts</h4><p><?= (int)$low_stock ?></p></div>
    <div class="card yellow"><h4>Pending Requests</h4><p><?= (int)$pending_requests ?></p></div>
  </section>

  <section class="content-grid">
    <div class="table-panel box">
      <div class="panel-top">
        <h4>Inventory List</h4>
        <div class="table-controls">
          <div class="search-wrapper">
            <input id="table-search" type="search" placeholder="Search items..." aria-label="Search items">
            <button class="filter-icon" id="filter-toggle" title="Filter"><i class="fa-solid fa-filter"></i></button>
            <div id="filter-dropdown" class="filter-dropdown hidden">
              <div>
                <label for="category-filter">Category</label>
                <select id="category-filter">
                  <option value="">All Categories</option>
                  <?php
                  $catRes = $conn->query("SELECT Category_Name FROM categories ORDER BY Category_Name");
                  while ($cat = $catRes->fetch_assoc()):
                  ?>
                    <option value="<?= htmlspecialchars($cat['Category_Name']) ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div style="margin-top:8px;">
                <label for="stock-filter">Stock Status</label>
                <select id="stock-filter">
                  <option value="">All Status</option>
                  <option value="in stock">In Stock</option>
                  <option value="low stock">Low Stock</option>
                  <option value="out of stock">Out of Stock</option>
                </select>
              </div>
              <button id="clear-filters" class="clear-btn" style="margin-top:8px;">Clear Filters</button>
            </div>
          </div>
        </div>
      </div>

      <div class="table-wrap">
        <table class="inventory-table" role="table" aria-label="Inventory table">
          <thead>
            <tr>
              <th>Item</th><th>Category</th><th>Current Stock</th><th>Status</th>
              <th>Expiration Date</th><th>SKU</th><th>Unit</th><th>Request</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($inventory as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['ProductName']) ?></td>
              <td><?= htmlspecialchars($item['Category_Name'] ?? '-') ?></td>
              <td><?= (int)$item['Quantity'] ?></td>
              <td class="<?= $item['status_class'] ?>"><?= htmlspecialchars($item['status']) ?></td>
              <td class="expiration"><?= !empty($item['ExpirationDate']) ? htmlspecialchars($item['ExpirationDate']) : '--' ?></td>
              <td><?= htmlspecialchars($item['SKU'] ?? '-') ?></td>
              <td><?= htmlspecialchars($item['Unit'] ?? '-') ?></td>
              <td>
                <button class="btn small select-btn"
                        data-productid="<?= (int)$item['ProductID'] ?>"
                        data-name="<?= htmlspecialchars($item['ProductName']) ?>">Select</button>
              </td>
              <td>
                <div class="action-wrap">
                  <button class="icon-more"><i class="fa-solid fa-ellipsis"></i></button>
                  <div class="more-menu">
                    <button class="menu-item edit-btn"
                      data-inventoryid="<?= (int)$item['InventoryID'] ?>"
                      data-name="<?= htmlspecialchars($item['ProductName']) ?>"
                      data-category="<?= htmlspecialchars($item['Category_Name'] ?? '') ?>"
                      data-unit="<?= htmlspecialchars($item['Unit'] ?? '') ?>"
                      data-sku="<?= htmlspecialchars($item['SKU'] ?? '') ?>"
                      data-quantity="<?= (int)$item['Quantity'] ?>"
                      data-expiration="<?= htmlspecialchars($item['ExpirationDate'] ?? '') ?>"
                    >Edit</button>

                    <form method="post" style="margin:0;">
                      <input type="hidden" name="action" value="delete_item">
                      <input type="hidden" name="inventory_id" value="<?= (int)$item['InventoryID'] ?>">
                      <button type="submit" class="menu-item danger" onclick="return confirm('Delete this row?')">Delete</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <aside class="quick-request box" aria-label="Quick request panel">
      <div style="padding:14px;">
        <h4>Quick Request</h4>
        <p>Select items and submit requests for your department.</p>

        <table id="qr-table" class="qr-table">
          <thead><tr><th>Item</th><th>Quantity</th><th>Action</th></tr></thead>
          <tbody id="qr-items"><tr class="empty"><td colspan="3" style="text-align:center;color:#999;">No items selected</td></tr></tbody>
        </table>
        <div class="qr-summary">
          <p><strong>Total Items:</strong> <span id="qr-total">0</span></p>
          <p><strong>Location:</strong> <span id="qr-location">Warehouse</span></p>
        </div>
        <div class="qr-actions">
          <button class="btn primary" id="submit-qr"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
          <button class="btn secondary" id="clear-qr"><i class="fa-solid fa-eraser"></i> Clear</button>
        </div>
      </div>
    </aside>
  </section>

  <!-- Add Item Modal -->
  <div class="modal" id="addItemModal" style="display:none;">
    <div class="modal-content">
      <span class="close" role="button" aria-label="Close">&times;</span>
      <h3>Add New Item</h3>
      <form method="post">
        <input type="hidden" name="action" value="add_item">
        <label>Item Name</label>
        <input type="text" name="item_name" required>
        <label>Category</label>
        <input type="text" name="category" placeholder="e.g. Skincare" required>
        <label>Unit</label>
        <input type="text" name="unit" placeholder="e.g. pcs, box, bottle">
        <label>SKU</label>
        <input type="text" name="sku" placeholder="Optional">
        <label>Quantity</label>
        <input type="number" name="quantity" min="0" required>
        <label>Expiration</label>
        <input type="date" name="expiration">
        <button type="submit" class="btn" style="margin-top:10px;">Add Item</button>
      </form>
    </div>
  </div>

  <!-- Edit Item Modal -->
  <div class="modal" id="editItemModal" style="display:none;">
    <div class="modal-content">
      <span class="close" role="button" aria-label="Close">&times;</span>
      <h3>Edit Item</h3>
      <form method="post">
        <input type="hidden" name="action" value="update_item">
        <input type="hidden" name="inventory_id" id="edit-inventory-id">
        <label>Item Name</label>
        <input type="text" name="item_name" id="edit-item-name" required>
        <label>Category</label>
        <input type="text" name="category" id="edit-category" required>
        <label>Unit</label>
        <input type="text" name="unit" id="edit-unit">
        <label>SKU</label>
        <input type="text" name="sku" id="edit-sku">
        <label>Quantity</label>
        <input type="number" name="quantity" id="edit-quantity" min="0" required>
        <label>Expiration</label>
        <input type="date" name="expiration" id="edit-expiration">
        <button type="submit" class="btn" style="margin-top:10px;">Update Item</button>
      </form>
    </div>
  </div>

</main>

<script>
$(function () {
  // Sidebar toggle
  $(".toggle").click(() => $(".sidebar").toggleClass("hide"));

  // Open/Close modals
  $(".add-item").click(e => { e.preventDefault(); $("#addItemModal").css('display','flex'); });
  $(".modal .close").click(function () { $(this).closest(".modal").hide(); });
  $(window).click(e => { if ($(e.target).hasClass("modal")) $(".modal").hide(); });

  // Prefill Edit modal (no AJAX submit — standard POST)
  $(document).on("click", ".edit-btn", function () {
    const $btn = $(this);
    $("#edit-inventory-id").val($btn.data("inventoryid"));
    $("#edit-item-name").val($btn.data("name"));
    $("#edit-category").val($btn.data("category"));
    $("#edit-unit").val($btn.data("unit"));
    $("#edit-sku").val($btn.data("sku"));
    $("#edit-quantity").val($btn.data("quantity"));
    $("#edit-expiration").val($btn.data("expiration") || '');
    $("#editItemModal").css('display','flex');
  });

  // Action menu
  $(document).on("click", ".icon-more", function (e) {
    e.stopPropagation();
    $(".more-menu").not($(this).siblings(".more-menu")).hide();
    $(this).siblings(".more-menu").toggle();
  });
  $(document).on("click", function () { $(".more-menu").hide(); });
  $(document).on("click", ".more-menu", function (e) { e.stopPropagation(); });

  // Search & filter
  $("#filter-toggle").on("click", function (e) { e.stopPropagation(); $("#filter-dropdown").toggleClass("hidden"); });
  $(document).on("click", function (e) { if (!$(e.target).closest(".filter-dropdown, #filter-toggle").length) $("#filter-dropdown").addClass("hidden"); });
  $("#table-search").on("keyup", filterTable);
  $("#category-filter, #stock-filter").on("change", filterTable);
  $("#clear-filters").on("click", function () { $("#table-search").val(''); $("#category-filter").val(''); $("#stock-filter").val(''); filterTable(); $("#filter-dropdown").addClass("hidden"); });

  function filterTable() {
    const searchValue   = $("#table-search").val().toLowerCase();
    const categoryValue = $("#category-filter").val().toLowerCase();
    const stockValue    = $("#stock-filter").val().toLowerCase();
    $(".inventory-table tbody tr").each(function () {
      const name     = $(this).find("td:nth-child(1)").text().toLowerCase();
      const category = $(this).find("td:nth-child(2)").text().toLowerCase();
      const status   = $(this).find("td:nth-child(4)").text().toLowerCase();
      const matchesSearch   = name.includes(searchValue) || category.includes(searchValue);
      const matchesCategory = !categoryValue || category === categoryValue;
      const matchesStock    = !stockValue || status === stockValue;
      $(this).toggle(matchesSearch && matchesCategory && matchesStock);
    });
  }

  // Quick Request (kept as-is; still posts to quick_request.php)
  let qrItems = [];
  function refreshQRTable() {
    const $tbody = $("#qr-items").empty();
    if (qrItems.length === 0) {
      $tbody.append(`<tr class="empty"><td colspan="3" style="text-align:center;color:#999;">No items selected</td></tr>`);
    } else {
      qrItems.forEach(item => {
        $tbody.append(`
          <tr data-productid="${item.productId}">
            <td>${item.name}</td>
            <td><input type="number" min="1" value="${item.quantity}" class="qr-qty"></td>
            <td><button class="btn small danger remove-qr"><i class="fa-solid fa-xmark"></i></button></td>
          </tr>
        `);
      });
    }
    $("#qr-total").text(qrItems.length);
  }
  $(document).on("click", ".select-btn", function () {
    const productId = $(this).data("productid");
    const name = $(this).data("name");
    if (!qrItems.some(i => i.productId === productId)) {
      qrItems.push({ productId, name, quantity: 1 });
      refreshQRTable();
      alert(`${name} added to Quick Request.`);
    } else {
      alert(`${name} is already in your request list.`);
    }
  });
  $(document).on("click", ".remove-qr", function () {
    const productId = $(this).closest("tr").data("productid");
    qrItems = qrItems.filter(i => i.productId !== productId);
    refreshQRTable();
  });
  $(document).on("input", ".qr-qty", function () {
    const productId = $(this).closest("tr").data("productid");
    const qty = Math.max(1, parseInt($(this).val()) || 1);
    qrItems = qrItems.map(i => i.productId === productId ? { ...i, quantity: qty } : i);
  });
  $("#clear-qr").click(() => {
    if (!qrItems.length) return alert("No items to clear.");
    if (confirm("Clear all selected items?")) { qrItems = []; refreshQRTable(); alert("All items cleared."); }
  });
  $("#submit-qr").click(() => {
    if (!qrItems.length) return alert("Please select at least one item.");
    if (!confirm("Submit this request?")) return;
    $.post("quick_request.php", { items: JSON.stringify(qrItems) }, res => {
      try { res = JSON.parse(res); } catch (e) { return alert("Unexpected response."); }
      if (res.success) { alert("Request submitted successfully!"); qrItems = []; refreshQRTable(); }
      else alert("Error: " + (res.message || "failed"));
    }).fail(() => alert("Failed to send request."));
  });
  refreshQRTable();

  // Nav
  $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
  $("#nav-suppliers").click(function(){ window.location.href = "suppliers.php"; });
  $("#request").click(function(){ window.location.href = "request_list.php"; });
  $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });

  //Logout
      $("#logout").click(function(){
        window.location.href = "logout.php";
      });
});
</script>
</body>
</html>
