<?php
session_start();
include 'db_connect.php';

// Fetch all inventory items
$inventory = [];
$result = $conn->query("SELECT * FROM inventory");
while ($row = $result->fetch_assoc()) {
    $inventory[] = $row;
}

// Initialize counters
$total_items = count($inventory);
$low_stock = 0;
$in_stock_count = 0;
$out_stock_count = 0;
$total_value = 0;
$categories = [];
$expirations = [];

// Fetch pending requests (actual 'Pending' from your requests table)
$pending_requests = 0;
$resultRequests = $conn->query("
    SELECT COUNT(*) AS total
    FROM requests r
    JOIN inventory i ON r.item_id = i.id
    WHERE r.status = 'Pending'
");
if ($resultRequests && $rowReq = $resultRequests->fetch_assoc()) {
    $pending_requests = $rowReq['total'];
}

// Analyze inventory
foreach ($inventory as $item) {
    $quantity = (int)$item['quantity'];
    $max_stock = (int)$item['max_stock'];
    $category = $item['category'] ?? 'Uncategorized';
    $expiration = $item['expiration'] ?? null;
    $price = (float)$item['price'];

    $total_value += $quantity * $price;

    if ($quantity == 0) {
        $out_stock_count++;
    } elseif ($max_stock > 0 && $quantity <= ($max_stock * 0.2)) {
        $low_stock++;
    } else {
        $in_stock_count++;
    }

    if (!isset($categories[$category])) {
        $categories[$category] = 0;
    }
    $categories[$category] += $quantity;

    if ($expiration) {
        $expirations[] = [
            'name' => $item['name'],
            'date' => $expiration
        ];
    }
}

// Stock percentages
$in_stock_pct = $total_items ? round(($in_stock_count / $total_items) * 100) : 0;
$low_stock_pct = $total_items ? round(($low_stock / $total_items) * 100) : 0;
$out_stock_pct = $total_items ? round(($out_stock_count / $total_items) * 100) : 0;

// Sort expiration dates ascending
usort($expirations, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Dashboard</title>
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
            <h4>Total Items</h4>
            <p><?php echo $total_items; ?></p>
        </div>
        <div class="card red">
            <h4>Low Stock</h4>
            <p><?php echo $low_stock; ?></p>
        </div>
        <div class="card yellow">
            <h4>Pending Requests</h4>
            <p><?php echo $pending_requests; ?></p>
        </div>
        <div class="card">
            <h4>Total Inventory Value</h4>
            <p>₱<?php echo number_format($total_value, 2); ?></p>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="box stock-status">
            <h4>Stock Status Overview</h4>
            <div class="status-item">
                <div class="status-label">
                    <span>In Stock</span>
                    <span class="details"><?php echo $in_stock_pct; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress in-stock" style="width: <?php echo $in_stock_pct; ?>%;"></div>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label">
                    <span>Low Stock</span>
                    <span class="details"><?php echo $low_stock_pct; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress low-stock" style="width: <?php echo $low_stock_pct; ?>%;"></div>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label">
                    <span>Out of Stock</span>
                    <span class="details"><?php echo $out_stock_pct; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress out-stock" style="width: <?php echo $out_stock_pct; ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="box category-summary">
            <h4>Category Summary</h4>
            <p>Inventory by category</p>
            <?php foreach ($categories as $cat => $totalQty): ?>
                <?php $tag = $totalQty <= 5 ? 'low' : 'good'; ?>
                <p><?php echo htmlspecialchars($cat); ?> 
                    <span class="tag <?php echo $tag; ?>"><?php echo strtoupper($tag); ?></span>
                </p>
            <?php endforeach; ?>
        </div>

        <div class="box expiration">
            <h4>Expiration Timeline</h4>
            <table class="table">
                <?php foreach ($expirations as $exp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exp['name']); ?></td>
                        <td><?php echo date("M d, Y", strtotime($exp['date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="box quick-actions">
            <h4>Quick Actions</h4><br>
            <a href="inventory.php" class="btn"><i class="fa-solid fa-plus"></i> Add Item</a>
            <a href="#" class="btn"><i class="fa-solid fa-bell"></i> View Alerts</a>
            <a href="#" class="btn"><i class="fa-solid fa-clock"></i> Pending Requests</a>
            <a href="#" class="btn"><i class="fa-solid fa-chart-bar"></i> Generate Report</a>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $(".toggle").click(function () {
        $(".sidebar").toggleClass("hide");
    });

    $("#inventory").click(function(){
        window.location.href = "inventory.php";
    });
});
</script>
</body>
</html>
