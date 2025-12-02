<?php
require 'db_connect.php';

/* ---------------------------------------------------------
   FETCH LOW & OUT-OF-STOCK ITEMS
--------------------------------------------------------- */
$sql = "
  SELECT 
    i.InventoryID,
    i.Quantity,
    i.Status,
    i.ExpirationDate,
    p.ProductName,
    u.UnitName AS Unit,
    c.Category_Name,
    s.supplier_name AS SupplierName
  FROM inventory i
  JOIN products p ON p.ProductID = i.ProductID
  JOIN units u ON u.UnitID = p.UnitID
  LEFT JOIN categories c ON c.CategoryID = p.CategoryID
  LEFT JOIN suppliers s ON s.supplier_id = p.CategoryID
  WHERE i.Quantity <= 5
  ORDER BY i.Quantity ASC
";

$result = $conn->query($sql);

$lowStockItems = [];
while ($row = $result->fetch_assoc()) {
    $q = (int)$row['Quantity'];

    if ($q === 0) {
        $row['priority'] = 'high';
    } elseif ($q < 5) {
        $row['priority'] = 'medium';
    }
    $lowStockItems[] = $row;
}

$highPriorityItems   = array_filter($lowStockItems, fn($i) => $i['priority'] === 'high');
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

  <!-- STYLESHEETS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="lowstock.css" />
  <link rel="stylesheet" href="notification.css">



  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

      <div class="top-right">
        <?php include 'notification_component.php'; ?>
      </div>
  </div>


  <div class="content-area">

      <!-- SUMMARY BOXES -->
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



      <!-- HIGH PRIORITY LIST -->
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
                <div class="alert-item high" data-id="<?= $item['InventoryID'] ?>">
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



      <!-- MEDIUM PRIORITY LIST -->
      <section class="priority-section medium-priority">
        <div class="priority-header">
          <h3>Medium Priority Alerts</h3>
          <p>These items need attention soon</p>

          <div class="search-container">
            <i class="fa-solid fa-filter filter-icon"></i>
            <input type="text" id="searchMedium" class="search-bar" placeholder="Search medium priority items...">
          </div>
        </div>

        <div class="priority-list" id="mediumPriorityList">
          <?php if (empty($mediumPriorityItems)): ?>
            <p>No medium priority alerts 🎉</p>
          <?php else: ?>
            <?php foreach ($mediumPriorityItems as $item): ?>
              <div class="alert-item medium"data-id="<?= $item['InventoryID'] ?>">
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

  <!-- ITEM DETAILS MODAL -->
<div id="itemDetailsModal" class="item-modal hidden">
    <div class="item-modal-content">

        <button class="close-modal">&times;</button>

        <h2>Item Details</h2>
        <p class="item-desc"></p>

        <span class="alert-tags"></span>

        <hr>

        <!-- Item Info -->
        <section class="modal-section">
            <h3>Item Information</h3>
            <div class="modal-grid">
                <p><strong>Item Name:</strong> <span id="mItemName"></span></p>
                <p><strong>Category:</strong> <span id="mCategory"></span></p>
                <p><strong>Item ID:</strong> <span id="mItemID"></span></p>
                <p><strong>Location:</strong> Emergency Ward</p>
            </div>
        </section>

        <!-- Stock Info -->
        <section class="modal-section">
            <h3>Stock Information</h3>
            <div class="modal-grid">
                <p><strong>Current Stock:</strong> <span id="mCurrentStock"></span></p>
                <p><strong>Minimum Required Stock:</strong> <span id="mMinStock"></span></p>
            </div>

            <div class="stock-bar">
                <div id="mStockLevel"></div>
            </div>

            <div id="mStockDeficit" class="stock-alert"></div>
        </section>

        <!-- Supplier Info -->
        <section class="modal-section">
            <h3>Supplier Information</h3>
            <div class="supplier-box">
                <p><strong>Supplier Name:</strong> <span id="mSupplier"></span></p>
                <p><strong>Contact Person:</strong> <span id="mContactPerson"></span></p>
                <p><strong>Email:</strong> <span id="mEmail"></span></p>
                <p><strong>Phone:</strong> <span id="mPhone"></span></p>
            </div>
        </section>

        <button id="contactSupplierBtn" class="btn-contact">Contact Supplier</button>
        <button class="btn-close">Close</button>
    </div>
