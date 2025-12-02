<?php
require 'db_connect.php';

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

// Pull any popup message (keeps existing behavior)
$popupMessage = '';
if (isset($_SESSION['popupMessage'])) {
    $popupMessage = $_SESSION['popupMessage'];
    unset($_SESSION['popupMessage']);
}

/*
  ---------- DYNAMIC DATA BUILD ----------
  Using schema from your uploaded SQL:
  - inventory table: InventoryID, ProductID, Quantity, ExpirationDate, Status
  - products table: ProductID, ProductName, CategoryID, Min_stock
  - categories table: CategoryID, Category_Name
  - requests table: request_id, status
*/

// 1) Fetch inventory rows joined with product and category (to get Min_stock)
$inventory_rows = [];
try {
    $sql = "
      SELECT
        i.InventoryID,
        i.ProductID,
        i.Quantity,
        i.ExpirationDate,
        i.Status AS InventoryStatus,
        p.ProductName,
        p.Min_stock,
        p.CategoryID,
        c.Category_Name
      FROM inventory i
      LEFT JOIN products p ON p.ProductID = i.ProductID
      LEFT JOIN categories c ON c.CategoryID = p.CategoryID
      ORDER BY p.ProductName
    ";
    $res = $conn->query($sql);
    while ($r = $res->fetch_assoc()) {
      $r['Quantity'] = (int)($r['Quantity'] ?? 0);
      // determine status using rule: qty == 0 => Out of Stock; qty < Min_stock => Low Stock; else In Stock
      $min = isset($r['Min_stock']) ? (int)$r['Min_stock'] : 5; // fallback Min_stock = 5 if null
      if ($r['Quantity'] === 0) {
        $r['computed_status'] = 'Out of Stock';
        $r['status_class'] = 'status-out';
      } elseif ($r['Quantity'] < $min) {
        $r['computed_status'] = 'Low Stock';
        $r['status_class'] = 'status-low';
      } else {
        $r['computed_status'] = 'In Stock';
        $r['status_class'] = 'status-ok';
      }
      $inventory_rows[] = $r;
    }
} catch (Throwable $e) {
    $inventory_rows = [];
}

// 2) Aggregate totals
$total_rows = count($inventory_rows);             // number of inventory rows (batches)
$total_quantity = array_sum(array_map(fn($i) => $i['Quantity'], $inventory_rows)); // sum of quantities

$in_rows = count(array_filter($inventory_rows, fn($i) => $i['computed_status'] === 'In Stock'));
$low_rows = count(array_filter($inventory_rows, fn($i) => $i['computed_status'] === 'Low Stock'));
$out_rows = count(array_filter($inventory_rows, fn($i) => $i['computed_status'] === 'Out of Stock'));

$in_perc  = $total_rows > 0 ? round(($in_rows / $total_rows) * 100) : 0;
$low_perc = $total_rows > 0 ? round(($low_rows / $total_rows) * 100) : 0;
$out_perc = $total_rows > 0 ? round(($out_rows / $total_rows) * 100) : 0;

// 3) Pending requests count
$pending_requests = 0;
try {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE status = 'Pending'");
  $stmt->execute();
  $stmt->bind_result($pending_requests);
  $stmt->fetch();
  $stmt->close();
} catch (Throwable $e) {
  $pending_requests = 0;
}

// 4) Category summary: for each category, how many inventory rows, and whether any are low
$categories = [];
try {
  $sql = "
    SELECT c.CategoryID, c.Category_Name,
           COUNT(i.InventoryID) AS row_count,
           SUM(i.Quantity) AS total_quantity
    FROM categories c
    LEFT JOIN products p ON p.CategoryID = c.CategoryID
    LEFT JOIN inventory i ON i.ProductID = p.ProductID
    GROUP BY c.CategoryID
    ORDER BY c.Category_Name
  ";
  $res = $conn->query($sql);
  while ($c = $res->fetch_assoc()) {
    $cid = (int)$c['CategoryID'];
    $categories[$cid] = [
      'name' => $c['Category_Name'],
      'row_count' => (int)$c['row_count'],
      'total_quantity' => (int)$c['total_quantity'],
      'has_low' => false,
      'has_out' => false
    ];
  }

  // determine per-category low/out flags by scanning inventory_rows
  foreach ($inventory_rows as $ir) {
    $catId = (int)($ir['CategoryID'] ?? 0);
    if ($catId && isset($categories[$catId])) {
      if ($ir['computed_status'] === 'Low Stock') $categories[$catId]['has_low'] = true;
      if ($ir['computed_status'] === 'Out of Stock') $categories[$catId]['has_out'] = true;
    }
  }
} catch (Throwable $e) {
  $categories = [];
}

