<?php
include 'db_connect.php';

// Fetch inventory
$inventory = [];
$result = $conn->query("SELECT * FROM inventory");
while ($row = $result->fetch_assoc()) {
    $row['status'] = 'Available';
    $row['status_class'] = 'status-ok';

    $quantity = (int)($row['quantity'] ?? 0);
    $max_stock = (int)($row['max_stock'] ?? 0);

    if ($quantity === 0) {
        $row['status'] = "Out of Stock";
        $row['status_class'] = "status-out";
    } elseif ($max_stock > 0 && $quantity < $max_stock * 0.2) {
        $row['status'] = "Low Stock";
        $row['status_class'] = "status-low";
    } elseif ($quantity >= $max_stock && $max_stock > 0) {
        $row['status'] = "Full Stock";
        $row['status_class'] = "status-full";
    }

    $inventory[] = $row;
}

// Dashboard stats
$total_items = count($inventory);
$low_stock = count(array_filter($inventory, fn($i) => $i['status'] === 'Low Stock'));
$total_value = array_sum(array_map(fn($i) => $i['quantity'] * $i['price'], $inventory));
// Fetch pending requests
$pending_requests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='pending'")->fetch_assoc()['total'] ?? 0;
?>
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
    <div class="icon" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
    <button class="toggle" aria-expanded="true" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
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
      <button class="icon-btn" title="Notifications" aria-label="Notifications"><i class="fa-solid fa-bell bell"></i></button>
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
            <p><?= $pending_requests ?></p>
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
          <label for="table-search" class="sr-only">Search inventory</label>
          <input id="table-search" type="search" placeholder="Search items..." aria-label="Search items" />
          <button class="filter-btn" title="Filter" aria-label="Open filters"><i class="fa-solid fa-filter"></i></button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="inventory-table" role="table" aria-label="Inventory table">
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
<?php foreach($inventory as $item): 
    $quantity = (int)$item['quantity'];
    $max_stock = (int)$item['max_stock'];

    // Determine status dynamically
    if ($quantity == 0) {
        $status = "Out of Stock";
        $status_class = "status-out";
    } elseif ($max_stock > 0 && $quantity < $max_stock * 0.2) {
        $status = "Low Stock";
        $status_class = "status-low";
    } elseif ($quantity >= $max_stock && $max_stock > 0) {
        $status = "Full Stock";
        $status_class = "status-full";
    } else {
        $status = "Available";
        $status_class = "status-ok";
    }
?>
<tr>
    <td><?php echo htmlspecialchars($item['name']); ?></td>
    <td><?php echo htmlspecialchars($item['category']); ?></td>
    <td><?php echo $quantity; ?></td>
    <td class="<?php echo $status_class; ?>"><?php echo $status; ?></td>
    <td class="expiration"><?php echo !empty($item['expiration']) ? $item['expiration'] : '--'; ?></td>
    <td><?php echo $max_stock; ?></td>
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


<<<<<<< HEAD
        </table>
=======
    <section class="content-grid">
  <div class="table-panel box">
    <div class="panel-top">
      <h4>Inventory List</h4>

<<<<<<< HEAD
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

<<<<<<< HEAD
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
        <li id="admin"><i class="fa-solid fa-user-shield"></i><span>User Manangement</span></li>
      </ul>
    </nav>
  </aside>

  <main class="main">
    <header class="topbar">
      <div class="top-left">
        <h2>Inventory</h2>
=======
  <section class="content-grid">
    <div class="table-panel box">
      <div class="panel-top">
        <h4>Inventory List</h4>
        <div class="table-controls">
          <input id="table-search" type="search" placeholder="Search items..." />
          <button class="filter-btn"><i class="fa-solid fa-filter"></i></button>
        </div>
>>>>>>> ba000c78567f7917deccc56c69b3b10a5cf210b4
=======
      <div class="table-controls">
        <label for="table-search" class="sr-only">Search inventory</label>
        <input id="table-search" type="search" placeholder="Search items..." aria-label="Search items" />
        <button class="filter-btn" title="Filter" aria-label="Open filters"><i class="fa-solid fa-filter"></i></button>
