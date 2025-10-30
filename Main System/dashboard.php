<?php
session_start();

// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     header("Location: login.php");
//     exit();  
// }

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
  <title>Dashboard Overview</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="dashboard.css" />
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
      <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
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

<div class="dashboard-grid">
  <div class="box stock-status">
    <h4>Stock Status Overview</h4>

    <div class="status-item">
      <div class="status-label">
        <span>In Stock</span>
        <span class="details">40%</span>
      </div>
      <div class="progress-bar">
        <div class="progress in-stock" style="width: 40%;"></div>
      </div>
    </div>

    <div class="status-item">
      <div class="status-label">
        <span>Low Stock</span>
        <span class="details">60%</span>
      </div>
      <div class="progress-bar">
        <div class="progress low-stock" style="width: 60%;"></div>
      </div>
    </div>

    <div class="status-item">
      <div class="status-label">
        <span>Out of Stock</span>
        <span class="details">0%</span>
      </div>
      <div class="progress-bar">
        <div class="progress out-stock" style="width: 0%;"></div>
      </div>
    </div>
  </div>

  <div class="box category-summary">
    <h4>Category Summary</h4>
    <p>Inventory breakdown by category</p>
    <p>Category 1 <span class="tag low">LOW</span></p>
    <p>Category 2 <span class="tag low">LOW</span></p>
    <p>Category 3 <span class="tag good">GOOD</span></p>
  </div>

  <div class="box expiration">
    <h4>Expiration Timeline</h4>
    <p>Visual timeline of upcoming expiration dates</p>
    <table class="table">
      <tr><td>PCM</td><td>09/21</td></tr>
      <tr><td>AMX</td><td>10/15</td></tr>
      <tr><td>MET</td><td>11/25</td></tr>
      <tr><td>ASA</td><td>12/25</td></tr>
      <tr><td>OME</td><td>11/01</td></tr>
    </table>
  </div>

  <div class="box quick-actions">
    <h4>Quick Actions</h4>
    <p>Common tasks and shortcuts</p><br>
    <a href="#" class="btn"><i class="fa-solid fa-plus"></i> Add New Items</a>
    <a href="#" class="btn"><i class="fa-solid fa-bell"></i> View Alerts</a>
    <a href="#" class="btn"><i class="fa-solid fa-clock"></i> Pending Requests</a>
    <a href="#" class="btn"><i class="fa-solid fa-chart-bar"></i> Generate Reports</a>
  </div>
</div>

  <script>
    $(document).ready(function () {
      $(".toggle").click(function () {
        $(".sidebar").toggleClass("hide");
      });
      
      //Navigation
      $("#inventory").click(function(){
        window.location.href = "Inventory.php";
      });
    });
  </script>

</body>
</html>