// ---------- EXPIRATION TIMELINE (improved) ----------
$expirations = [];

try {
    // window: show items expired in last 30 days, and items expiring within next 365 days
    $start = date('Y-m-d', strtotime('-30 days'));
    $end   = date('Y-m-d', strtotime('+365 days'));

    $stmt = $conn->prepare("
      SELECT i.InventoryID, p.ProductName, i.ExpirationDate, i.Quantity
      FROM inventory i
      LEFT JOIN products p ON p.ProductID = i.ProductID
      WHERE i.ExpirationDate IS NOT NULL
        AND i.ExpirationDate BETWEEN ? AND ?
      ORDER BY i.ExpirationDate ASC
      LIMIT 12
    ");
    $stmt->bind_param('ss', $start, $end);
    $stmt->execute();
    $stmt->bind_result($iid, $pname, $expd, $qty);
    while ($stmt->fetch()) {
        // normalize values and compute days difference relative to today
        $expDate = $expd; // string from DB
        $daysLeft = null;
        if ($expDate && strtotime($expDate) !== false) {
            $daysLeft = (int)floor((strtotime($expDate) - strtotime(date('Y-m-d'))) / 86400);
        }
        $expirations[] = [
            'InventoryID'   => $iid,
            'ProductName'   => $pname ?: 'Unnamed Product',
            'ExpirationDate'=> $expDate,
            'Quantity'      => (int)$qty,
            'daysLeft'      => $daysLeft
        ];
    }
    $stmt->close();
} catch (Throwable $e) {
    // keep $expirations as empty on error
    $expirations = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Overview</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="sidebar.css" />
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="notification.css">


  <style>
    /* small inline helpers so labels look good if your CSS misses them */
    .status-label { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size: 18px; }
    .status-label span:first-child { font-weight: 600; font-size: 18px; }
    .status-label .details { color:#555; font-weight:600; font-size: 18px; }
    .progress-bar { background:#e6e6e6; height:10px; border-radius:8px; overflow:hidden; }
    .progress { height:100%; border-radius:8px; }
    .progress.in-stock { background:#28b463; }
    .progress.low-stock { background:#e05a47; } /* orange/red-ish to match your theme */
    .progress.out-stock { background:#8a8a8a; }
    .tag { display:inline-block; padding:4px 8px; border-radius:999px; font-size:.78rem; margin-left:8px; font-weight:700; color:#fff; }
    .tag.low { background: #f05a5a; }
    .tag.good { background: #0b66a1; }
    .expiration .table { width:100%; }
    /* optional helpers for clearer expiration states */
    .expiration .table td { border-bottom: 1px solid #f1f3f6; }
    .expiration small { font-weight:600; }

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
      <li id="dashboard" class="active"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
      <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <?php if ($_SESSION['roleName'] === 'Admin'): ?>
      <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
      <?php endif; ?>
      <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
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
      <h1>Dashboard Overview</h1>   
    </div>

    <div class="cards">
      <div class="card">
        <h4>Total Items per Unit</h4>
        <p><?= number_format($total_quantity) ?></p>
      </div>
      <div class="card red">
        <h4>Low Stock Alerts</h4>
        <p><?= (int)$low_rows ?></p>
      </div>
      <div class="card yellow">
        <h4>Pending Requests</h4>
        <p><?= (int)$pending_requests ?></p>
      </div>
    </div>

  <div class="dashboard-grid">
    <div class="box stock-status">
      <h4>Stock Status Overview</h4>

      <div class="status-item">
        <div class="status-label">
          <span>In Stock</span>
          <span class="details"><?= (int)$in_rows ?> Items — <?= (int)$in_perc ?>%</span>
        </div>
        <div class="progress-bar" aria-hidden="true">
          <div class="progress in-stock" style="width: <?= (int)$in_perc ?>%;"></div>
        </div>
      </div>

      <div class="status-item">
        <div class="status-label">
          <span>Low Stock</span>
          <span class="details"><?= (int)$low_rows ?> Items — <?= (int)$low_perc ?>%</span>
        </div>
        <div class="progress-bar" aria-hidden="true">
          <div class="progress low-stock" style="width: <?= (int)$low_perc ?>%;"></div>
        </div>
      </div>

      <div class="status-item">
        <div class="status-label">
          <span>Out of Stock</span>
          <span class="details"><?= (int)$out_rows ?> Items — <?= (int)$out_perc ?>%</span>
        </div>
        <div class="progress-bar" aria-hidden="true">
          <div class="progress out-stock" style="width: <?= (int)$out_perc ?>%;"></div>
        </div>
      </div>

    </div>

    <div class="box category-summary">
    <h4>Category Summary</h4>
    <p>Inventory breakdown by category</p>

    <?php
      $countShown = 0;
      foreach ($categories as $cat) {
          if ($countShown >= 3) break; // show only first 3
          $label = $cat['has_low']
              ? '<span class="tag low">LOW</span>'
              : '<span class="tag good">GOOD</span>';

          echo '<p style="margin:8px 0; font-size:18px;">' 
                . htmlspecialchars($cat['name']) .
                ' <small style="color:#666; font-size:16px;">(' . (int)$cat['row_count'] . ' rows, ' . (int)$cat['total_quantity'] . ' qty)</small> ' .
                $label .
                '</p>';

          $countShown++;
      }
    ?>

    <button id="show-all-categories" class="btn" 
        style="width: 100%; margin-top: 10px; background:#0b66a1; color:white; box-shadow: none;">
        See More
    </button>
</div>


   <div class="box expiration">
  <h4>Expiration Timeline</h4>
  <p>Visual timeline of upcoming expiration dates</p>

  <table class="table" style="margin-top:100px; width:100%;">
    <tbody>
      <?php if (empty($expirations)): ?>
        <tr><td style="color:#999; padding:10px;">No recent or upcoming expirations found.</td>
      <?php else: ?>
        <?php 
$shown = 0;
foreach ($expirations as $e): 
    if ($shown >= 3) break;
    $shown++;
?>
          <tr style="vertical-align:middle; <?= $rowStyle ?>">
            <td style="padding:8px 6px; width:56%;">
              <div style="font-weight:600; color:#0b3350; font-size:18px;"><?= htmlspecialchars($e['ProductName']) ?></div>
              <div style="font-size:16px; color:#666; margin-top:4px;">
                Qty: <?= (int)$e['Quantity'] ?> &nbsp; • &nbsp; Expires: <?= ($e['ExpirationDate'] ? date('m/d/Y', strtotime($e['ExpirationDate'])) : '--') ?>
              </div>
            </td>
            <td style="padding:8px 6px; width:30%; text-align:right;">
              <?= $label ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <button id="show-all-expirations" class="btn" 
    style="width: 100%; margin-top: 10px; background:#0b66a1; color:white; box-shadow: none;">
    See More
</button>

</div>


    <div class="box quick-actions">
      <h4>Quick Actions</h4>
      <p>Common tasks and shortcuts</p><br>
      <a href="Inventory.php" class="btn"><i class="fa-solid fa-plus"></i> Manage Inventory</a>
      <a href="lowstock.php" class="btn"><i class="fa-solid fa-bell"></i> View Low Stock</a>
      <a href="request_list.php" class="btn"><i class="fa-solid fa-clock"></i> Pending Requests</a>
      <a href="reports.php" class="btn"><i class="fa-solid fa-chart-bar"></i> Generate Reports</a>
    </div>

    <!-- Category List Modal -->
<div id="categoryModal" class="modal" style="
    display:none; position:fixed; inset:0; 
    background:rgba(0,0,0,0.35); justify-content:center; align-items:center; z-index:2000;
">
  <div class="modal-content" style="background:white; width:400px; padding:20px; border-radius:12px;">
      <h3>All Categories</h3>
      <p style="color:#666;">Full inventory category status</p>

      <div style="max-height:300px; overflow-y:auto; margin-top:15px;">
        <?php foreach ($categories as $cat): ?>
          <?php 
            $label = $cat['has_low'] 
                ? '<span class="tag low">LOW</span>' 
                : '<span class="tag good">GOOD</span>';
          ?>
          <p style="margin:10px 0;">
            <?= htmlspecialchars($cat['name']) ?>
            <small style="color:#555;">(<?= (int)$cat['row_count'] ?> rows, <?= (int)$cat['total_quantity'] ?> qty)</small>
            <?= $label ?>
          </p>
        <?php endforeach; ?>
      </div>

      <button id="closeCategoryModal" class="btn" 
          style="background:#444; color:white; width:100%; margin-top:15px;">
          Close
      </button>
  </div>
</div>

<!-- Expiration List Modal -->
<div id="expirationModal" class="modal" style="
    display:none; position:fixed; inset:0; 
    background:rgba(0,0,0,0.35); justify-content:center; align-items:center; z-index:2000;
">
  <div class="modal-content" style="background:white; width:500px; padding:20px; border-radius:12px;">
      <h3>All Expiring Items</h3>
      <p style="color:#666;">Complete list of items with upcoming or past expirations</p>

      <div style="max-height:350px; overflow-y:auto; margin-top:15px;">
        <table style="width:100%; font-size:14px;">
            <tbody>
              <?php foreach ($expirations as $e): 
                  $days = $e['daysLeft'];
                  $label = '';
                  if ($days < 0) {
                      $label = '<span style="color:#c4162e; font-weight:700;">Expired '.abs($days).'d ago</span>';
                  } elseif ($days <= 30) {
                      $label = '<span style="color:#e07b2f; font-weight:700;">'.$days.'d left</span>';
                  } else {
                      $label = '<span style="color:#4c636f;">'.$days.'d left</span>';
                  }
                  $expDate = $e['ExpirationDate'] ? date('m/d/Y', strtotime($e['ExpirationDate'])) : '--';
              ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:8px;">
                        <strong><?= htmlspecialchars($e['ProductName']) ?></strong><br>
                        <small style="color:#666;">
                            Qty: <?= (int)$e['Quantity'] ?> • Expires: <?= $expDate ?>
                        </small>
                    </td>
                    <td style="padding:8px; text-align:right;"><?= $label ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
        </table>
      </div>

      <button id="closeExpirationModal" class="btn" 
          style="background:#444; color:white; width:100%; margin-top:15px;">
          Close
      </button>
  </div>
</div>


  </div> <!-- /dashboard-grid -->
  </main>

  <script src="sidebar.js"></script>
  <script>
    $(document).ready(function () {
      //Navigation
      $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
      $("#inventory").click(function(){ window.location.href = "Inventory.php";});
      $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });
      $("#request").click(function(){ window.location.href = "request_list.php"; });
      $("#nav-suppliers").click(function(){ window.location.href ="supplier.php"; });
      $("#reports").click(function(){ window.location.href = "report.php"; });
      $("#users").click(function(){ window.location.href = "admin.php"; });
      $("#settings").click(function(){ window.location.href = "settings.php"; });
      $("#logout").click(function(){ window.location.href = "logout.php"; });
    });

    // Category Modal Controls
$("#show-all-categories").click(function () {
    $("#categoryModal").css("display", "flex");
});

$("#closeCategoryModal").click(function () {
    $("#categoryModal").hide();
});

// Close if clicking outside modal content
$(window).on("click", function (e) {
    if ($(e.target).attr("id") === "categoryModal") {
        $("#categoryModal").hide();
    }
});

// Expiration Modal Controls
$("#show-all-expirations").click(function () {
    $("#expirationModal").css("display", "flex");
});

$("#closeExpirationModal").click(function () {
    $("#expirationModal").hide();
});

// Close popup when clicking outside
$(window).on("click", function (e) {
    if ($(e.target).attr("id") === "expirationModal") {
        $("#expirationModal").hide();
    }
});


  </script>
  <script src="notification.js" defer></script>

</body>
</html>
