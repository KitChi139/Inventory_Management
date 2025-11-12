<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request List</title>
  <link rel="stylesheet" href="request_list.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    /* i did this because it wont work in the css file */
/* === FILTERS SECTION (aligned horizontally) === */
.filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.filters input[type="text"] {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid #d3d3d3;
  border-radius: 8px;
  background-color: #fff;
  font-size: 14px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.filter-right {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.filter-right select {
  padding: 10px 14px;
  border: 1px solid #d3d3d3;
  border-radius: 8px;
  background-color: #fff;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  transition: border-color 0.2s ease;
}

.filter-right select:focus {
  outline: none;
  border-color: #4f9cf9;
  box-shadow: 0 0 0 2px rgba(79,156,249,0.3);
}

/* === TABLE ACTION BUTTONS (bottom-right corner) === */
.table-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e0e0e0;
  position: relative;
}

#selectedCount {
  position: absolute;
  left: 0;
  color: #043873;
  font-weight: 600;
  bottom: 15px;
}

.buttons {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 10px 22px;
  border: 1px solid #ccc;
  border-radius: 30px;
  cursor: pointer;
  background: #fff;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.25s ease;
}

.btn.clear {
  color: #333;
}

.btn.create {
  background: #4f9cf9;
  color: #fff;
  border: none;
  padding: 12px 30px;
  font-size: 15px;
  font-weight: 700;
  border-radius: 40px;
  box-shadow: 0 4px 10px rgba(79,156,249,0.3);
}

.btn.create:hover {
  background: #1c74e9;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(79,156,249,0.35);
}
</style>

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
      <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li class="active"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <li><i class="fa-solid fa-users"></i><span>Users</span></li>
      <li><i class="fa-solid fa-gear"></i><span>Settings</span></li>
      <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
  </nav>
</aside>

<main class="main">
  <header class="topbar">
    <div class="top-left"><h2>Request</h2></div>
    <div class="top-right">
      <button class="icon-btn"><i class="fa-solid fa-bell bell"></i></button>
    </div>
  </header>

  <div class="cards">
    <div class="card blue">
      <h3>Total Requests</h3>
      <p>
        <?php 
          $sql = "SELECT COUNT(*) AS total FROM requests";
          $res = $conn->query($sql);
          echo $res->fetch_assoc()['total'];
        ?>
      </p>
    </div>
    <div class="card yellow">
      <h3>Pending</h3>
      <p>
        <?php 
          $sql = "SELECT COUNT(*) AS total FROM requests WHERE status='Pending'";
          $res = $conn->query($sql);
          echo $res->fetch_assoc()['total'];
        ?>
      </p>
    </div>
    <div class="card green">
      <h3>Approved</h3>
      <p>
        <?php 
          $sql = "SELECT COUNT(*) AS total FROM requests WHERE status='Approved'";
          $res = $conn->query($sql);
          echo $res->fetch_assoc()['total'];
        ?>
      </p>
    </div>
  </div>

  <div class="table-section">
    <div class="filters">
  <input type="text" id="search" placeholder="Search requests...">
  
  <div class="filter-right">
    <select id="supplierFilter">
      <option value="all">All Suppliers</option>
      <?php
        $suppliers = $conn->query("SELECT DISTINCT supplier_name FROM suppliers");
        while($s = $suppliers->fetch_assoc()) {
          echo "<option value='{$s['supplier_name']}'>{$s['supplier_name']}</option>";
        }
      ?>
    </select>
    
    <select id="statusFilter">
      <option value="all">All Statuses</option>
      <option value="Pending">Pending</option>
      <option value="Approved">Approved</option>
      <option value="Rejected">Rejected</option>
    </select>
  </div>
</div>

<table id="requestTable">
  <thead>
    <tr>
      <th><input type="checkbox" id="selectAll"></th>
      <th>Request ID</th>
      <th>Product</th>
      <th>Quantity</th>
      <th>Requester</th>
      <th>Supplier</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $sql = "SELECT r.request_id, p.ProductName, r.quantity, r.requester, r.status, r.request_date, s.supplier_name
              FROM requests r
              JOIN products p ON r.ProductID = p.ProductID
              LEFT JOIN suppliers s ON s.supplier_id = p.Category_ID
              ORDER BY r.request_date DESC";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo "<tr data-supplier='{$row['supplier_name']}' data-status='{$row['status']}'>
                  <td><input type='checkbox' class='row-check'></td>
                  <td>{$row['request_id']}</td>
                  <td>{$row['ProductName']}</td>
                  <td>{$row['quantity']}</td>
                  <td>{$row['requester']}</td>
                  <td>{$row['supplier_name']}</td>
                  <td><span class='status {$row['status']}'>{$row['status']}</span></td>
                  <td>{$row['request_date']}</td>
                </tr>";
        }
      }
    ?>
  </tbody>
</table>

<div class="table-actions">
  <span id="selectedCount">0 selected</span>
  <div class="buttons">
    <button id="clearSelection" class="btn clear">Clear Selection</button>
    <button id="createRequest" class="btn create">Create Purchase Request</button>
  </div>
</div>

  </div>
</main>

<script>
$(function () {
  $(".toggle").click(() => $(".sidebar").toggleClass("hide"));

  // Search Filter
  $("#search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#requestTable tbody tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });

  // Supplier Filter
  $("#supplierFilter, #statusFilter").change(function(){
    var supplier = $("#supplierFilter").val();
    var status = $("#statusFilter").val();

    $("#requestTable tbody tr").each(function(){
      var matchSupplier = supplier === "all" || $(this).data("supplier") === supplier;
      var matchStatus = status === "all" || $(this).data("status") === status;
      $(this).toggle(matchSupplier && matchStatus);
    });
  });

  // Checkbox logic
  $("#selectAll").on("change", function() {
    $(".row-check").prop("checked", this.checked);
    updateSelection();
  });

  $("#requestTable").on("change", ".row-check", function() {
    updateSelection();
  });

  $("#clearSelection").click(function(){
    $(".row-check").prop("checked", false);
    $("#selectAll").prop("checked", false);
    updateSelection();
  });

  function updateSelection(){
    var selected = $(".row-check:checked").length;
    $("#selectedCount").text(selected + " selected");
  }

  // Placeholder for Create Purchase Request
  $("#createRequest").click(function(){
    var selected = $(".row-check:checked");
    if(selected.length === 0){
      alert("Please select at least one item.");
      return;
    }
    alert(selected.length + " item(s) selected for purchase request!");
  });

  // Navigation
  $("#dashboard").click(()=> window.location.href = "dashboard.php");
  $("#inventory").click(()=> window.location.href = "inventory.php");
  $("#low-stock").click(()=> window.location.href = "lowstock.php");
  $("#nav-suppliers").click(()=> window.location.href = "suppliers.php");
  $("#logout").click(()=> window.location.href = "logout.php");
});
</script>
</body>
</html>
