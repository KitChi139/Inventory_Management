<?php
require_once 'db_connect.php';

$supplier_id = $_SESSION['SupplierID']; // supplier logged in

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
WHERE r.SupplierID = ?
ORDER BY b.request_date DESC, r.request_id DESC
LIMIT 10";  // optional: limit to 10 most recent

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$inventoryItems = $stmt->get_result();




$totalItems = $conn->query("SELECT COUNT(*) AS total FROM requests")->fetch_assoc()['total'];
$criticalItems = $conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];

$currentMonth = date('m');
$currentYear = date('Y');

$totalRequests = $conn->query("SELECT COUNT(*) AS total 
    FROM requests r
    JOIN batches b on b.batchID = r.BatchID
    WHERE SupplierID = $supplier_id 
    AND MONTH(b.request_date) = $currentMonth 
    AND YEAR(b.request_date) = $currentYear")->fetch_assoc()['total'] ?? 0;

$pendingRequests = $conn->query("SELECT COUNT(*) AS total 
    FROM requests 
    WHERE SupplierID = $supplier_id 
    AND status = 'Pending'")->fetch_assoc()['total'] ?? 0;

$approvedRequests = $conn->query("SELECT COUNT(*) AS total 
    FROM requests 
    WHERE SupplierID = $supplier_id 
    AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;

$completedRequests = $conn->query("SELECT COUNT(*) AS total 
    FROM requests 
    WHERE SupplierID = $supplier_id 
    AND status = 'Completed'")->fetch_assoc()['total'] ?? 0;

$totalRequestsAll = $conn->query("SELECT COUNT(*) AS total 
    FROM requests 
    WHERE SupplierID = $supplier_id")->fetch_assoc()['total'] ?? 0;

// Declined requests
$declinedRequests = $conn->query("SELECT COUNT(*) AS total 
    FROM requests 
    WHERE SupplierID = $supplier_id 
    AND status = 'Declined'")->fetch_assoc()['total'] ?? 0;

// Calculate percentages
$completedPercent = $totalRequestsAll ? round(($completedRequests / $totalRequestsAll) * 100) : 0;
$approvedPercent = $totalRequestsAll ? round(($approvedRequests / $totalRequestsAll) * 100) : 0;
$pendingPercent  = $totalRequestsAll ? round(($pendingRequests / $totalRequestsAll) * 100) : 0;
$declinedPercent  = $totalRequestsAll ? round(($declinedRequests / $totalRequestsAll) * 100) : 0;

$currentMonth = date('m');
$currentYear = date('Y');

// Initialize counts
$statusCounts = [
    'Pending' => 0,
    'Approved' => 0,
    'Declined' => 0,
    'Completed' => 0
];

// Query counts per status for this supplier for current month
$statusQuery = $conn->query("
    SELECT b.status, COUNT(*) AS total
    FROM requests r
    JOIN batches b ON b.BatchID = r.BatchID
    WHERE r.SupplierID = $supplier_id
      AND MONTH(b.request_date) = $currentMonth
      AND YEAR(b.request_date) = $currentYear
    GROUP BY status
");

while ($row = $statusQuery->fetch_assoc()) {
    $statusCounts[$row['status']] = (int)$row['total'];
}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script src="supplier_portal.js" defer></script>
</head>
<body>
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

    <nav class="tab-navigation">
        <button id="db" class="tab-link active" data-tab="dashboard">
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
        <button id="cr" class="tab-link" data-tab="completed-requests">
            <i class="fas fa-clipboard-check"></i>
            <span>Completed Requests</span>
        </button>

    </nav>

    <main class="main-content">
        <section class="content" id="dashboard-section">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
            </div>

            <div class="summary-grid">
                <div class="stat-card yellow-card">
    <div class="stat-header">
        <span class="stat-label">Total Requests</span>
        <i class="fas fa-box stat-icon"></i>
    </div>
    <div class="stat-value"><?php echo $totalRequests; ?></div>
    <div class="stat-footer">This Month</div>
</div>

<div class="stat-card red-card">
    <div class="stat-header">
        <span class="stat-label">Pending Requests</span>
        <i class="fas fa-clock stat-icon"></i>
    </div>
    <div class="stat-value"><?php echo $pendingRequests; ?></div>
    <div class="stat-footer">Awaiting Response</div>
</div>

<div class="stat-card blue-card">
    <div class="stat-header">
        <span class="stat-label">Approved Requests</span>
        <i class="fas fa-envelope stat-icon"></i>
    </div>
    <div class="stat-value"><?php echo $approvedRequests; ?></div>
    <div class="stat-footer">Awaiting Completion</div>
</div>

<div class="stat-card green-card">
    <div class="stat-header">
        <span class="stat-label">Completed Requests</span>
        <i class="fas fa-clipboard-check stat-icon"></i>
    </div>
    <div class="stat-value"><?php echo $completedRequests; ?></div>
    <div class="stat-footer">This Month</div>
</div>
            </div>

            <div class="widgets-grid">
                <div class="widget-card">
                    <h3 class="widget-title">Request Status Overview</h3>
                    <canvas id="statusBarChart" width="400" height="250"></canvas>
</div>

                <div class="widget-card">
                    <h3 class="widget-title">Quick Actions</h3>
                    <div class="quick-actions-list">
                        <button id="quick-pr" class="quick-action-btn" data-tab="pending-requests">
                            <i class="fas fa-hourglass-half"></i>
                            <span>View Pending Requests</span>
                        </button>
                        <button id="quick-dr" class="quick-action-btn" data-tab="declined-requests">
                            <i class="fas fa-envelope-open"></i>
                            <span>View Declined Requests</span>
                        </button>
                        <button id="quick-ar" class="quick-action-btn" data-tab="approved-requests">
                            <i class="fas fa-clipboard-check"></i>
                            <span>View Approved Requests</span>
                        </button>
                        <button id="quick-cr" class="quick-action-btn" data-tab="completed-requests">
                            <i class="fas fa-clipboard-check"></i>
                            <span>View Completed Requests</span>
                        </button>
                    </div>
                </div>

                <div class="widget-card full-width">
                    <h3 class="widget-title">Recent Requests</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <?php while($row = $inventoryItems->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['request_id']; ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($row['request_date'])); ?></td>
                                <td><?php echo $row['ProductName']; ?></td>
                                <td>₱<?php echo number_format($row['quantity'] * 100); // example ?></td>
                                <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
<script>
   const ctx = document.getElementById('statusBarChart').getContext('2d');

const statusBarChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Approved', 'Declined', 'Completed'],
        datasets: [{
            label: 'Number of Requests',
            data: [
                <?php echo $statusCounts['Pending']; ?>,
                <?php echo $statusCounts['Approved']; ?>,
                <?php echo $statusCounts['Declined']; ?>,
                <?php echo $statusCounts['Completed']; ?>
            ],
            backgroundColor: [
                '#FFC107',
                '#2196F3', 
                '#F44336', 
                '#4CAF50'  
            ],
            borderColor: [
                '#FFC107',
                '#2196F3',
                '#F44336',
                '#4CAF50'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw;
                    }
                }
            },
            datalabels: { 
                anchor: 'end',
                align: 'end',
                color: '#000',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: function(value) {
                    return value;
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                stepSize: 1,
                title: {
                    display: true,
                    text: 'Number of Requests'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Request Status'
                }
            }
        }
    },
    plugins: [ChartDataLabels] 
});
    $(document).ready(function () {
      
        $("#pr").click(function(){ window.location.href = "supplier_portal_1pr.php";});
        $("#dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
        $("#ar").click(function(){ window.location.href = "supplier_portal_3ar.php";});
        $("#cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
        $("#m").click(function(){ window.location.href = "supplier_portal_m.php"; });
        $("#cp").click(function(){ window.location.href = "supplier_portal_cp.php"; });
        $("#quick-pr").click(function(){ window.location.href = "supplier_portal_1pr.php";});
        $("#quick-dr").click(function(){ window.location.href = "supplier_portal_2dr.php";});
        $("#quick-ar").click(function(){ window.location.href = "supplier_portal_3ar.php";});
        $("#quick-cr").click(function(){ window.location.href = "supplier_portal_4cr.php";});
    });
</script>
</html>