>>>>>>> parent of ba000c7 (updated dashboard and inventory)
>>>>>>> fd05e8422b26d34bd41ce34072e92c1863a82698
      </div>
    </div>

   <aside class="quick-request box" aria-label="Quick request panel">
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
<input type="number" name="max_stock" id="edit-max-stock" required>

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

</main>

<script>
$(function () {

  const modalAdd = $("#addItemModal");
  const modalEdit = $("#editItemModal");


  // Sidebar toggle
  $(".toggle").click(() => $(".sidebar").toggleClass("hide"));

  // Open Add Modal
  $(".add-item").click(e => { e.preventDefault(); modalAdd.show(); });
  $(".modal .close").click(function () { $(this).closest(".modal").hide(); });
  $(window).click(e => {
    if ($(e.target).hasClass("modal")) $(".modal").hide();
  });

  // Add or Update row dynamically
  function appendRow(item) {
    const row = `
      <tr>
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
    $(".inventory-table tbody").append(row);
  }

  // Add Item
  $("#addItemForm").submit(function (e) {
    e.preventDefault();
    $.post("add_item.php", $(this).serialize(), res => {
      res = JSON.parse(res);
      if (res.success) {
        appendRow(res.item);
        $("#addItemForm")[0].reset();
        modalAdd.hide();


      }
      alert(res.message);
    });
  });

  // Edit Item modal
  $(document).on("click", ".edit-btn", function () {
    const row = $(this).closest("tr");
    const id = $(this).data("id");

    $("#edit-item-id").val(id);
    $("#edit-item-name").val(row.find("td:nth-child(1)").text());
    $("#edit-category").val(row.find("td:nth-child(2)").text());
    $("#edit-quantity").val(row.find("td:nth-child(3)").text());
    $("#edit-max-stock").val(row.find("td:nth-child(6)").text());
    $("#edit-price").val(parseFloat(row.find("td:nth-child(7)").text().replace(/[₱,]/g, '')));
    const exp = row.find("td.expiration").text();
    $("#edit-expiration").val(exp !== '--' ? exp : '');

    modalEdit.show();
  });

  // Update Item
  $("#editItemForm").submit(function (e) {
    e.preventDefault();
    $.post("update_item.php", $(this).serialize(), res => {
      res = JSON.parse(res);
      alert(res.message);
      if (res.success) location.reload();
    });
  });

  // Delete item
  $(document).on("click", ".delete-btn", function () {
    if (!confirm("Are you sure you want to delete this item?")) return;
    const id = $(this).data("id");
    $.post("delete_item.php", { id }, res => {
      res = JSON.parse(res);
      alert(res.message);
      if (res.success) location.reload();
    });
  });

  // Quick request
  let qrItems = [];
  $(document).on("click", ".select-btn", function () {
    const id = $(this).data("id");
    const name = $(this).data("name");
    if (!qrItems.some(i => i.id === id)) {
      qrItems.push({ id, name });
      $("#qr-items").append(`<li data-id="${id}">${name} <button class="remove-qr">×</button></li>`);
    }
  });
  $(document).on("click", ".remove-qr", function () {
    const id = $(this).parent().data("id");
    qrItems = qrItems.filter(i => i.id !== id);
    $(this).parent().remove();
  });
  $("#clear-qr").click(() => { qrItems = []; $("#qr-items").empty(); });
  $("#submit-qr").click(() => {
    if (!qrItems.length) return alert("No items selected!");
    $.post("quick_request.php", { items: qrItems }, res => {
      res = JSON.parse(res);
      alert(res.message);
      if (res.success) { qrItems = []; $("#qr-items").empty(); }
    });
  });

  // Dropdown toggle
  $(document).on("click", ".icon-more", function (e) {
    e.stopPropagation();
    const $menu = $(this).siblings(".more-menu");
    $(".more-menu").not($menu).hide();
    $menu.toggle();
  });
  $(document).ready(function () {
    // Dashboard navigation
    $("#dashboard").css("cursor", "pointer").on("click", function () {
        window.location.href = "dashboard.php";
    });
});


});





</script>

</body>
</html>
