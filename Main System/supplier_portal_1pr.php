<?php
require_once 'db_connect.php';

$supplier_id = $_SESSION['SupplierID']; // supplier logged in

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
    r.request_id,
    r.BatchID,
    r.ProductID,
    p.ProductName,
    c.Category_Name,
    r.quantity,
    r.BatchNum,
    r.ExpirationDate,
    r.status,
    b.request_date,
    b.shipping_date,
    b.complete_date
FROM requests r
JOIN products p ON r.ProductID = p.ProductID
LEFT JOIN categories c ON c.CategoryID = p.CategoryID
LEFT JOIN batches b ON b.BatchID = r.BatchID
WHERE r.SupplierID = ? AND r.status = 'Pending'
ORDER BY r.BatchID DESC, p.ProductName ASC;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$inventoryItems = $stmt->get_result();


// Get statistics
// $totalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 10")->fetch_assoc()['total'];
// $criticalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];

$totalItems = $conn->query("SELECT COUNT(*) AS total FROM requests")->fetch_assoc()['total'];
$criticalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];
// Get distinct suppliers for filter
// $suppliers = $conn->query("SELECT DISTINCT supplier_name FROM suppliers WHERE supplier_name IS NOT NULL");

if (isset($_POST['approve_request'])) {
    $batchId = (int)$_POST['BatchID'];
    $products = $_POST['products'] ?? [];
    $shippingDate = $_POST['shipping_date'];
    $expirationDate = $_POST['expiration_date'];
    $batchNum = $_POST['auto_batch_num'];

    if (!empty($products)) {
        foreach ($products as $productId => $qty) {
            $stmt = $conn->prepare(
                "UPDATE requests 
                 SET status='Approved', 
                     date_approved=NOW(),
                     BatchNum=?, 
                     ExpirationDate=? 
                 WHERE BatchID=? 
                   AND ProductID=?"
            );
            $stmt->bind_param("ssii", $batchNum, $expirationDate, $batchId, $productId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update the batch shipping date (overwrite if user changes it)
    $stmt = $conn->prepare("UPDATE batches SET shipping_date=? WHERE BatchID=?");
    $stmt->bind_param("si", $shippingDate, $batchId);
    $stmt->execute();
    $stmt->close();

    header("Location: supplier_portal_1pr.php?approved=1");
    exit();
}


if (isset($_POST['decline_request'])) {
    $batchId = (int)$_POST['BatchID'];
    $products = json_decode($_POST['products'], true); // decode JSON array

    foreach ($products as $productId => $qty) {
        $stmt = $conn->prepare(
            "UPDATE requests 
             SET status='Rejected', date_declined=NOW()
             WHERE BatchID=? AND ProductID=?"
        );
        $stmt->bind_param("ii", $batchId, $productId);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: supplier_portal_1pr.php?declined=1");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Portal - MediSync</title>
    <link rel="stylesheet" href="supplier_portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            <button class="icon-btn notification-btn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <button class="icon-btn profile-btn">
                <i class="fas fa-user-circle"></i>
            </button>
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
        <button id="pr" class="tab-link active" data-tab="pending-requests">
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
        <button id="cr" class="tab-link" data-tab="completed-requests">
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
        <!-- Pending Requests Section -->
            <section class="content" id="pending-requests-section">
            <div class="page-header">
                <h1>Pending Requests</h1>
            </div>
            <!-- Filter bar -->
        <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search batch ID, item name, or quantity..." />
        <select id="categoryFilter">
            <option value="">All Categories</option>
            <option value="Protective Equipment">Protective Equipment</option>
            <option value="Antibiotics / Antibacterials">Antibiotics / Antibacterials</option>
            <option value="Analgesics / Antipyretics">Analgesics / Antipyretics</option>
            <option value="Antivirals">Antivirals</option>
            <option value="Antifungals">Antifungals</option>
            <option value="Antihistamines / Antiallergics">Antihistamines / Antiallergics</option>
            <option value="Antacids / Antiulcerants">Antacids / Antiulcerants</option>
        </select>
        <!-- Optional: include only if you want date filtering -->
        <input type="date" id="dateFilter" />
        <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
        </div>
                
        <div class="table-container">
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
                                <?php while($item = $inventoryItems->fetch_assoc()): ?>
<tr>
    <td><?= str_pad((int)$item['BatchID'], 4, "0", STR_PAD_LEFT) ?></td>
    <td><?= htmlspecialchars($item['ProductName']) ?></td>
    <td><?= htmlspecialchars($item['Category_Name'] ?? '-') ?></td>
    <td><?= htmlspecialchars($item['quantity'] ?? '-') ?></td>
    <td><?= date("m/d/Y h:i A", strtotime($item['request_date'])) ?></td>
    <td style="white-space: nowrap;">
        <button class="btn approve-btn"
            data-productid="<?= (int)$item['ProductID'] ?>"
            data-productname="<?= htmlspecialchars($item['ProductName']) ?>"
            data-batchid="<?= (int)$item['BatchID'] ?>"
            data-shippingdate="<?= $item['shipping_date'] ? date('Y-m-d', strtotime($item['shipping_date'])) : '' ?>">
            Approve
        </button>
        <button class="btn decline-btn"
            data-productid="<?= (int)$item['ProductID'] ?>"
            data-productname="<?= htmlspecialchars($item['ProductName']) ?>"
            data-batchid="<?= (int)$item['BatchID'] ?>">
            Decline
        </button>
    </td>
</tr>
<?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        </section>
        <!-- Approve Request Modal -->
        <div id="approveModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close-approve">&times;</span>
                <h3>Approve Request</h3>

                <form action="supplier_portal_1pr.php" method="POST">
                    <input type="hidden" name="BatchID" id="approve-batch-id">
                    <input type="hidden" name="auto_batch_num" id="auto-batch-num">

                    <<label><strong>Shipping Date</strong></label>
                    <input type="date" name="shipping_date" id="shipping_date" required>
                    <small id="shipping-warning" style="color:#d9534f; display:none;">
                        Changing the shipping date will affect all items in this batch.
                    </small>

                    <label><strong>Expiration Date</strong></label>
                    <input type="date" name="expiration_date" id="expiration_date" required>

                    <label><strong>Batch Number</strong></label>
                    <input type="text" name="batch_num" id="batch_num" readonly placeholder="Auto-generated">
                    <button type="submit" name="approve_request" class="btn btn-primary" style="margin-top:10px;">
                        Confirm Approval
                    </button>
                </form>
            </div>
        </div>
        <!-- Decline Confirmation Modal -->
            <div id="declineModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span class="close-decline">&times;</span>
                    <h3>Confirm Decline</h3>
                    <p>Are you sure you want to decline this request?</p>

                    <form id="declineForm" method="POST">
                        <input type="hidden" name="BatchID" id="decline-batch-id">
                        <input type="hidden" name="products" id="decline-products">
                        <button type="submit" name="decline_request" class="btn btn-danger">Yes, Decline</button>
                        <button type="button" class="btn btn-secondary close-decline">Cancel</button>
                    </form>
                </div>
            </div>
    </main>
</body>
<script>
    
    // When shipping date or expiration date changes, generate batch number
    function generateBatchNumFor(productName) {
        const expirationDate = $('#expiration_date').val();
        if (!expirationDate) return;

        // First four letters of product name, uppercase
        let prefix = productName.replace(/\s/g,'').substring(0,4).toUpperCase();
        
        // Expiration date in YYYYMMDD format
        let exp = expirationDate.replace(/-/g,''); 
        
        $.post('supplier_portal_generate.php', { prefix: prefix, exp: exp }, function(seqNum){
            seqNum = seqNum.trim().padStart(4,'0');
            let finalBatch = `${prefix}-${exp}-${seqNum}`; // hyphens added
            $('#batch_num').val(finalBatch);
            $('#auto-batch-num').val(finalBatch);
        });
    }

    // Bind events
    $('#expiration_date, #shipping_date').on('change', function() {
        const productName = $('#selected-product-name').val();
        generateBatchNumFor(productName);
    });

    // $('.row-checkbox').on('change', generateBatchNum);
    // Row Checkbox
    // $(document).on('change', '.row-checkbox', function () {
    //     if (this.checked) {
    //         // Get current batch ID of clicked checkbox
    //         const selectedBatch = $(this).closest('tr').find('td:nth-child(2)').text().trim();

    //         // Uncheck all checkboxes from different batch
    //         $('.row-checkbox').each(function () {
    //             const batch = $(this).closest('tr').find('td:nth-child(2)').text().trim();
    //             if (batch !== selectedBatch) {
    //                 $(this).prop('checked', false);
    //             }
    //         });
    //     }
    // });
    //Modal Approve
    $('.approve-btn').on('click', function() {
        const productId = $(this).data('productid');
        const productName = $(this).data('productname');
        const batchId = $(this).data('batchid');
        const shippingDate = $(this).data('shippingdate'); // <- get date

        // Set hidden inputs for modal
        $('#approve-batch-id').val(batchId);
        $('#auto-batch-num').val('');
        $('#batch_num').val('');

        // Store product ID
        $('#approveModal form .selected-product').remove();
        $('#approveModal form').append(`
            <input type="hidden" name="products[${productId}]" value="1" class="selected-product">
            <input type="hidden" id="selected-product-name" value="${productName}">
        `);

        // Pre-fill shipping date if exists
        if (shippingDate) {
            $('#shipping_date').val(shippingDate);
            $('#shipping-warning').show();
        } else {
            $('#shipping-warning').hide();
            $('#shipping_date').val('');
        }

        $('#approveModal').show();

        generateBatchNumFor(productName);
    });
    // Close approve modal
    $('.close-approve').on('click', function() {
        $('#approveModal').hide();
        $('#approveModal form .selected-product').remove();
        $('#batch_num').val('');
        $('#auto-batch-num').val('');
        $('#shipping_date').val('');
        $('#expiration_date').val('');
    });

    // Also close modal if user clicks outside modal-content
    $(window).on('click', function(e) {
        if ($(e.target).is('#approveModal')) {
            $('#approveModal').hide();
        }
    });

    // Modal or inline decline
    // Trigger decline modal
    $('.decline-btn').on('click', function() {
        const productId = $(this).data('productid');
        const batchId = $(this).data('batchid');

        // Set hidden inputs in the modal
        $('#decline-batch-id').val(batchId);
        $('#decline-products').val(JSON.stringify({ [productId]: 1 }));

        // Show modal
        $('#declineModal').show();
    });

    // Close modal on X or cancel
    $('.close-decline').on('click', function() {
        $('#declineModal').hide();
    });

    // Close modal if click outside content
    $(window).on('click', function(e) {
        if ($(e.target).is('#declineModal')) {
            $('#declineModal').hide();
        }
    });
    $(document).ready(function () {
    const today = new Date().toISOString().split('T')[0];
    $('#shipping_date, #expiration_date').attr('min', today);

    //Navigation
    $("#db").click(function(){ window.location.href = "supplier_portal_db.php";});
    $("#dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
    $("#ar").click(function(){ window.location.href = "supplier_portal_3ar.php";});
    $("#cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
    $("#m").click(function(){ window.location.href = "supplier_portal_m.php"; });
    $("#cp").click(function(){ window.location.href = "supplier_portal_cp.php"; });
});
</script>
</html>