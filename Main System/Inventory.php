<?php
include 'db_connect.php';

// Fetch inventory data
$inventory = [];
$result = $conn->query("SELECT * FROM inventory");
while ($row = $result->fetch_assoc()) {
    $inventory[] = $row;
}

// Calculate dashboard stats
$total_items = count($inventory);
$low_stock = 0;
$total_value = 0;

foreach ($inventory as &$item) {
    $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
    $max_stock = isset($item['max_stock']) ? $item['max_stock'] : 0;

    // Determine status dynamically
    if ($quantity == 0) {
        $item['status'] = "Out of Stock";
        $item['status_class'] = "status-out";
    } elseif ($max_stock > 0 && $quantity < $max_stock * 0.2) {
        $item['status'] = "Low Stock";
        $item['status_class'] = "status-low";
    } elseif ($quantity >= $max_stock) {
        $item['status'] = "Full Stock";
        $item['status_class'] = "status-full";
    } else {
        $item['status'] = "Available";
        $item['status_class'] = "status-ok";
    }

    // Low stock counter for dashboard
    if ($item['status'] === "Low Stock") $low_stock++;

    // Total value
    $price = isset($item['price']) ? $item['price'] : 0;
    $total_value += $quantity * $price;
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
</head>
<body>

<aside class="sidebar" aria-label="Primary">
  <div class="profile">
    <div class="icon"><i class="fa-solid fa-user"></i></div>
    <button class="toggle"><i class="fa-solid fa-bars"></i></button>
  </div>
  <h3 class="title">Navigation</h3>
  <nav>
    <ul class="menu">
      <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
      <li class="active"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <li><i class="fa-solid fa-clock-rotate-left"></i><span>Transactions</span></li>
      <li><i class="fa-solid fa-users"></i><span>Users</span></li>
      <li><i class="fa-solid fa-gear"></i><span>Settings</span></li>
    </ul>
  </nav>
</aside>

<main class="main">
  <header class="topbar">
    <div class="top-left">
      <h2>Inventory</h2>
    </div>
    <div class="top-right">
      <button class="icon-btn"><i class="fa-solid fa-bell bell"></i></button>
      <a href="#" class="btn add-item"><i class="fa-solid fa-plus"></i> Add Item</a>
    </div>
  </header>

  <section class="cards">
    <div class="card">
      <h4>Total Items In Stock</h4>
      <p><?php echo $total_items; ?></p>
    </div>
    <div class="card red">
      <h4>Low Stock Alerts</h4>
      <p><?php echo $low_stock; ?></p>
    </div>
    <div class="card yellow">
      <h4>Pending Requests</h4>
      <p>0</p>
    </div>
    <div class="card blue">
      <h4>Total Value of Inventory</h4>
      <p>₱<?php echo number_format($total_value, 2); ?></p>
    </div>
  </section>

  <section class="content-grid">
    <div class="table-panel box">
      <div class="panel-top">
        <h4>Inventory List</h4>
        <div class="table-controls">
          <input id="table-search" type="search" placeholder="Search items..." />
          <button class="filter-btn"><i class="fa-solid fa-filter"></i></button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="inventory-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Category</th>
              <th>Current Stock</th>
              <th>Status</th>
              <th>Expiration Date</th>
              <th>Max Stock</th>
              <th>Price</th>
              <th>Request</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($inventory as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td><?php echo htmlspecialchars($item['category']); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td class="<?php echo $item['status_class']; ?>"><?php echo $item['status']; ?></td>
              <td class="expiration"><?php echo !empty($item['expiration']) ? $item['expiration'] : '--'; ?></td>
              <td><?php echo $item['max_stock']; ?></td>
              <td>₱<?php echo number_format($item['price'], 2); ?></td>
              <td>
                <button class="btn small select-btn" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>">Select</button>
              </td>
              <td>
                <div class="action-wrap">
                  <button class="icon-more"><i class="fa-solid fa-ellipsis"></i></button>
                  <div class="more-menu">
                    <button class="menu-item edit-btn" data-id="<?php echo $item['id']; ?>">Edit</button>
                    <button class="menu-item danger delete-btn" data-id="<?php echo $item['id']; ?>">Delete</button>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <aside class="quick-request box">
      <h4>Quick Request</h4>
      <p>Select items and submit requests for your department</p>
      <div class="qr-field">
        <div class="qr-row">
          <strong>Items:</strong>
          <ul id="qr-items" class="qr-list"></ul>
        </div>
        <div class="qr-row">
          <strong>Available:</strong> <span id="qr-available">--</span>
        </div>
        <div class="qr-row">
          <strong>Location:</strong> <span id="qr-location">Warehouse</span>
        </div>
      </div>
      <div class="qr-actions">
        <button class="btn primary" id="submit-qr">Submit Request</button>
        <button class="btn secondary" id="clear-qr">Clear Selection</button>
      </div>
    </aside>

    <!-- Add Item Modal -->
    <div class="modal" id="addItemModal" style="display:none;">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Add New Item</h3>
        <form id="addItemForm">
          <label>Item Name</label>
          <input type="text" name="item_name" required>
          <label>Category</label>
          <input type="text" name="category" required>
          <label>Quantity</label>
          <input type="number" name="current_stock" required>
          <label>Max Stock</label>
          <input type="number" name="max_stock" required>
          <label>Price</label>
          <input type="number" step="0.01" name="price" required>
          <label>Expiration</label>
          <input type="date" name="expiration">
          <button type="submit" class="btn">Add Item</button>
        </form>
      </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal" id="editItemModal" style="display:none;">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Edit Item</h3>
        <form id="editItemForm">
          <input type="hidden" name="id" id="edit-item-id">
          <label>Item Name</label>
          <input type="text" name="item_name" id="edit-item-name" required>
          <label>Category</label>
          <input type="text" name="category" id="edit-category" required>
          <label>Quantity</label>
          <input type="number" name="current_stock" id="edit-quantity" required>
          <label>Max Stock</label>
          <input type="number" name="max_stock" id="edit-max-stock" required>
          <label>Price</label>
          <input type="number" step="0.01" name="price" id="edit-price" required>
          <label>Expiration</label>
          <input type="date" name="expiration" id="edit-expiration">
          <button type="submit" class="btn">Update Item</button>
        </form>
      </div>
    </div>
  </section>
</main>

<script>
$(function () {
    // Sidebar toggle
    $(".toggle").click(() => $(".sidebar").toggleClass("hide"));

    // Add Item Modal
    const addModal = $("#addItemModal");
    $(".add-item").click(e => { e.preventDefault(); addModal.show(); });
    $(".modal .close").click(() => $(".modal").hide());
    $(window).click(e => { if ($(e.target).hasClass('modal')) $(".modal").hide(); });

    $("#addItemForm").off("submit").on("submit", function(e){
    e.preventDefault();
    $.post("add_item.php", $(this).serialize(), function(res){
        res = JSON.parse(res);
        alert(res.message);
        if(res.success){
            const item = res.item;
            const row = `<tr>
                <td>${item.name}</td>
                <td>${item.category}</td>
                <td>${item.quantity}</td>
                <td class="${item.status_class}">${item.status}</td>
                <td>${item.expiration || '--'}</td>
                <td>${item.max_stock}</td>
                <td>₱${parseFloat(item.price).toFixed(2)}</td>
                <td><button class="btn small select-btn" data-id="${item.id}" data-name="${item.name}">Select</button></td>
                <td>
                    <div class="action-wrap">
                        <button class="icon-more"><i class="fa-solid fa-ellipsis"></i></button>
                        <div class="more-menu">
                            <button class="menu-item edit-btn" data-id="${item.id}">Edit</button>
                            <button class="menu-item danger delete-btn" data-id="${item.id}">Delete</button>
                        </div>
                    </div>
                </td>
            </tr>`;
            $(".inventory-table tbody").append(row); // append only once
            $("#addItemModal").hide();
            $("#addItemForm")[0].reset();
        }
    });
});


    // Delete Item
    $(document).on("click", ".delete-btn", function(){
        if(!confirm("Are you sure?")) return;
        const id = $(this).data("id");
        $.post("delete_item.php", {id}, function(res){
            res = JSON.parse(res);
            alert(res.message);
            if(res.success) location.reload();
        });
    });

    // Quick Request
    let qrItems = [];
    $(document).on("click", ".select-btn", function(){
        const id = $(this).data("id");
        const name = $(this).data("name");
        if(!qrItems.find(i=>i.id===id)){
            qrItems.push({id, name});
            $("#qr-items").append(`<li data-id="${id}">${name} <button class="remove-qr">×</button></li>`);
        }
    });
    $(document).on("click", ".remove-qr", function(){
        const id = $(this).parent().data("id");
        qrItems = qrItems.filter(i=>i.id!==id);
        $(this).parent().remove();
    });
    $("#clear-qr").click(()=>{ qrItems=[]; $("#qr-items").empty(); });
    $("#submit-qr").click(()=>{
        if(qrItems.length===0){ alert("No items selected!"); return; }
        $.post("quick_request.php", {items: qrItems}, function(res){
            res = JSON.parse(res);
            alert(res.message);
            if(res.success){ qrItems=[]; $("#qr-items").empty(); }
        });
    });

    // Edit Item Modal
    $(document).on("click", ".edit-btn", function(){
        const row = $(this).closest("tr");
        $("#edit-item-id").val($(this).data("id"));
        $("#edit-item-name").val(row.find("td:nth-child(1)").text());
        $("#edit-category").val(row.find("td:nth-child(2)").text());
        $("#edit-quantity").val(row.find("td:nth-child(3)").text());
        $("#edit-max-stock").val(row.find("td:nth-child(6)").text());
        $("#edit-price").val(parseFloat(row.find("td:nth-child(7)").text().replace(/[₱,]/g,'')));
        const exp = row.find("td.expiration").text();
        $("#edit-expiration").val(exp!=='--'?exp:'');
        $("#editItemModal").show();
    });

    $("#editItemForm").submit(function(e){
        e.preventDefault();
        $.post("update_item.php", $(this).serialize(), function(res){
            res = JSON.parse(res);
            alert(res.message);
            if(res.success) location.reload();
        });
    });

    // Sidebar navigation
    $("#dashboard").css("cursor","pointer").click(()=>{ window.location.href="dashboard.php"; });

    // Action menu toggle
    $(document).on("click", ".icon-more", function(e){
        e.stopPropagation();
        const menu = $(this).siblings(".more-menu");
        $(".more-menu").not(menu).hide();
        menu.toggle();
    });
    $(document).click(()=>$(".more-menu").hide());
});
</script>
</body>
</html>
