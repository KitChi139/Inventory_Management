<?php
require 'db_connect.php';

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

// Auto-create requests table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    quantity INT NOT NULL,
    requester VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_approved TIMESTAMP NULL,
    FOREIGN KEY (ProductID) REFERENCES products(ProductID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Fetch inventory items for request list (low stock items)
// $sql = "SELECT 
//     i.InventoryID,
//     i.Quantity,
//     i.ExpirationDate,
//     p.ProductID,
//     p.ProductName,
//     c.Category_Name,
//     s.supplier_name,
//     i.BatchNum
// FROM inventory i
// JOIN products p ON p.ProductID = i.ProductID
// LEFT JOIN categories c ON c.CategoryID = p.CategoryID
// LEFT JOIN suppliers s ON s.supplier_id = p.CategoryID
// WHERE i.Quantity <= 10
// ORDER BY i.Quantity ASC, p.ProductName ASC";

$sql = "SELECT   
    r.BatchID,
    p.ProductID,
    p.ProductName,
    c.Category_Name,
    r.status,
    b.request_date,
    b.complete_date,
    r.quantity,
    com.companyName
FROM requests r
JOIN products p ON r.ProductID = p.ProductID
LEFT JOIN categories c ON c.CategoryID = p.CategoryID
LEFT JOIN batches b ON b.BatchID = r.BatchID
LEFT JOIN suppliers s on s.SupplierID = r.SupplierID
LEFT JOIN company com on com.comID = s.comID
ORDER BY r.BatchID DESC, p.ProductName ASC;";

$inventoryItems = $conn->query($sql);

// Get statistics
// $totalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 10")->fetch_assoc()['total'];
// $criticalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];

$totalItems = $conn->query("SELECT COUNT(*) AS total FROM requests")->fetch_assoc()['total'];
$criticalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];
// Get distinct suppliers for filter
$suppliers = $conn->query("SELECT DISTINCT CompanyName FROM company WHERE CompanyName IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request List</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="request_list.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" aria-label="Primary">
        <div class="profile">
            <div class="icon">
                <img src="logo.png?v=2" alt="MediSync Logo" class="medisync-logo">
            </div>
            <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
        </div>

        
        <ul class="menu">
            <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
            <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
            <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
            <li id="request" class="active"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
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
    <main class="main">
        <!-- Notification + Profile icon (top-right) -->
        <div class="topbar-right">
            <button class="messages-btn" onclick="window.location.href='message_list.php'">
                <i class="fa-solid fa-envelope"></i>
                <span>Messages</span>
            </button>
            <?php include 'notification_component.php'; ?>
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>

        <!-- Heading Bar -->
        <div class="heading-bar">
            <h1>Request List</h1>   
        </div>

        <!-- Statistics Cards -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-title">Total Items</div>
                <div class="stat-number"><?= $totalItems ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Selected Items</div>
                <div class="stat-number" id="selected-count">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Critical Items</div>
                <div class="stat-number"><?= $criticalItems ?></div>
            </div>
        </section>

        <!-- Filters -->
        <section class="filters">
            <input type="text" class="search-input" id="search-input" placeholder="Search batch number or item name...">
            <select class="filter-dropdown" id="supplier-filter">
                <option value="">All Suppliers</option>
                <?php while($s = $suppliers->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($s['CompanyName']) ?>"><?= htmlspecialchars($s['CompanyName']) ?></option>
                <?php endwhile; ?>
            </select>
            <select class="filter-dropdown" id="category-filter">
                <option value="">All Categories</option>
                  <?php
                  $catRes = $conn->query("SELECT Category_Name FROM categories ORDER BY Category_Name");
                  while ($cat = $catRes->fetch_assoc()):
                  ?>
                    <option value="<?= htmlspecialchars($cat['Category_Name']) ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
                  <?php endwhile; ?>
            </select>
            <button class="btn-clear" id="clear-filters">Clear Selection</button>
        </section>

        <!-- Main Layout: Single Column -->
        <section class="layout">
            <div class="requests-column">
                <div class="table-container">
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkbox" id="select-all"></th>
                                <th>Batch ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Quantity Requested</th>
                                <th>Status</th>
                                <th>Supplier</th>
                                <th>Requested Date</th>
                                <th>Completed Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($inventoryItems->num_rows == 0): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:20px; color:#777;">
                                        No low stock items found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <!-- <?php while($item = $inventoryItems->fetch_assoc()): 
                                    $qty = (int)$item['Quantity'];
                                    $status = $qty == 0 ? 'Critical' : ($qty <= 5 ? 'Critical' : 'Almost Low');
                                    $statusClass = $qty == 0 ? 'status-critical' : ($qty <= 5 ? 'status-critical' : 'status-almost-low');
                                ?> -->
                                    <tr data-supplier="<?= htmlspecialchars($item['companyName'] ?? '') ?>" data-status="<?= $status ?>">
                                        <td><input type="checkbox" class="row-checkbox" data-product-id="<?= $item['ProductID'] ?>" data-product-name="<?= htmlspecialchars($item['ProductName']) ?>"></td>
                                        <td><?= str_pad((int)$item['BatchID'], 4, "0", STR_PAD_LEFT) ?></td>
                                        <td><?= htmlspecialchars($item['ProductName']) ?></td>
                                        <td><?= htmlspecialchars($item['Category_Name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['quantity'] ?? '-') ?></td>
                                        <!-- <td><span class="status-badge <?= $statusClass ?>"><?= $status ?></span></td> -->
                                        <td><?= htmlspecialchars($item['status']) ?></td>
                                        <td><?= htmlspecialchars($item['companyName'] ?? 'N/A') ?></td>
                                        <!-- <td><?= !empty($item['ExpirationDate']) ? date('Y/m/d', strtotime($item['ExpirationDate'])) : 'N/A' ?></td> -->
                                        <td><?= htmlspecialchars($item['request_date'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($item['complete_date'] ?? 'N/A') ?></td>
                                        <td style="white-space: nowrap;">
                                            <button class="btn action-btn"
                                            data-productid="<?= (int)$item['ProductID'] ?>"
                                            data-productname="<?= htmlspecialchars($item['ProductName']) ?>">
                                        Mark Complete
                                        </button>
                                        </td>
                                    </tr>
                                <!-- <?php endwhile; ?> -->
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-actions">
                    <button class="btn-secondary" id="clear-selection">Clear Selection</button>
                    <button class="btn-primary" id="create-purchase-request">Create Purchase Request</button>
                </div>
            </div>
        </section>
    </main>

    <!-- Contact Modal -->
    <div class="modal" id="contact-modal" style="display:none;">
        <div class="modal-content">
            <h2>Contact Supplier</h2>
            <p class="modal-subtitle">Send a message to the supplier</p>
            <form class="modal-form" id="contact-form">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" id="contact-subject" placeholder="Enter subject" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea id="contact-message" placeholder="Enter your message" rows="5" required></textarea>
                </div>
                <div class="supplier-info">
                    <strong>Supplier:</strong> <span id="modal-supplier"></span><br>
                    <strong>Batch:</strong> <span id="modal-batch"></span>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="close-modal">Cancel</button>
                    <button type="submit" class="btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>

    <script src="sidebar.js"></script>
    <script>

        // Select all checkbox
        document.getElementById('select-all').addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateSelectedCount();
        });

        // Row checkbox change
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const selected = document.querySelectorAll('.row-checkbox:checked').length;
            document.getElementById('selected-count').textContent = selected;
        }

        // Search and filter
        // Search and filter
        $('#search-input').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            const category = $('#category-filter').val().toLowerCase();
            const status = $('#status-filter').val()?.toLowerCase() || '';
            
            $('.request-table tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                const rowCategory = $(this).find('td:nth-child(4)').text().toLowerCase(); // Category column
                const rowStatus = $(this).data('status')?.toLowerCase() || '';
                
                const matchesSearch = text.includes(search);
                const matchesCategory = !category || rowCategory.includes(category);
                const matchesStatus = !status || rowStatus.includes(status);
                
                $(this).toggle(matchesSearch && matchesCategory && matchesStatus);
            });
        });

        $('#category-filter, #status-filter').on('change', function() {
            $('#search-input').trigger('keyup');
        });

        $('#clear-filters').on('click', function() {
            $('#search-input').val('');
            $('#category-filter').val('');
            $('#status-filter').val('');
            $('#search-input').trigger('keyup');
        });


        // Clear selection
        $('#clear-selection').on('click', function() {
            $('.row-checkbox').prop('checked', false);
            $('#select-all').prop('checked', false);
            updateSelectedCount();
        });

        // Create purchase request
        $('#create-purchase-request').on('click', function() {
            const selected = [];
            $('.row-checkbox:checked').each(function() {
                selected.push({
                    productId: $(this).data('product-id'),
                    productName: $(this).data('product-name')
                });
            });
            
            if (selected.length === 0) {
                alert('Please select at least one item to create a purchase request.');
                return;
            }
            
            // Send to bulk request handler
            $.ajax({
                url: 'request_stock_bulk.php',
                method: 'POST',
                data: { items: selected },
                success: function(response) {
                    alert('Purchase request created successfully!');
                    location.reload();
                },
                error: function() {
                    alert('Error creating purchase request. Please try again.');
                }
            });
        });

        // Modal functionality
        const closeModalBtn = document.getElementById('close-modal');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                document.getElementById('contact-modal').style.display = 'none';
            });
        }

        // Close modal on outside click
        document.getElementById('contact-modal').addEventListener('click', (e) => {
            if (e.target.id === 'contact-modal') {
                document.getElementById('contact-modal').style.display = 'none';
            }
        });

        // Sidebar toggle
        $("#toggleBtn").click(function() {
            $("#sidebar").toggleClass("hide");
        });

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
