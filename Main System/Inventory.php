<?php
require 'db_connect.php';

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

if (isset($_POST['fetch_product']) && $_POST['fetch_product'] == 1) {
    $productID = (int)$_POST['product_id'];

    $stmt = $conn->prepare("
        SELECT 
        p.ProductName AS product_name,
        p.ProductID AS product_id,
        p.CategoryID AS category_ID,
        c.Category_Name AS category, 
        p.UnitID as unit_ID,
        u.UnitName AS unit
        FROM products p
        JOIN categories c ON p.CategoryID = c.CategoryID
        JOIN units u ON p.UnitID = u.UnitID
        WHERE p.ProductID = ?
    ");
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc() ?: [
      'product_name' => '',
      'product_id' => '',
      'category_id' => '',
      'category'=>'',
      'unit_id' => '',
      'unit'=>''];

    echo json_encode($data);
    exit; // important to stop the rest of the page
}
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
      // $productID       = trim($_POST['item_name'] ?? '');
      // $productID  = trim($_POST['product_id'] ?? '');
      $product    = trim($_POST['product'] ?? '');
      $category   = trim($_POST['category'] ?? '');
      $catid      = trim($_POST['category_id'] ?? '');
      $unit       = trim($_POST['unit'] ?? '');
      $unitId       = trim($_POST['unit_id'] ?? '');
      $price       = trim($_POST['price'] ?? '');
      // $sku        = trim($_POST['sku'] ?? '');
      // $Batchnum     = trim($_POST['Batchnum'] ?? '');
      $minquantity   = isset($_POST['minquantity']) ? (int)$_POST['minquantity'] : 0;
      $maxquantity   = isset($_POST['maxquantity']) ? (int)$_POST['maxquantity'] : 0;
      // $expiration = $_POST['expiration'] ?? null;
      // if ($expiration === '') $expiration = null;


      if ($product === '') {
          throw new Exception("Product name is required.");
      }

      if ($catid === '' && $category === '') {
          throw new Exception("Category is required (select or enter new).");
      }

      if (!is_numeric($price) || $price < 0) {
          throw new Exception("Invalid price.");
      }

      if ($minquantity < 0 || $maxquantity < 0) {
          throw new Exception("Stock quantities must be non-negative.");
      }
      $conn->begin_transaction();
      // Category
      if ($catid !== '') {
        $categoryId = (int)$catid;
    } else {
        // Check if name already exists
        $stmt = $conn->prepare("SELECT CategoryID FROM categories WHERE Category_Name = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $stmt->bind_result($existingCatId);
        if ($stmt->fetch()) {
            $categoryId = $existingCatId;
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO categories (Category_Name) VALUES (?)");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $categoryId = $stmt->insert_id;
        }
        $stmt->close();
    }

      // Unit
      if (!$unitId && $unit !== '') {
          $stmt = $conn->prepare("SELECT UnitID FROM units WHERE UnitName = ?");
          $stmt->bind_param('s', $unit);
          $stmt->execute();
          $stmt->bind_result($existingUnitId);
          if ($stmt->fetch()) {
              $unitId = $existingUnitId;
          } else {
              $stmt->close();
              $stmt = $conn->prepare("INSERT INTO units (UnitName) VALUES (?)");
              $stmt->bind_param('s', $unit);
              $stmt->execute();
              $unitId = $stmt->insert_id;
          }
          $stmt->close();
      }

      $stmt = $conn->prepare("
        INSERT INTO products (ProductName, CategoryID, UnitID, Price, Min_stock, Max_stock)
        VALUES (?, ?, ?, ?, ?, ?)
      ");
      $stmt->bind_param('siidii', $product, $categoryId, $unitId, $price, $minquantity, $maxquantity);
      $stmt->execute();
      $stmt->close();
      // // Insert inventory (SKU optional -> NULLIF to avoid UNIQUE '' issue)

      // $status = ($quantity === 0) ? 'Out of Stock' : (($quantity < 10) ? 'Low Stock' : 'In Stock');
      // $stmt = $conn->prepare("
      //   INSERT INTO inventory (ProductID, SKU, BatchNum, Quantity, ExpirationDate, Status)
      //   VALUES (?, NULLIF(?, ''), ?, ?, ?, ?)
      // ");
      // $stmt->bind_param('ississ', $productID, $sku, $Batchnum, $quantity, $expiration, $status);
      // $stmt->execute();
      // $stmt->close();

//       $stmt = $conn->prepare("
//         SELECT Min_stock, Max_stock FROM inventory i
//         WHERE ProductID = ?
//       ");
//       $stmt->bind_param('i', $productId);
//       $stmt->execute();
//       $result = $stmt->get_result();
//       $data = $results->fetch_assoc();
//       $stmt->close();
//       $minstock = $data['Min_stock'];
//       $maxstock = $data['Max_stock'];

//       // $status = ($quantity === 0) ? 'Out of Stock' : (($quantity < $minstock) ? 'Low Stock' : 'In Stock');

      $conn->commit();
      flash('success', 'Item added successfully.');

    } elseif ($action === 'update_item') {

    $productID  = (int)($_POST['product_id'] ?? 0);
    $product    = trim($_POST['product'] ?? '');
    $category   = trim($_POST['category'] ?? '');
    $categoryId = trim($_POST['category_id'] ?? '');
    $unit       = trim($_POST['unit'] ?? '');
    $unitId     = trim($_POST['unit_id'] ?? '');
    $price      = trim($_POST['price'] ?? '');
    $minquantity = (int)($_POST['minquantity'] ?? 0);
    $maxquantity = (int)($_POST['maxquantity'] ?? 0);

    if ($productID <= 0 || $product === '') {
        throw new Exception("Invalid input for Update.");
    }

    $conn->begin_transaction();

    // Create category if needed
    if (!$categoryId && $category !== '') {
        $stmt = $conn->prepare("SELECT CategoryID FROM categories WHERE Category_Name = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $stmt->bind_result($existingCatId);

        if ($stmt->fetch()) {
            $categoryId = $existingCatId;
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO categories (Category_Name) VALUES (?)");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $categoryId = $stmt->insert_id;
        }
        $stmt->close();
    }

    // Create unit if needed
    if (!$unitId && $unit !== '') {
        $stmt = $conn->prepare("SELECT UnitID FROM units WHERE UnitName = ?");
        $stmt->bind_param("s", $unit);
        $stmt->execute();
        $stmt->bind_result($existingUnitId);

        if ($stmt->fetch()) {
            $unitId = $existingUnitId;
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO units (UnitName) VALUES (?)");
            $stmt->bind_param("s", $unit);
            $stmt->execute();
            $unitId = $stmt->insert_id;
        }
        $stmt->close();
    }

    // Perform update
    $stmt = $conn->prepare("
        UPDATE products 
        SET ProductName = ?, CategoryID = ?, UnitID = ?, Price = ?, Min_stock = ?, Max_stock = ?
        WHERE ProductID = ?
    ");

    $stmt->bind_param(
        "siidiii",
        $product,
        $categoryId,
        $unitId,
        $price,
        $minquantity,
        $maxquantity,
        $productID
    );

    $stmt->execute();
    $rows = $stmt->affected_rows;
    $stmt->close();

    $conn->commit();

    if ($rows > 0) {
        flash('success', 'Item updated successfully.');
    } else {
        flash('warning', 'No changes were made.');
    }
} elseif ($action === 'delete_item') {
      $productid = (int)($_POST['product_id'] ?? 0);
      if ($productid <= 0) throw new Exception('Invalid product id.');

      $stmt = $conn->prepare("DELETE FROM products WHERE ProductID = ?");
      $stmt->bind_param('i', $productid);
      $stmt->execute();
      $affected = $stmt->affected_rows;
      $stmt->close();

      if ($affected > 0) flash('success', 'Product row deleted.');
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
  // SELECT 
  //     p.ProductID,
  //     p.ProductName,
  //     p.SKU,
  //     SUM(i.Quantity) AS TotalQuantity,
  //     p.Min_stock,
  //     p.Max_stock,
  //     u.UnitName,
  //     c.Category_Name
  // FROM products p
  // LEFT JOIN inventory i ON i.ProductID = p.ProductID
  // LEFT JOIN categories c ON c.CategoryID = p.CategoryID
  // LEFT JOIN units u ON u.UnitID = p.UnitID
  // GROUP BY p.ProductID
  // ORDER BY p.ProductName;
$inventory = [];
try {
  $sql = "
    SELECT 
    p.ProductID,
    p.ProductName,
    i.SKU,
    IFNULL(SUM(i.Quantity),0) AS TotalQuantity,
    p.Price,
    p.Min_stock,
    p.Max_stock,
    u.UnitName,
    c.Category_Name,
    c.CategoryID,
    u.UnitName,
    u.UnitID
FROM products p
LEFT JOIN inventory i ON i.ProductID = p.ProductID
LEFT JOIN categories c ON c.CategoryID = p.CategoryID
LEFT JOIN units u ON u.UnitID = p.UnitID
GROUP BY p.ProductID
ORDER BY p.ProductName;


  ";
  $res = $conn->query($sql);
  while ($row = $res->fetch_assoc()) {
    $q = (int)($row['TotalQuantity'] ?? 0);
    $maxstock = (int)($row['Max_stock'] ?? 0);
    $minstock = (int)($row['Min_stock'] ?? 0);
    if     ($q === 0) { 
      $row['status']='Out of Stock'; 
      $row['status_class']='status-out'; }
    elseif ($q < $minstock)   { 
      $row['status']='Low Stock';   
      $row['status_class']='status-low'; }
    elseif ($q > $maxstock) {
      $row['status']='Overstock';
      $row['status_class']='status-high'; 
    }
    else {
      $row['status']='In Stock';
      $row['status_class'] = 'status-ok';
    }
    $inventory[] = $row;
  }
} catch (Throwable $e) {
  $inventory = [];
}
$total_low_and_out = count(array_filter($inventory, fn($i) => 
    $i['status'] === 'Low Stock' || $i['status'] === 'Out of Stock'
));
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
<link rel="stylesheet" href="sidebar.css" />
<link rel="stylesheet" href="inventory.css" />
<link rel="stylesheet" href="notification.css">
<script src="notification.js" defer></script>
<style>
/* (minimal safe styles; keep your inventory.css) */
.status-ok { color:#12805c; font-weight:600; } .status-low { color:#b48a00; font-weight:600; } .status-out { color:#c5162e; font-weight:600; }
.quick-request { padding:14px; }
.qr-actions { display:flex; gap:8px; margin-top:12px; }
.modal { position:fixed; inset:0; display:none; background:rgba(0,0,0,.35); align-items:center; justify-content:center; z-index:1000; }
.modal .modal-content { background:#fff; width:min(560px, 92vw); border-radius:12px; padding:16px; }
.modal label { display:block; font-size:.9rem; margin-top:8px; }
.modal input[type="text"], .modal input[type="number"], .modal input[type="date"], .modal select { width:100%; padding:8px; border:1px solid #ddd; border-radius:8px; }
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

<aside class="sidebar" id="sidebar">
  <div class="profile">
    <div class="icon">
      <img src="logo.png?v=2" alt="MediSync Logo" class="medisync-logo">
    </div>
    <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
  </div>
  <ul class="menu">
    <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
    <li id="inventory" class="active"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
    <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
    <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
    <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
    <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
    <?php if ($_SESSION['roleName'] === 'Admin'): ?>
      <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
    <?php endif; ?>    <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
    <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
  </ul>
</aside>

<main class="main">
  <!-- Notification + Profile icon (top-right in main content) -->
  <div class="topbar-right">
    <?php include 'notification_component.php'; ?>
    <div class="profile-icon">
      <i class="fa-solid fa-user"></i>
    </div>
  </div>

  <!-- Heading Bar -->
  <div class="heading-bar">
    <h1>Inventory</h1>
    <a href="#" class="btn add-item"><i class="fa-solid fa-plus"></i> Add Item</a>
  </div>

  <!-- flash alerts -->
  <div class="alerts">
    <?php foreach ($_SESSION['flash'] as $f): ?>
      <div class="alert <?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
    <?php endforeach; $_SESSION['flash']=[]; ?>
  </div>

  <section class="cards" style="margin-top: 0;">
    <div class="card"><h4>Total Items per Product</h4><p><?= (int)$total_items ?></p></div>
<div class="card red">
      <h4>Low Stock Alerts</h4>
      <p><?= (int)$low_stock ?></p>
    </div>
    <div class="card" style="background-color: #ffe5e5; color: #d32f2f;">
      <h4>Out of Stock Alerts</h4>
      <p><?= (int)($total_low_and_out - $low_stock) ?></p>
    </div>
    <div class="card yellow"><h4>Pending Requests</h4><p><?= (int)$pending_requests ?></p></div>
  </section>

  <section class="content-grid" style="margin-top: 20px;">
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
        <th><input type="checkbox" class="select-all" id="select-all"></th>
        <th>Product</th>
        <th>SKU</th>
        <th>Total Stock</th>
        <th>Unit</th>
        <th>Status</th>
        <th>Category</th>
        <th>Actions</th>
      </tr>
      
    </thead>

    <tbody>
<?php foreach ($inventory as $item): ?>
<tr data-category="<?= htmlspecialchars($item['Category_Name'] ?? '') ?>">
  <td>
    <input type="checkbox" class="item-check" value="<?= (int)$item['ProductID'] ?>">
  </td>

  <td><?= htmlspecialchars($item['ProductName']) ?></td>
  <td><?= htmlspecialchars($item['SKU']) ?></td>
  <td><?= htmlspecialchars($item['TotalQuantity']) ?></td>
  <td><?= htmlspecialchars($item['UnitName'] ?? '-') ?></td>
  <td><?= htmlspecialchars($item['status']) ?></td>
  <td><?= htmlspecialchars($item['Category_Name']) ?></td>
  <td style="white-space: nowrap;">
    <button class="btn view-batches-btn"
        data-productid="<?= (int)$item['ProductID'] ?>"
        data-productname="<?= htmlspecialchars($item['ProductName']) ?>">
      View Batches
    </button>
    <div class="action-wrap">
      <button class="icon-more"><i class="fa-solid fa-ellipsis"></i></button>
      <div class="more-menu">
        <button class="menu-item edit-btn"
            data-productid="<?= (int)$item['ProductID'] ?>"
            data-product="<?= htmlspecialchars($item['ProductName']) ?>"
            data-categoryid="<?= htmlspecialchars($item['CategoryID'] ?? '') ?>"
            data-categoryname="<?= htmlspecialchars($item['Category_Name'] ?? '') ?>"
            data-unitid="<?= htmlspecialchars($item['UnitID'] ?? '') ?>"
            data-unitname="<?= htmlspecialchars($item['Unit'] ?? '') ?>"
            data-price="<?= htmlspecialchars($item['Price'] ?? '') ?>"
            data-minquantity="<?= (int)$item['Min_stock'] ?>"
            data-maxquantity="<?= (int)$item['Max_stock'] ?>"
        >Edit</button>

        <form method="post" style="margin:0;">
          <input type="hidden" name="action" value="delete_item">
          <input type="hidden" name="product_id" value="<?= (int)$item['ProductID'] ?>">
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

      <!-- <div class="table-wrap">
        <table class="inventory-table" role="table" aria-label="Inventory table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Category</th>
              <th>Current Stock</th>
              <th>Status</th>
              <th>Expiration Date</th>
              <th>Batch Number</th>
              <th>SKU</th><th>Unit</th>
              <th>Request</th>
              <th>Action</th>
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
              <td><?= htmlspecialchars($item['BatchNum'] ?? '-') ?></td>
              <td><?= htmlspecialchars($item['SKU'] ?? '-') ?></td>
              <td><?= htmlspecialchars($item['UnitName'] ?? '-') ?></td>
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
      </div>  -->
      
    </div>

    <aside class="quick-request box" aria-label="Quick request panel">
      <div style="padding:14px;">
        <h4>Quick Request</h4>
        <p style="font-size: 18px;">Select items and submit requests for your department.</p>

        <table id="qr-table" class="qr-table">
          <thead><tr><th>Item</th><th>Quantity</th><th>Action</th></tr></thead>
          <tbody id="qr-items"><tr class="empty"><td colspan="3" style="text-align:center;color:#999; font-size: 16px;">No items selected</td></tr></tbody>
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
      <h3>Product Information</h3>
      <form method="post">
        <input type="hidden" name="action" value="add_item">
        <!-- <label>Select Product</label>
        <select name="item_name" id="item_name" required>
        <option value="">-- Select Product --</option>
        <?php
        $result = $conn->query("SELECT ProductID, ProductName FROM products");
        while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['ProductID']}'>{$row['ProductName']}</option>";
        }
        ?>
        </select> -->
        <input type="hidden" name="product_id" id="product_id">
        <label>Product Name</label>
        <input type="text" name="product">
        <label>Category</label>
        <select id="category-select">
          <option value="">-- Select Category --</option>
          <?php
          $catRes = $conn->query("SELECT CategoryID, Category_Name FROM categories ORDER BY Category_Name");
          while ($cat = $catRes->fetch_assoc()):
          ?>
            <option value="<?= $cat['CategoryID'] ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
          <?php endwhile; ?>
        </select>
        <input type="hidden" name="category_id" id="category_id">
        <label>Unit</label>
        <select id="unit-select">
          <option value="">-- Select Unit --</option>
          <?php
          $unitRes = $conn->query("SELECT UnitID, UnitName FROM units ORDER BY UnitName");
          while ($u = $unitRes->fetch_assoc()):
          ?>
            <option value="<?= $u['UnitID'] ?>"><?= htmlspecialchars($u['UnitName']) ?></option>
          <?php endwhile; ?>
        </select>
        <input type="hidden" name="unit_id" id="unit_id">
        <label>Price</label>
        <input type="text" name="price" placeholder="eg... 2.75">
        <label>Minimum Stock</label>
        <input type="number" name="minquantity" min="0" required >
        <label>Maximum Stock</label>
        <input type="number" name="maxquantity" min="0" required>
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
        <input type="hidden" name="product_id" id="edit-product_id">
        <label>Product Name</label>
        <input type="text" name="product" id="edit-product">
        <label>Category</label>
        <select id="edit-category-select">
          <option value="">-- Select Category --</option>
          <?php
          $catRes = $conn->query("SELECT CategoryID, Category_Name FROM categories ORDER BY Category_Name");
          while ($cat = $catRes->fetch_assoc()):
          ?>
            <option value="<?= $cat['CategoryID'] ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
          <?php endwhile; ?>
        </select>
        <input type="text" name="category" id="edit-category" placeholder="Or enter new category">
        <input type="hidden" name="category_id" id="edit-category_id">
        <label>Unit</label>
        <select id="edit-unit-select">
          <option value="">-- Select Unit --</option>
          <?php
          $unitRes = $conn->query("SELECT UnitID, UnitName FROM units ORDER BY UnitName");
          while ($u = $unitRes->fetch_assoc()):
          ?>
            <option value="<?= $u['UnitID'] ?>"><?= htmlspecialchars($u['UnitName']) ?></option>
          <?php endwhile; ?>
        </select>
        <input type="text" name="unit" id="edit-unit" placeholder="Or enter new unit">        
        <input type="hidden" name="unit_id" id="edit-unit_id">
        <label>Price</label>
        <input type="text" name="price" id="edit-price" placeholder="eg... 2.75">
        <label>Minimum Stock</label>
        <input type="number" name="minquantity" min="0" id="edit-minquantity" required >
        <label>Maximum Stock</label>
        <input type="number" name="maxquantity" min="0" id="edit-maxquantity" required>
        <button type="submit" class="btn" style="margin-top:10px;">Update Item</button>
      </form>
    </div>
  </div>

</main>

<script src="sidebar.js"></script>
<script>
$(function () {
  $('#item_name').change(function() {
        var productID = $(this).val();
        if (productID) {
            $.ajax({
                type: 'POST',
                url: '', // same PHP file
                data: { fetch_product: 1, product_id: productID },
                dataType: 'json',
                success: function(response) {
                    $('#product_id').val(response.product_id);
                    $('#category').val(response.category);
                    $('#unit').val(response.unit);
                    $('#category_id').val(response.category_id);
                    $('#unit_id').val(response.unit_id);
                    console.log(response);
                    if (response.product_name) {
                      let namePart = response.product_name.substring(0, 4).toUpperCase(); // fixed here
                      let datePart = new Date().toISOString().slice(2,10).replace(/-/g, '')
                      let randomPart = Math.floor(Math.random() * 900 + 100);
                      let SKU = `${namePart}-${datePart}-${randomPart}`;

                      $('input[name="sku"]').val(SKU);
                  }
                } 
            });
        } else {
            $('#category').val('');
            $('#unit').val('');
            $('#category_id').val('');
            $('#unit_id').val('');
        }
  });

  $('#category-select').change(function() {
  const selectedText = $(this).find('option:selected').text();
  const selectedId   = $(this).val();
  $('#category').val(selectedText);
  $('#category_id').val(selectedId);
});

// Unit select → fill text input and hidden ID
$('#unit-select').change(function() {
  const selectedText = $(this).find('option:selected').text();
  const selectedId   = $(this).val();
  $('#unit').val(selectedText);
  $('#unit_id').val(selectedId);
});

// Optional: if user types new category/unit → clear hidden ID
$('#category').on('input', function() {
  $('#category_id').val(''); // new category
});
$('#unit').on('input', function() {
  $('#unit_id').val(''); // new unit
});
  $(document).on('click', '.view-batches-btn', function() {
  const productId = $(this).data('productid');
  const productName = $(this).data('productname');

  // fetch batches
  $.ajax({
      url: 'fetch_batches.php',
      method: 'POST',
      data: { product_id: productId },
      success: function(response) {
        $('#batches-container').html(`
          <h3>Batches for ${productName}</h3>
          ${response}
        `);
        $('#batches-modal').addClass('show').css('display', 'flex');
      },
      error: function() {
        alert('Failed to load batches.');
      }
  });
});

// Close modal when clicking the close button
$(document).on('click', '#close-modal, .batches-close', function(e) {
  e.preventDefault();
  e.stopPropagation();
  $('#batches-modal').removeClass('show').fadeOut(300);
});

// Close modal when clicking outside modal content
$(document).on('click', '#batches-modal', function(e) {
  if ($(e.target).is('#batches-modal')) {
    $(this).removeClass('show').fadeOut(300);
  }
});

// Close modal on Escape key
$(document).on('keydown', function(e) {
  if (e.key === 'Escape' && $('#batches-modal').hasClass('show')) {
    $('#batches-modal').removeClass('show').fadeOut(300);
  }
});

  // Sidebar toggle handled by sidebar.js

  // Open/Close modals
  $(".add-item").click(e => { e.preventDefault(); $("#addItemModal").css('display','flex'); });
  $(".modal .close").click(function () { $(this).closest(".modal").hide(); });
  $(window).click(e => { if ($(e.target).hasClass("modal")) $(".modal").hide(); });

  //Edit Mode
  // Prefill Edit modal (no AJAX submit — standard POST)
  $(document).on("click", ".edit-btn", function () {
    const $btn = $(this);
    $("#edit-product_id").val($btn.data("productid"));
    $("#edit-product").val($btn.data("product"));
    $("#edit-price").val($btn.data("price"));
    $("#edit-minquantity").val($btn.data("minquantity"));
    $("#edit-maxquantity").val($btn.data("maxquantity"));
    // CATEGORY
    const catId = $btn.data("categoryid");
    const catName = $btn.data("categoryname");

    $("#edit-category_id").val(catId || "");

    if (catId) {
        $("#edit-category-select").val(catId);
        $("#edit-category").val("");
    } else {
        $("#edit-category-select").val("");
        $("#edit-category").val(catName);
    }

    // UNIT
    const unitId = $btn.data("unitid");
    const unitName = $btn.data("unitname");

    $("#edit-unit_id").val(unitId || "");

    if (unitId) {
        $("#edit-unit-select").val(unitId);
        $("#edit-unit").val("");
    } else {
        $("#edit-unit-select").val("");
        $("#edit-unit").val(unitName);
    }


    $("#editItemModal").css('display','flex');
  });

$("#edit-category-select").on("change", function () {
    const val = $(this).val();
    $("#edit-category_id").val(val);
    if (val) $("#edit-category").val("");
});

// Category text input
$("#edit-category").on("input", function () {
    $("#edit-category-select").val("");
    $("#edit-category_id").val("");
});

// Unit dropdown
$("#edit-unit-select").on("change", function () {
    const val = $(this).val();
    $("#edit-unit_id").val(val);
    if (val) $("#edit-unit").val("");
});

// Unit text input
$("#edit-unit").on("input", function () {
    $("#edit-unit-select").val("");
    $("#edit-unit_id").val("");
});
//Edit Mode
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
    const name     = $(this).find("td:nth-child(2)").text().toLowerCase(); // Product
    const category = $(this).find("td:nth-child(7)").text().toLowerCase(); // Category (moved after Unit)
    const status   = $(this).find("td:nth-child(6)").text().toLowerCase(); // Status (moved after Unit)

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

  $(document).on("change", ".item-check", function() {
    const productId = parseInt($(this).val());
    const $row = $(this).closest("tr");
    const productName = $row.find("td:nth-child(2)").text(); // assuming 2nd column is Product Name

    if (this.checked) {
        // Add to QR if not already there
        if (!qrItems.some(i => i.productId === productId)) {
            qrItems.push({ productId, name: productName, quantity: 1 });
        }
    } else {
        // Remove from QR
        qrItems = qrItems.filter(i => i.productId !== productId);
    }

    refreshQRTable();
    const all = document.querySelectorAll(".item-check");
    const checked = document.querySelectorAll(".item-check:checked");
    document.getElementById("select-all").checked = all.length === checked.length;
  });

    // Toggle all checkboxes when "Select All" is clicked
  document.getElementById("select-all").addEventListener("change", function () {
    const isChecked = this.checked;
    document.querySelectorAll(".item-check").forEach(cb => {
      cb.checked = isChecked;
    $(cb).trigger('change'); // trigger change to update QR
    });
});



  // Update quantity in QR table
$(document).on("input", ".qr-qty", function() {
    const productId = $(this).closest("tr").data("productid");
    const qty = Math.max(1, parseInt($(this).val()) || 1);
    qrItems = qrItems.map(i => i.productId === productId ? { ...i, quantity: qty } : i);
});

// Remove item via button
$(document).on("click", ".remove-qr", function() {
    const productId = $(this).closest("tr").data("productid");
    qrItems = qrItems.filter(i => i.productId !== productId);
    // Also uncheck the checkbox in the main table
    $(`.item-check[value="${productId}"]`).prop("checked", false);
    refreshQRTable();
});

// Clear all
$("#clear-qr").click(() => {
    qrItems = [];
    $(".item-check").prop("checked", false);
    refreshQRTable();
});
  // $(document).on("click", ".select-btn", function () {
  //   const productId = $(this).data("productid");
  //   const name = $(this).data("name");
  //   if (!qrItems.some(i => i.productId === productId)) {
  //     qrItems.push({ productId, name, quantity: 1 });
  //     refreshQRTable();
  //     alert(`${name} added to Quick Request.`);
  //   } else {
  //     alert(`${name} is already in your request list.`);
  //   }
  // });
  // $(document).on("click", ".remove-qr", function () {
  //   const productId = $(this).closest("tr").data("productid");
  //   qrItems = qrItems.filter(i => i.productId !== productId);
  //   refreshQRTable();
  // });
  // $(document).on("input", ".qr-qty", function () {
  //   const productId = $(this).closest("tr").data("productid");
  //   const qty = Math.max(1, parseInt($(this).val()) || 1);
  //   qrItems = qrItems.map(i => i.productId === productId ? { ...i, quantity: qty } : i);
  // });
  // Clear all
$("#clear-qr").click(() => {
    qrItems = [];
    $(".item-check, #select-all").prop("checked", false);
    refreshQRTable();
});
  $("#submit-qr").click(() => {
    if (!qrItems.length) return alert("Please select at least one item.");
    if (!confirm("Submit this request?")) return;
    $.post("quick_request.php", { items: JSON.stringify(qrItems) }, res => {
      // try { res = JSON.parse(res); } catch (e) { return alert("Unexpected response."); } 
      if (res.success) { alert("Request submitted successfully!"); qrItems = []; refreshQRTable(); }
      else alert("Error: " + (res.message || "failed"));
    }).fail(() => alert("Failed to send request."));
  });
  refreshQRTable();

  // Nav
  $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
  $("#inventory").click(function(){ window.location.href = "Inventory.php"; });
  $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });
  $("#request").click(function(){ window.location.href = "request_list.php"; });
  $("#nav-suppliers").click(function(){ window.location.href = "supplier.php"; });
  $("#reports").click(function(){ window.location.href = "report.php"; });
  $("#users").click(function(){ window.location.href = "admin.php"; });
  $("#settings").click(function(){ window.location.href = "settings.php"; });
  $("#logout").click(function(){ window.location.href = "logout.php"; });

      

});
document.addEventListener('DOMContentLoaded', () => {
  const table = document.querySelector('.inventory-table tbody');
  const rows = Array.from(table.querySelectorAll('tr'));

  const categoryFilter = document.getElementById('category-filter');
  const stockFilter = document.getElementById('stock-filter');
  const clearBtn = document.getElementById('clear-filters');

  function applyFilters() {
    const categoryVal = categoryFilter.value.toLowerCase();
    const stockVal = stockFilter.value.toLowerCase();

    rows.forEach(row => {
      const category = row.dataset.category?.toLowerCase() || '';
      const status = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

      const matchCategory = !categoryVal || category === categoryVal;
      const matchStock = !stockVal || status === stockVal;

      if (matchCategory && matchStock) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  categoryFilter.addEventListener('change', applyFilters);
  stockFilter.addEventListener('change', applyFilters);

  clearBtn.addEventListener('click', () => {
    categoryFilter.value = '';
    stockFilter.value = '';
    applyFilters();
  });
});
function loadNotifications() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            let total = data.messages + data.lowstock;

            if (total > 0) {
                $("#notifBadge").text(total).show();
            } else {
                $("#notifBadge").hide();
            }
        }
    });
}
loadNotifications();

// Refresh every 10 seconds
setInterval(loadNotifications, 10000);

</script>
<!-- Batch Modal -->
    <div id="batches-modal" style="display:none;">
      <div class="modal-content">
        <span id="close-modal">&times;</span>
        <div id="batches-container"></div>
      </div>
    </div>
</body>
</html>
