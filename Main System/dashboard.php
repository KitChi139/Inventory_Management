<?php
session_start();
include 'db_connect.php';
include 'inventory_functions.php';

// Fetch all inventory
$inventory = $conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC);

// Initialize counts
$total_items = count($inventory);
$in_stock_count = 0;
$low_stock_count = 0;
$out_stock_count = 0;
$total_value = 0;
$categories = [];
$expirations = [];

// Process inventory
foreach ($inventory as $item) {
    $quantity = (int)($item['quantity'] ?? 0);
    $max_stock = (int)($item['max_stock'] ?? 0);
    $price = (float)($item['price'] ?? 0);
    $category = $item['category'] ?: 'Uncategorized';
    $expiration = $item['expiration'] ?: null;

    // Total value
    $total_value += $quantity * $price;

    // Stock status logic
    if ($quantity === 0) {
        $out_stock_count++;
    } elseif ($max_stock > 0 && $quantity < $max_stock * 0.2) {
        $low_stock_count++;
    } else {
        $in_stock_count++;
    }

    // Categories
    $categories[$category] = ($categories[$category] ?? 0) + $quantity;

    // Expiration dates
    if ($expiration) $expirations[] = ['name' => $item['name'], 'date' => $expiration];
}

// Percentages
$in_stock_pct = $total_items ? round(($in_stock_count / $total_items) * 100) : 0;
$low_stock_pct = $total_items ? round(($low_stock_count / $total_items) * 100) : 0;
$out_stock_pct = $total_items ? round(($out_stock_count / $total_items) * 100) : 0;

// Sort expirations ascending
usort($expirations, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));

// Fetch pending requests
$pending_requests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='pending'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Overview</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="dashboard.css"/>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
    </ul>
</div>

<div class="main">
    <div class="topbar">
        <h2>Dashboard Overview</h2>
        <i class="fa-solid fa-bell bell"></i>
    </div>

    <!-- Dashboard Cards -->
    <div class="cards">
        <div class="card">
            <h4>Total Items In Stock</h4>
            <p><?= $total_items ?></p>
        </div>
        <div class="card red">
            <h4>Low Stock Alerts</h4>
            <p><?= $low_stock_count ?></p>
        </div>
        <div class="card yellow">
            <h4>Pending Requests</h4>
            <p><?= $pending_requests ?></p>
        </div>
        <div class="card blue">
            <h4>Total Value of Inventory</h4>
            <p>₱<?= number_format($total_value,2) ?></p>
        </div>
    </div>

    <!-- Stock Status Overview -->
    <div class="dashboard-grid">
        <div class="box stock-status">
            <h4>Stock Status Overview</h4>
            <div class="status-item">
                <div class="status-label">
                    <span>In Stock</span>
                    <span class="details"><?= $in_stock_pct ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress in-stock" style="width: <?= $in_stock_pct ?>%;"></div>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label">
                    <span>Low Stock</span>
                    <span class="details"><?= $low_stock_pct ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress low-stock" style="width: <?= $low_stock_pct ?>%;"></div>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label">
                    <span>Out of Stock</span>
                    <span class="details"><?= $out_stock_pct ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress out-of-stock" style="width: <?= $out_stock_pct ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Category Summary -->
        <div class="box category-summary">
            <h4>Category Summary</h4>
            <p>Inventory breakdown by category</p>
            <?php foreach ($categories as $cat => $totalQty): 
                $tag = $totalQty <= 5 ? 'low' : 'good'; ?>
                <p><?= htmlspecialchars($cat) ?> <span class="tag <?= $tag ?>"><?= strtoupper($tag) ?></span></p>
            <?php endforeach; ?>
        </div>

        <!-- Expiration Timeline -->
        <div class="box expiration">
            <h4>Expiration Timeline</h4>
            <p>Visual timeline of upcoming expiration dates</p>
            <table class="table">
                <?php foreach ($expirations as $exp): ?>
                <tr>
                    <td><?= htmlspecialchars($exp['name']) ?></td>
                    <td><?= date("m/d", strtotime($exp['date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="box quick-actions">
            <h4>Quick Actions</h4>
            <a href="inventory.php" class="btn"><i class="fa-solid fa-plus"></i> Add New Items</a>
            <a href="#" class="btn"><i class="fa-solid fa-bell"></i> View Alerts</a>
            <a href="#" class="btn"><i class="fa-solid fa-clock"></i> Pending Requests</a>
            <a href="#" class="btn"><i class="fa-solid fa-chart-bar"></i> Generate Reports</a>
        </div>
    </div>
</div>

<script>
$(function(){
    $(".toggle").click(()=>$(".sidebar").toggleClass("hide"));
    $("#inventory").click(()=>window.location.href="inventory.php");
});
</script>
</body>
</html>
