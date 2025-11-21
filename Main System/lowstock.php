<?php
require 'db_connect.php';

// Fetch Low and Out of Stock Items
$lowStockItems = [];
$sql = "
  SELECT 
    i.InventoryID,
    i.Quantity,
    i.Status,
    i.ExpirationDate,
    p.ProductName,
    u.UnitName as Unit,
    c.Category_Name
  FROM inventory i
  JOIN products p ON p.ProductID = i.ProductID
  JOIN units u ON u.UnitID = p.UnitID
  LEFT JOIN categories c ON c.CategoryID = p.CategoryID
  WHERE i.Quantity <= 5
  ORDER BY i.Quantity ASC
";

$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
  $q = (int)$row['Quantity'];
  if     ($q === 0) { $row['priority'] = 'high'; }
  elseif ($q < 5)   { $row['priority'] = 'medium'; }
  $lowStockItems[] = $row;
}

$highPriorityItems = array_filter($lowStockItems, fn($i) => $i['priority'] === 'high');
$mediumPriorityItems = array_filter($lowStockItems, fn($i) => $i['priority'] === 'medium');

$highCount   = count($highPriorityItems);
$mediumCount = count($mediumPriorityItems);
$totalAlerts = count($lowStockItems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Low Stock Alerts</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="lowstock.css" />
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="profile">
    <div class="icon"><i class="fa-solid fa-user"></i></div>
    <button class="toggle"><i class="fa-solid fa-bars"></i></button>
  </div>
  <nav>
    <h3 class="title">Navigation</h3>
    <ul class="menu">
      <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
      <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li class="active"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
      <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
      <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
  </nav>
</aside>

<!-- MAIN SECTION -->
<div class="main">
  <div class="topbar">
    <h2>Low Stock Alerts</h2>
    <i class="fa-solid fa-bell bell"></i>
  </div>

  <div class="content-area">
    <div class="alert-summary">
      <div class="alert-box high">
        <h3>High Priority</h3>
        <p><?= $highCount ?> Require immediate action</p>
      </div>
      <div class="alert-box medium">
        <h3>Medium Priority</h3>
        <p><?= $mediumCount ?> Need attention soon</p>
      </div>
      <div class="alert-box total">
        <h3>Total Alerts</h3>
        <p><?= $totalAlerts ?> Active Alerts</p>
      </div>
    </div>

    <!-- HIGH PRIORITY ALERTS -->
    <section class="priority-section high-priority">
      <div class="priority-header">
        <h3>High Priority Alerts</h3>
        <p>These items require immediate attention</p>
        <div class="search-container">
          <i class="fa-solid fa-filter filter-icon"></i>
          <input type="text" id="searchHigh" class="search-bar" placeholder="Search high priority items...">
        </div>
      </div>

      <div class="priority-list" id="highPriorityList">
        <?php if (empty($highPriorityItems)): ?>
          <p>No high priority alerts 🎉</p>
        <?php else: ?>
          <?php foreach ($highPriorityItems as $item): ?>
            <div class="alert-item high">
              <h4><?= htmlspecialchars($item['ProductName']) ?></h4>
              <p>Company: <?= htmlspecialchars($item['SupplierName'] ?? 'N/A') ?></p>
              <p>Category: <?= htmlspecialchars($item['Category_Name'] ?? '—') ?></p>
              <p>Current: <?= (int)$item['Quantity'] ?> | Unit: <?= htmlspecialchars($item['Unit']) ?></p>
              <?php if (!empty($item['ExpirationDate'])): ?>
                <p>Expiration: <?= htmlspecialchars($item['ExpirationDate']) ?></p>
              <?php endif; ?>

              <div class="labels">
  <span class="label out">Out of Stock</span>
  <span class="label high">High Priority</span>
  <span class="label view">View Items</span>
  <span class="label contact">Contact Supplier</span>
</div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- MEDIUM PRIORITY ALERTS -->
    <section class="priority-section medium-priority">
      <div class="priority-header">
        <h3>Medium Priority Alerts</h3>
        <p>These items need attention in the near future</p>
        <div class="search-container">
          <i class="fa-solid fa-filter filter-icon"></i>
          <input type="text" id="searchMedium" class="search-bar" placeholder="Search medium priority items...">
        </div>
      </div>

      <div class="priority-list" id="mediumPriorityList">
        <?php if (empty($mediumPriorityItems)): ?>
          <p>No medium priority alerts ✅</p>
        <?php else: ?>
          <?php foreach ($mediumPriorityItems as $item): ?>
            <div class="alert-item medium">
              <h4><?= htmlspecialchars($item['ProductName']) ?></h4>
              <p>Company: <?= htmlspecialchars($item['SupplierName'] ?? 'N/A') ?></p>
              <p>Category: <?= htmlspecialchars($item['Category_Name'] ?? '—') ?></p>
              <p>Current: <?= (int)$item['Quantity'] ?> | Unit: <?= htmlspecialchars($item['Unit']) ?></p>
              <?php if (!empty($item['ExpirationDate'])): ?>
                <p>Expiration: <?= htmlspecialchars($item['ExpirationDate']) ?></p>
              <?php endif; ?>

              <div class="labels">
  <span class="label low">Low Stock</span>
  <span class="label medium">Medium Priority</span>
  <span class="label view">View Items</span>
  <span class="label contact">Contact Supplier</span>
</div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>

<script>
$(function(){
  $(".toggle").click(()=> $(".sidebar").toggleClass("hide"));

  // Sidebar navigation
  $("#dashboard").click(()=>window.location.href="dashboard.php");
  $("#inventory").click(()=>window.location.href="inventory.php");
  $("#request").click(()=>window.location.href="request_list.php");
  $("#nav-suppliers").click(()=>window.location.href="supplier.php");
  $("#logout").click(()=>window.location.href="logout.php");

  // Search filtering
  $("#searchHigh").on("keyup", function(){
    const val = $(this).val().toLowerCase();
    $("#highPriorityList .alert-item").filter(function(){
      $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
    });
  });

  $("#searchMedium").on("keyup", function(){
    const val = $(this).val().toLowerCase();
    $("#mediumPriorityList .alert-item").filter(function(){
      $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
    });
  });
});
</script>
</body>
</html>
