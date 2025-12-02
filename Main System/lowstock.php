<?php
require 'db_connect.php';

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

/* ---------------------------------------------------------
   FETCH LOW & OUT-OF-STOCK ITEMS
--------------------------------------------------------- */
$sql = "
  SELECT 
    i.InventoryID,
    i.Quantity,
    i.Status,
    i.ExpirationDate,
    i.BatchNum,
    p.ProductName,
    p.Min_stock,
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alerts</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="lowstock.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="profile">
            <div class="icon">
                <img src="logo.png?v=2" alt="MediSync Logo" class="medisync-logo">
            </div>
            <!-- Toggle button -->
            <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
        </div>


        <ul class="menu">
            <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
            <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
            <li id="low-stock" class="active"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
            <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
            <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
            <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
            <?php if ($_SESSION['roleName'] === 'Admin'): ?>
            <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
            <?php endif; ?>   
            <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
            <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main low-stock-main">
        <!-- Notification + Profile icon (top-right in main content) -->
        <div class="topbar-right">
            <?php include 'notification_component.php'; ?>
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>

        <!-- Heading Bar -->
        <div class="heading-bar">
            <h1>Low Stock Alerts</h1>   
        </div>

        <!-- Statistics Cards -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-title">High Priority</div>
                <div class="stat-number"><?= $highCount ?></div>
                <div class="stat-subtext">Require immediate action</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Medium Priority</div>
                <div class="stat-number"><?= $mediumCount ?></div>
                <div class="stat-subtext">Need attention soon</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Alerts</div>
                <div class="stat-number"><?= $totalAlerts ?></div>
                <div class="stat-subtext">Active Alerts</div>
            </div>
        </section>

        <!-- High Priority Alerts -->
        <section class="alerts-section">
            <div class="section-header">
                <div class="section-title-group">
                    <h2>High Priority Alerts</h2>
                    <p class="section-subtitle">These items require immediate attention</p>
                </div>
                <div class="section-filters">
                    <button class="filter-icon"><i class="fa-solid fa-filter"></i></button>
                    <input type="text" class="search-input" id="search-high" placeholder="Search items...">
                </div>
            </div>

            <div class="alerts-list" id="high-priority-list">
                <?php if (empty($highPriorityItems)): ?>
                    <div class="alert-card placeholder">
                        <div class="alert-content">
                            <div class="alert-main">
                                <div class="alert-item-name">No high priority alerts 🎉</div>
                                <div class="alert-company">All items are well stocked</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($highPriorityItems as $item): 
                        $qty = (int)$item['Quantity'];
                        $statusTag = $qty == 0 ? 'out-of-stock' : 'low-stock';
                        $statusText = $qty == 0 ? 'Out of Stock' : 'Low Stock';
                    ?>
                        <div class="alert-card" data-name="<?= strtolower(htmlspecialchars($item['ProductName'])) ?>">
                            <div class="alert-content">
                                <div class="alert-main">
                                    <div class="alert-item-name"><?= htmlspecialchars($item['ProductName']) ?></div>
                                    <div class="alert-company"><?= htmlspecialchars($item['SupplierName'] ?? 'N/A') ?></div>
                                    <div class="alert-supplier">Supplier: <?= htmlspecialchars($item['SupplierName'] ?? 'N/A') ?></div>
                                    <div class="alert-stock-info">
                                        <span>Current: <?= $qty ?> <?= htmlspecialchars($item['Unit']) ?> | Min Required: <?= htmlspecialchars($item['Min_stock'] ?? 5) ?> <?= htmlspecialchars($item['Unit']) ?></span>
                                    </div>
                                </div>
                                <div class="alert-actions">
                                    <span class="status-tag <?= $statusTag ?>"><?= $statusText ?></span>
                                    <span class="priority-tag high-priority">High Priority</span>
                                    <button class="action-btn" onclick="window.location.href='Inventory.php'">View Items</button>
                                    <button class="action-btn" onclick="window.location.href='supplier.php'">Contact Suppliers</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Medium Priority Alerts -->
        <section class="alerts-section">
            <div class="section-header">
                <div class="section-title-group">
                    <h2>Medium Priority Alerts</h2>
                    <p class="section-subtitle">These items need attention in the near future</p>
                </div>
                <div class="section-filters">
                    <button class="filter-icon"><i class="fa-solid fa-filter"></i></button>
                    <input type="text" class="search-input" id="search-medium" placeholder="Search items...">
                </div>
            </div>

            <div class="alerts-list" id="medium-priority-list">
                <?php if (empty($mediumPriorityItems)): ?>
                    <div class="alert-card placeholder">
                        <div class="alert-content">
                            <div class="alert-main">
                                <div class="alert-item-name">No medium priority alerts 🎉</div>
                                <div class="alert-company">All items are well stocked</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($mediumPriorityItems as $item): 
                        $qty = (int)$item['Quantity'];
                    ?>
                        <div class="alert-card" data-name="<?= strtolower(htmlspecialchars($item['ProductName'])) ?>">
                            <div class="alert-content">
                                <div class="alert-main">
                                    <div class="alert-item-name"><?= htmlspecialchars($item['ProductName']) ?></div>
                                    <div class="alert-company"><?= htmlspecialchars($item['SupplierName'] ?? 'N/A') ?></div>
                                    <div class="alert-supplier">Supplier: <?= htmlspecialchars($item['SupplierName'] ?? 'N/A') ?></div>
                                    <div class="alert-stock-info">
                                        <span>Current: <?= $qty ?> <?= htmlspecialchars($item['Unit']) ?> | Min Required: <?= htmlspecialchars($item['Min_stock'] ?? 5) ?> <?= htmlspecialchars($item['Unit']) ?></span>
                                    </div>
                                </div>
                                <div class="alert-actions">
                                    <span class="status-tag low-stock">Low Stock</span>
                                    <span class="priority-tag medium-priority">Medium Priority</span>
                                    <button class="action-btn" onclick="window.location.href='Inventory.php'">View Items</button>
                                    <button class="action-btn" onclick="window.location.href='supplier.php'">Contact Suppliers</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="sidebar.js"></script>
    <script>
        // Search functionality
        $('#search-high').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            $('#high-priority-list .alert-card').each(function() {
                const name = $(this).data('name') || '';
                $(this).toggle(name.includes(search));
            });
        });

        $('#search-medium').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            $('#medium-priority-list .alert-card').each(function() {
                const name = $(this).data('name') || '';
                $(this).toggle(name.includes(search));
            });
        });

        // Sidebar toggle handled by sidebar.js

        // Navigation handlers
        $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
        $("#inventory").click(function(){ window.location.href = "Inventory.php"; });
        $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });
        $("#request").click(function(){ window.location.href = "request_list.php"; });
        $("#nav-suppliers").click(function(){ window.location.href = "supplier.php"; });
        $("#reports").click(function(){ window.location.href = "report.php"; });
        $("#users").click(function(){ window.location.href = "admin.php"; });
        $("#settings").click(function(){ window.location.href = "settings.php"; });
        $("#logout").click(function(){ window.location.href = "logout.php"; });
    </script>
    <script src="notification.js" defer></script>
</body>
</html>
