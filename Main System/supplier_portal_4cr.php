<?php
require_once 'db_connect.php';
$supplier_id = $_SESSION['SupplierID'];

// Fetch approved requests with shipping date
$sql = "SELECT
        r.request_id,
        r.BatchID,
        r.ProductID,
        p.ProductName,
        c.Category_Name,
        r.quantity,
        b.request_date,
        r.date_completed,
        r.BatchNum
    FROM requests r
    JOIN products p ON r.ProductID = p.ProductID
    LEFT JOIN categories c ON c.CategoryID = p.CategoryID
    LEFT JOIN batches b ON b.BatchID = r.BatchID
    WHERE r.SupplierID = ? AND r.status = 'Completed'
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
    <!-- Top Navigation Bar -->
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

    <!-- Secondary Navigation Tabs -->
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
        <button id="ar" class="tab-link" data-tab="approved-requests">
            <i class="fas fa-check-circle"></i>
            <span>Approved Requests</span>
        </button>
        <button id="cr" class="tab-link active" data-tab="completed-requests">
            <i class="fas fa-clipboard-check"></i>
            <span>Completed Requests</span>
        </button>
        <!-- <button id="m" class="tab-link" data-tab="messages">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </button> -->
        <!-- <button id="cp" class="tab-link" data-tab="company-profile">
            <i class="fas fa-building"></i>
            <span>Company Profile</span>
        </button> -->
    </nav>
        <!-- Main Content Area -->
            <main class="main-content">
            <!-- Completed Requests Section -->
            <section class="content" id="completed-requests-section">
            <div class="page-header">
                <h1>Completed Requests</h1>
            </div>
            <!-- Filter bar -->
        <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search word..." />
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
                        <th>Completed Date</th>
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
                            <td><?= $item['date_completed'] ? date("Y-m-d h:i A", strtotime($item['date_completed'])) : '-' ?></td>
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

// Trigger search on input
$('#searchInput').on('input', filterTable);

// Clear button
function clearFilters() {
    $('#searchInput').val('');
    filterTable();
}
    $(document).ready(function () {
        //Navigation
        $("#db").click(function(){ window.location.href = "supplier_portal_db.php";});
        $("#pr").click(function(){ window.location.href = "supplier_portal_1pr.php";});
        $("#dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
        $("#ar").click(function(){ window.location.href = "supplier_portal_3ar.php";});
        // $("#cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
        $("#m").click(function(){ window.location.href = "supplier_portal_m.php"; });
        $("#cp").click(function(){ window.location.href = "supplier_portal_cp.php"; });
    });
</script>
</html>