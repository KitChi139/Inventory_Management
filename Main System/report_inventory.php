    <?php
    session_start();
    require 'db_connect.php';

    // Protect page - require login
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }
    // Fetch categories for filter dropdown
    $categories = [];
    $catRes = $conn->query("SELECT CategoryID, Category_Name FROM categories ORDER BY Category_Name");
    while($cat = $catRes->fetch_assoc()){
        $categories[] = $cat;
    }

    // Get selected category
    $selectedCategory = $_GET['category'] ?? null;

    $whereDate = "";
    $whereCategory = "";
    $startDate = $_GET['start'] ?? null;
    $endDate   = $_GET['end'] ?? null;
    if($startDate && $endDate){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate   = date('Y-m-d', strtotime($endDate));
        $whereDate = "DateTime BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
    } else {
        $whereDate = "DateTime >= DATE_SUB(NOW(), INTERVAL 4 WEEK)";
    }

    // Category filter
    if($selectedCategory){
        $whereCategory = "p.CategoryID = ".(int)$selectedCategory;
    }

    $whereClause = "WHERE 1";
    if($whereDate) $whereClause .= " AND $whereDate";
    if($whereCategory) $whereClause .= " AND $whereCategory";

    // =====================
    // 1️⃣ Weekly Movement Data (for chart)
    // =====================
    $weeklyData = [
        'labels' => [],
        'stock_in' => [],
        'stock_out' => [],
        'net_change' => []
    ];

    $total_in = 0;
    $total_out = 0;

    try {
    $sqlWeekly = "
    SELECT
        YEAR(t.DateTime) AS yr,
        WEEK(t.DateTime, 1) AS wk,
        SUM(CASE WHEN UPPER(t.MovementType)='IN' THEN t.Quantity ELSE 0 END) AS total_in,
        SUM(CASE WHEN UPPER(t.MovementType)='OUT' THEN t.Quantity ELSE 0 END) AS total_out,
        SUM(CASE WHEN UPPER(t.MovementType)='IN' THEN t.Quantity ELSE 0 END) -
        SUM(CASE WHEN UPPER(t.MovementType)='OUT' THEN t.Quantity ELSE 0 END) AS net_change
    FROM inventory_transactions t
    JOIN products p ON t.ProductID = p.ProductID
    $whereClause
    GROUP BY yr, wk
    ORDER BY yr, wk;
    ";
        $res = $conn->query($sqlWeekly);
        while($row = $res->fetch_assoc()) {
            $weeklyData['labels'][] = "Week " . $row['wk'];
            $weeklyData['stock_in'][] = (int)$row['total_in'];
            $weeklyData['stock_out'][] = (int)$row['total_out'];
            $weeklyData['net_change'][] = (int)$row['net_change'];

            $total_in += (int)$row['total_in'];
            $total_out += (int)$row['total_out'];
        }
    } catch (Throwable $e) {
        $weeklyData = ['labels'=>[], 'stock_in'=>[], 'stock_out'=>[], 'net_change'=>[]];
    }

    // Net change total
    $net_change = $total_in - $total_out;

    // =====================
    // 2️⃣ Frequently Moved Items (for table)
    // =====================
    $frequentItems = [];
    try {
        $sqlItems = "
    SELECT 
        p.ProductName,
        t.BatchNum,
        SUM(CASE WHEN UPPER(t.MovementType)='IN' THEN t.Quantity ELSE 0 END) AS total_in,
        SUM(CASE WHEN UPPER(t.MovementType)='OUT' THEN t.Quantity ELSE 0 END) AS total_out,
        SUM(CASE WHEN UPPER(t.MovementType)='IN' THEN t.Quantity ELSE 0 END) -
        SUM(CASE WHEN UPPER(t.MovementType)='OUT' THEN t.Quantity ELSE 0 END) AS net_change,
        c.Category_Name
    FROM inventory_transactions t
    JOIN products p ON t.ProductID = p.ProductID
    JOIN categories c ON p.CategoryID = c.CategoryID
    $whereClause
    GROUP BY t.ProductID, t.BatchNum
    ORDER BY p.ProductName;
    ";
        $resItems = $conn->query($sqlItems);
        while($row = $resItems->fetch_assoc()) {
            $frequentItems[] = $row;
        }
    } catch (Throwable $e) {
        $frequentItems = [];
    }

    // =====================
    // 3️⃣ Total Expired
    // =====================
    $stmt = $conn->query("SELECT SUM(Quantity) as total_expired FROM inventory_transactions WHERE Source='Expired'");
    $total_expired = ($stmt->fetch_assoc())['total_expired'] ?? 0;

    if (isset($_GET['export']) && $_GET['export'] == '1') {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory_report.csv"');

    $output = fopen("php://output", "w");

    // Title row
    fputcsv($output, ["Inventory Management Report"]);
    fputcsv($output, []); // blank row

    // Summary section
    fputcsv($output, ["Summary"]);
    fputcsv($output, ["Total IN", $total_in]);
    fputcsv($output, ["Total OUT", $total_out]);
    fputcsv($output, ["Net Change", $net_change]);
    fputcsv($output, ["Expired", $total_expired]);
    fputcsv($output, []); // blank row

    // Frequent items table
    fputcsv($output, ["Frequently Moved Items"]);
    fputcsv($output, ["Item", "Category", "Batch", "IN", "OUT", "Net"]);

    foreach ($frequentItems as $row) {
        fputcsv($output, [
            $row['ProductName'],
            $row['Category_Name'],
            $row['BatchNum'],
            $row['total_in'],
            $row['total_out'],
            $row['net_change']
        ]);
    }

    fclose($output);
    exit;
}

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Inventory Management Report</title>
    <link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="notification.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
    :root {
    --primary: #043873;
    --accent: #4f9cf9;
    --danger: #ff4d4f;
    --muted: #777;
    --bg: #f5f8ff;
    --radius: 10px;
    --sidebar-width: 240px;
    --sidebar-collapsed: 70px;

    --approve: #3ecb57;
    --pending: #ffc107;
    --reject: #dc3545;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); display: flex; height: 100vh; color: #333; font-size: 18px; }

    /* Topbar */
    .topbar-right { display: flex; align-items: center; gap: 15px; justify-content: flex-end; margin-bottom: 10px; position: relative; z-index: 100; }
    .profile-icon { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; transition: 0.2s; }
    .profile-icon:hover { background: var(--accent); }

    /* Heading Bar */
            .heading-bar {   display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 16px 20px;   /* reduced padding */
    border-radius: var(--radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 15px;  /* reduced margin */ }
            .heading-bar h1 {   font-size: 32px;      /* slightly smaller */
    font-weight: 700;
    color: var(--primary); }

    /* Cards */
    .cards-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-top: 10px; }
    .card { background: #d9d9d9; padding: 35px 25px; border-radius: 8px; text-align: center; cursor: pointer; transition: 0.2s; }
    .card:hover { background: #c8c8c8; transform: translateY(-3px); }
    .card .emoji { font-size: 46px; margin-bottom: 10px; }

    /* Report container */
    .report-container { background: white; padding: 20px 25px; border-radius: var(--radius); box-shadow: 0 0 10px rgba(0,0,0,0.06); color: #333; font-size: 18px; max-width: 100%; }
    .report-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px; }
    .report-left { display: flex; align-items: center; gap: 12px; min-width: 250px; flex-grow: 1; }
    .title-icon { width: 44px; height: 44px; border-radius: 8px; background: linear-gradient(135deg, var(--accent), #1b68d6); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; }
    .filter-group { background: #f0f6ff; padding: 8px 14px; border-radius: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; color: var(--primary); font-weight: 600; border: 1px solid rgba(4,56,115,0.08); min-width: 140px; justify-content: center; white-space: nowrap; transition: background-color 0.2s; }
    .filter-group:hover { background-color: #d7e6ff; }
    .export-btn { padding: 8px 16px; background: var(--accent); border: none; color: white; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 18px; min-width: 110px; transition: background-color 0.2s; }
    .export-btn:hover { background-color: #3b7cd3; }
    .back-btn { background: #cccccc; color: #000; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: 600; margin-bottom: 20px; transition: background-color 0.2s; }
    .back-btn:hover { background-color: #b3b3b3; }

    .report-body { display: flex; gap: 25px; margin-bottom: 25px; flex-wrap: wrap; justify-content: space-between; }
    .chart-box, .summary { flex: 1 1 280px; min-width: 280px; padding: 18px 20px; border-radius: 10px; background: #f8fbff; border: 1px solid rgba(4,56,115,0.06); box-sizing: border-box; font-size: 18px; }
    .chart-box canvas {
        width: 100% !important;
        max-width: 600px;
        height: 300px !important;
    }
    .report-title { margin-bottom: 14px; font-weight: 700; font-size: 17px; color: var(--primary); border-bottom: 1px solid #e6eefb; padding-bottom: 8px; }

    .bar-group { margin-bottom: 14px; }
    .bar-label { font-size: 17px; margin-bottom: 6px; color: var(--primary); font-weight: 600; }
    .bar, .bar2 { height: 10px; background: var(--accent); border-radius: 6px; }
    .bar2 { background: #7ebaff; }

    .donut { width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(var(--approve) 0deg 120deg, var(--pending) 120deg 180deg, var(--reject) 180deg 360deg); margin: auto; position: relative; }
    .donut:after { content: ""; position: absolute; top: 35px; left: 35px; width: 50px; height: 50px; background: #fff; border-radius: 50%; }

    table { width: 100%; border-collapse: collapse; min-width: 700px; table-layout: auto; }
    thead th { background: #f8f9fa; color: #444; font-weight: 600;
    font-size: 16px; position: sticky; top: 0; z-index: 10; }
    td, th { text-align: left; padding: 12px 16px;
    border-bottom: 1px solid #eee; vertical-align: middle;
    font-size: 15px; white-space: nowrap; }

    /* Dropdown */
    .has-dropdown { position: relative; }
    .has-dropdown .dropdown-menu { display: none; position: absolute; top: 100%; left: 0; background: white; list-style: none; padding: 0; margin: 0; width: 220px; border-radius: var(--radius); box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 10; }
    .has-dropdown:hover .dropdown-menu { display: block; }
    .has-dropdown .dropdown-menu li { padding: 12px 16px; cursor: pointer; transition: 0.2s; }
    .has-dropdown .dropdown-menu li:hover { background-color: #f0f6ff; }
    .has-dropdown.open .dropdown-menu { display: block !important; }

    /* Date filter dropdown */
    .filter-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid rgba(4,56,115,0.15);
        border-radius: 8px;
        padding: 10px 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        z-index: 20;
        min-width: 220px;
        font-size: 14px;
    }

    .filter-dropdown form {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-dropdown input[type="date"] {
        padding: 6px 10px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    .filter-dropdown .export-btn {
        padding: 6px 10px;
        font-size: 14px;
        background: var(--accent);
        border-radius: 6px;
    }

    #dateFilterBtn {
        position: relative;
        min-width: 140px;
    }
    #categoryFilterBtn {
    position: relative; /* Add this */
    min-width: 140px;
    }

    </style>
    </head>
    <body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
  <div class="profile">
    <div class="icon">
      <img src="logo.png?v=2" alt="MediSync Logo" class="medisync-logo">
    </div>
    <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
  </div>
      <ul class="menu">
        <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
        <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
            <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
    <li class="nav-reports has-dropdown">
    <i class="fa-solid fa-file-lines"></i><span>Reports</span>
    <ul class="dropdown-menu" >
  <li id="inventorymanagement" class="active">
    <a class="report-link">Inventory Management</a>
  </li>
  <!-- <li>
    <a class="report-link" href="report_pos.php">POS Exchange</a>
  </li> -->
  <li id="expirationwastage">
    <a class="report-link">Expiration / Wastage</a>
  </li>


    </ul>
  </li>

        <?php if ($_SESSION['roleName'] === 'Admin'): ?>
        <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
        <?php endif; ?>
        <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
      </ul>
</aside>

    <main class="main">
    

    <div class="heading-bar"><h1>Inventory Management Report</h1><div class="topbar-right">
        <?php include 'notification_component.php'; ?>
        <div class="profile-icon"><i class="fa-solid fa-user"></i></div>
    </div></div>
    

    <div class="report-container">
        
        <div class="report-header">
            
            <div class="report-left">
                <div class="title-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div>
                    <div style="font-size:16px;font-weight:700;color:var(--primary)">Inventory Management Report</div>
                    <div class="small-muted">Weekly item movement and net changes</div>
                </div>
                
            </div>
            <div style="display:flex;gap:12px">
            <div class="filter-group" id="dateFilterBtn"><i class="fa-solid fa-calendar"></i> Date Range
                    <div class="filter-dropdown" id="dateFilterDropdown">
                        <form method="get">
                            <input type="date" name="start" value="<?= isset($_GET['start']) ? $_GET['start'] : '' ?>" placeholder="Start Date">
                            <input type="date" name="end" value="<?= isset($_GET['end']) ? $_GET['end'] : '' ?>" placeholder="End Date">
                            <!-- Preserve other GET params if needed -->
                            <button type="submit" class="export-btn">Apply</button>
                        </form>
                    </div>
                </div>
                <div class="filter-group" id="categoryFilterBtn">
        <i class="fa-solid fa-tags"></i> Item Category
        <div class="filter-dropdown" id="categoryFilterDropdown">
            <form method="get">
                <input type="hidden" name="start" value="<?= htmlspecialchars($_GET['start'] ?? '') ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($_GET['end'] ?? '') ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['CategoryID'] ?>" <?= ($selectedCategory==$cat['CategoryID'])?'selected':'' ?>>
                            <?= htmlspecialchars($cat['Category_Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="export-btn">Apply</button>
            </form>
        </div>
    </div>
                <div style="display:flex; gap:8px;">
        <a href="?export=1&start=<?= urlencode($_GET['start'] ?? '') ?>&end=<?= urlencode($_GET['end'] ?? '') ?>&category=<?= urlencode($_GET['category'] ?? '') ?>" 
   class="export-btn" 
   style="text-decoration:none;display:flex;align-items:center;gap:6px;">
   <i class="fa-solid fa-file-export"></i> Export
</a>
        <a href="report_inventory.php" class="export-btn" style="background:#ff4d4f;"><i class="fa-solid fa-eraser"></i> Clear Filter</a>
    </div>
            </div>
        </div>

        <div class="report-body">
            <div class="chart-box">
                    <div class="report-title">Weekly Movement</div>
                    <canvas id="weeklyMovementChart"></canvas>
                </div>

            <div class="summary">
                <div class="report-title">Summary</div>
    <p>Total Stock IN: <strong id="stock-in"><?= $total_in ?></strong></p>
    <p>Total Stock OUT: <strong id="stock-out"><?= $total_out ?></strong></p>
    <p>Net Change: <strong id="net-change"><?= $net_change ?></strong></p>
    <p>Total Expired: <strong><?= $total_expired ?></strong></p>
            </div>
        </div>

        <div>
            <div class="report-title">Frequently Moved Items</div>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Batch</th>
                        <th>IN</th>
                        <th>OUT</th>
                        <th>Net</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($frequentItems as $tr): ?>
                    <tr>
                        <td><?= htmlspecialchars($tr['ProductName']) ?></td>
                        <td><?= htmlspecialchars($tr['Category_Name']) ?></td>
                        <td><?= htmlspecialchars($tr['BatchNum']) ?></td>
                        <td class="stock-in"><?= $tr['total_in'] ?></td>
                        <td class="stock-out"><?= $tr['total_out'] ?></td>
                        <td class="net-change"><?= $tr['net_change'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
            </table>
        </div>
    </div>
    </main>

    <script src="sidebar.js"></script>
    <script src="notification.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function(){
        $("#dateFilterBtn").click(function(e){
            e.stopPropagation();
            $("#dateFilterDropdown").toggle();
        });
        $("#categoryFilterBtn").click(function(e){
            e.stopPropagation();
            $("#categoryFilterDropdown").toggle();
        });

        $(".filter-dropdown").click(function(e){
            e.stopPropagation();
        });

        $(document).click(function(){
            $(".filter-dropdown").hide();
        });
    });
    function applyDateRange() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;

        if (!start || !end) return alert("Please select both start and end dates.");

        const url = new URL(window.location.href);
        url.searchParams.set('start', start);
        url.searchParams.set('end', end);
        window.location.href = url.toString();
    }
    const ctx = document.getElementById('weeklyMovementChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($weeklyData['labels']) ?>,
            datasets: [
                {
                    label: 'Stock IN',
                    data: <?= json_encode($weeklyData['stock_in']) ?>,
                    backgroundColor: '#4f9cf9'
                },
                {
                    label: 'Stock OUT',
                    data: <?= json_encode($weeklyData['stock_out']) ?>,
                    backgroundColor: '#ff4d4f'
                },
                {
                    label: 'Net Change',
                    data: <?= json_encode($weeklyData['net_change']) ?>,
                    backgroundColor: '#3ecb57'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
    $(document).ready(function () {
        //Navigation
        $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
        $("#inventory").click(function(){ window.location.href = "Inventory.php";});
        $("#request").click(function(){ window.location.href = "request_list.php";});
        $("#inventorymanagement").click(function(){ window.location.href = "report_inventory.php";});
        $("#expirationwastage").click(function(){ window.location.href = "report_expiration.php";});
  $(document).ready(function(){
    const current = window.location.pathname.split("/").pop(); // e.g., report_inventory.php

    $(".report-link").each(function(){
      const link = $(this).attr("href");
      if(link === current){
        $(this).addClass("active");
        $("#reports").addClass("active"); // open dropdown
      }
    });
  });

        $("#users").click(function(){ window.location.href = "admin.php"; });
        $("#settings").click(function(){ window.location.href = "settings.php"; });
        $("#logout").click(function(){ window.location.href = "logout.php"; });
      });

       $(document).on("click", ".report-link", function(e){
      e.stopPropagation();
      const view = $(this).data("view");
      $("#view-title").text($(this).text());
      $("#view-content").removeClass("cards-container").html(views[view]);
      validateInventoryReport(); // optional for Inventory report
  }); 
$("#reports").click(function(e){
    e.stopPropagation();
    $(this).toggleClass("active");
});


    // Optional: toggle dropdown on click
    document.querySelectorAll(".has-dropdown > span").forEach(span => {
        span.addEventListener("click", e => {
            const parent = span.parentElement;
            parent.classList.toggle("open");
            const menu = parent.querySelector(".dropdown-menu");
            if(menu) menu.style.display = parent.classList.contains("open") ? "block" : "none";
        });
    });

    // Validate report numbers
    const stockIn = document.getElementById('stock-in');
    const stockOut = document.getElementById('stock-out');
    const netChange = document.getElementById('net-change');
    [stockIn, stockOut, netChange].forEach(el => {
        if(parseInt(el.textContent) < 0) el.textContent = 'Invalid';
    });
    </script>
    </body>
    </html>