</div>

<style>
.item-modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,.5);
    display: flex; justify-content: center; align-items: center;
    z-index: 9999;
}
.item-modal.hidden { display: none; }

.item-modal-content {
    background: #fff;
    width: 750px;
    padding: 25px;
    border-radius: 12px;
    max-height: 95vh;
    overflow-y: auto;
    position: relative;
}

.close-modal {
    position: absolute;
    top: 15px; right: 20px;
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
}

.modal-section { margin-top: 25px; }
.modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.supplier-box {
    background: #e8f0ff;
    padding: 15px;
    border-radius: 10px;
}

.btn-contact {
    margin-top: 20px;
    background: #0066ff;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}

.btn-close {
    margin-left: 10px;
    padding: 10px 20px;
    border-radius: 8px;
    background: #f0f0f0;
    border: none;
    cursor: pointer;
}

.stock-bar {
    width: 100%;
    height: 8px;
    background: #ddd;
    border-radius: 5px;
    margin-top: 8px;
}
#mStockLevel {
    height: 100%;
    background: #3b82f6;
    border-radius: 5px;
}
.stock-alert {
    margin-top: 10px;
    background: #fff8d1;
    border-left: 5px solid #e6c200;
    padding: 10px;
    border-radius: 6px;
}
</style>

</div>




<!-- PAGE SCRIPTS -->
<script>
$(function(){

  // Sidebar toggle
  $(".toggle").click(()=> $(".sidebar").toggleClass("hide"));

  // Sidebar navigation
  $("#dashboard").click(()=>window.location.href="dashboard.php");
  $("#inventory").click(()=>window.location.href="inventory.php");
  $("#request").click(()=>window.location.href="request_list.php");
  $("#nav-suppliers").click(()=>window.location.href="supplier.php");
  $("#logout").click(()=>window.location.href="logout.php");

  // Search filtering
  $("#searchHigh").keyup(function(){
    let val = $(this).val().toLowerCase();
    $("#highPriorityList .alert-item").each(function(){
      $(this).toggle($(this).text().toLowerCase().includes(val));
    });
  });

  $("#searchMedium").keyup(function(){
    let val = $(this).val().toLowerCase();
    $("#mediumPriorityList .alert-item").each(function(){
      $(this).toggle($(this).text().toLowerCase().includes(val));
    });
  });

});

// OPEN MODAL (View Items)
$(document).on("click", ".label.view", function() {

    let itemElement = $(this).closest(".alert-item");
    let id = itemElement.data("id");

    $.get("get_item_details.php", { id: id }, function(res){

        if (res.error) {
            alert(res.error);
            return;
        }

        $("#mItemName").text(res.ProductName);
        $("#mCategory").text(res.Category_Name ?? "—");
        $("#mItemID").text(res.InventoryID);

        $("#mCurrentStock").text(res.Quantity + " units");
        $("#mMinStock").text(res.Min_stock + " units");

        let pct = Math.min(100, (res.Quantity / res.Min_stock) * 100);
        $("#mStockLevel").css("width", pct + "%");

        let deficit = res.Min_stock - res.Quantity;
        $("#mStockDeficit").text(
            deficit > 0 
            ? `Stock Deficit: ${deficit} units needed to reach minimum stock`
            : "Stock is sufficient"
        );

        $("#mSupplier").text(res.supplier_name ?? "N/A");
        $("#mContactPerson").text(res.contact_person ?? "N/A");
        $("#mEmail").text(res.email ?? "N/A");
        $("#mPhone").text(res.phone ?? "N/A");

        $("#contactSupplierBtn").off().click(function(){
            window.location.href = "supplier.php?id=" + res.supplier_id;
        });

        $("#itemDetailsModal").removeClass("hidden");
    });
});

// Close modal
$(document).on("click", ".close-modal, .btn-close", function(){
    $("#itemDetailsModal").addClass("hidden");
});
</script>


</script>

<!-- GLOBAL NOTIFICATION SCRIPT (must be LAST) -->
<script src="notification.js" defer></script>

</body>
</html>
