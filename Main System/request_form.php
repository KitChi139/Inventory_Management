<?php
session_start();



$popupMessage = '';
if (isset($_SESSION['popupMessage'])) {
    $popupMessage = $_SESSION['popupMessage'];
    unset($_SESSION['popupMessage']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashdoard Overview</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="request_form.css" />
</head>
<body>

  <div class="sidebar">
    <div class="profile">
      <div class="icon"><i class="fa-solid fa-user"></i></div>
      <button class="toggle"><i class="fa-solid fa-bars"></i></button>
    </div>

    <h3 class="title">Navigation</h3>
    <ul class="menu">
      <li class="active"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
      <li><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
      <li><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <li><i class="fa-solid fa-clock-rotate-left"></i><span>Transactions</span></li>
      <li><i class="fa-solid fa-users"></i><span>Users</span></li>
      <li><i class="fa-solid fa-gear"></i><span>Settings</span></li>
      <li class="logout"><a href="logout.php"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></a></li>
    </ul>
  </div>

  <div class="main">
    <div class="topbar">
      <h2>Dashboard Overview</h2>
      <i class="fa-solid fa-bell bell"></i>
    </div>

    <div class="cards">
      <div class="card">
        <h4>Total Items In Stock</h4>
        <p>5</p>
      </div>
      <div class="card red">
        <h4>Low Stock Alerts</h4>
        <p>3</p>
      </div>
      <div class="card yellow">
        <h4>Pending Requests</h4>
        <p>15</p>
      </div>
      <div class="card">
        <h4>Total Value of Inventory</h4>
        <p>₱50,150</p>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      $(".toggle").click(function () {
        $(".sidebar").toggleClass("hide");
      });
    });
  </script>

</body>
</html>
