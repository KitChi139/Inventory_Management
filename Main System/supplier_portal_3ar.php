<?php
require_once 'db_connect.php';
$supplier_id = $_SESSION['SupplierID'];

$sql = "SELECT
        r.request_id,
        r.BatchID,
        r.ProductID,
        p.ProductName,
        c.Category_Name,
        r.quantity,
        b.request_date,
        b.shipping_date,
        r.BatchNum
    FROM requests r
    JOIN products p ON r.ProductID = p.ProductID
    LEFT JOIN categories c ON c.CategoryID = p.CategoryID
    LEFT JOIN batches b ON b.BatchID = r.BatchID
    WHERE r.SupplierID = ? AND r.status = 'Approved'
    ORDER BY r.BatchID DESC, p.ProductName ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$approvedRequests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Portal - MediSync</title>
    <link rel="stylesheet" href="styles/supplier_portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>   
    <script src="supplier_portal.js" defer></script>
</head>
<body>
<<<<<<< HEAD
=======

>>>>>>> 2d7145306ffce975d8498d07fdbb32884df67c94
    <header class="top-nav">
        <div class="nav-left">
            <div class="logo-container">
                <img src="logo.png" alt="MediSync Logo" class="logo-img">
            </div>
        </div>
        <div class="nav-right">
           
            <button class="logout-button" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </header>
<<<<<<< HEAD

=======
>>>>>>> 2d7145306ffce975d8498d07fdbb32884df67c94
    <nav class="tab-navigation">
        <button id="db" class="tab-link" data-tab="dashboard">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </button>
        <button id="pr" class="tab-link" data-tab="pending-requests">
            <i class="fas fa-hourglass-half"></i>
            <span>Pending Requests</span>
        </button>
        <button id="dr" class="tab-link" data-tab="declined-requests">
            <i class="fas fa-times-circle"></i>
            <span>Declined Requests</span>
        </button>
        <button id="ar" class="tab-link active" data-tab="approved-requests">
            <i class="fas fa-check-circle"></i>
            <span>Approved Requests</span>
        </button>
        <button id="cr" class="tab-link" data-tab="completed-requests">
            <i class="fas fa-clipboard-check"></i>
            <span>Completed Requests</span>
        </button>
    </nav>
<<<<<<< HEAD
=======

>>>>>>> 2d7145306ffce975d8498d07fdbb32884df67c94
    <main class="main-content">
    <section class="content" id="approved-requests-section">
        <div class="page-header">
            <h1>Approved Requests</h1>
        </div>
<<<<<<< HEAD
=======

>>>>>>> 2d7145306ffce975d8498d07fdbb32884df67c94
    <div class="filter-bar">
    <input type="text" id="searchInput" placeholder="Search Word..." />
    <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
    </div>
        
    <div class="requests-column">
        <div class="table-container">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Batch ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Requested Date</th>
                        <th>Shipping Date</th>
                    </tr>
                </thead>
                <tbody id="approvedTableBody">
                <?php if ($approvedRequests->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color:#777;">No approved requests found.</td>
                    </tr>
                <?php else: ?>
                    <?php while($item = $approvedRequests->fetch_assoc()): ?>
                        <tr>
                            <td><?= str_pad((int)$item['BatchID'], 4, "0", STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($item['ProductName']) ?></td>
                            <td><?= htmlspecialchars($item['Category_Name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= date("m/d/Y h:i A", strtotime($item['request_date'])) ?></td>
                            <td><?= $item['shipping_date'] ? date("Y-m-d h:i A", strtotime($item['shipping_date'])) : '-' ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
            
        </div>
    </section>
    </main>
</body>
<script>
    function filterTable() {
    const searchText = $('#searchInput').val().toLowerCase();

    $('.request-table tbody tr').each(function() {
        const rowText = $(this).text().toLowerCase();
        $(this).toggle(rowText.includes(searchText));
    });
}

$('#searchInput').on('input', filterTable);

function clearFilters() {
    $('#searchInput').val('');
    filterTable();
}
    $(document).ready(function () {
        $("#db").click(function(){ window.location.href = "supplier_portal_db.php";});
        $("#pr").click(function(){ window.location.href = "supplier_portal_1pr.php";});
        $("#dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
        $("#cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
        $("#m").click(function(){ window.location.href = "supplier_portal_m.php"; });
        $("#cp").click(function(){ window.location.href = "supplier_portal_cp.php"; });
    });
</script>
</html>
