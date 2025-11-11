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
        <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
        <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
        <li class="active"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
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
    <div class="top-left"><h2>Request</h2></div>
    <div class="top-right">
      <button class="icon-btn" title="Notifications" aria-label="Notifications"><i class="fa-solid fa-bell bell"></i></button>
      
    </div>
  </header>

    <div class="cards">
      <div class="card blue"><h3>Total Requests</h3>
          <?php 
            $sql = "SELECT COUNT(*) AS total FROM requests";
            $res = $conn->query($sql);
            echo $res->fetch_assoc()['total'];
          ?>
      </div>
      <div class="card yellow"><h3>Pending</h3>
        
          <?php 
            $sql = "SELECT COUNT(*) AS total FROM requests WHERE status='Pending'";
            $res = $conn->query($sql);
            echo $res->fetch_assoc()['total'];
          ?>
      </div>
      <div class="card green"><h3>Approved</h3>
        
          <?php 
            $sql = "SELECT COUNT(*) AS total FROM requests WHERE status='Approved'";
            $res = $conn->query($sql);
            echo $res->fetch_assoc()['total'];
          ?>
      </div>
    </div>
    <br>

    <div class="table-section">
      <input type="text" id="search" placeholder="Search requests...">

      <table id="requestTable">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Requester</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $sql = "SELECT r.request_id, p.ProductName, r.quantity, r.requester, r.status, r.request_date 
                    FROM requests r 
                    JOIN products p ON r.ProductID = p.ProductID 
                    ORDER BY r.request_date DESC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['request_id']}</td>
                        <td>{$row['ProductName']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['requester']}</td>
                        <td><span class='status {$row['status']}'>{$row['status']}</span></td>
                        <td>{$row['request_date']}</td>
                      </tr>";
              }
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
  </main>
<script>
$(function () {
  // Sidebar toggle
  $(".toggle").click(() => $(".sidebar").toggleClass("hide"));
  $(document).ready(function() {
  $("#search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#requestTable tbody tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });
});


 // Nav
  $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
  $("#nav-suppliers").click(function(){ window.location.href = "suppliers.php"; });
  $("#inventory").click(function(){ window.location.href = "inventory.php"; });
  $("#low-stock").click(function(){ window.location.href = "lowstock.html"; });

 //Logout
      $("#logout").click(function(){
        window.location.href = "logout.php";
      });
});
</script>
</body>


</html>
