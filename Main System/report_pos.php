<?php
require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>POS Exchange Report</title>
  <link rel="stylesheet" href="sidebar.css" />
  <link rel="stylesheet" href="notification.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .chart-summary-wrapper {
  display: flex;
  gap: 140px;
  flex-wrap: wrap;
  justify-content: flex-start;
}

.chart-box, .summary {
  flex: 1 1 280px;
  min-width:700px;
  padding: 20px;
  border-radius: 10px;
  background: #f8fbff;
  border: 1px solid rgba(4, 56, 115, 0.06);
  box-sizing: border-box;
}

.donut {
  width: 150px;
  height: 150px;
  margin: 0 auto 10px auto;
}

.donut-legend {
  text-align: center;
  font-size: 16px;
  margin-top: 10px;
}

.summary p {
  font-size: 18px;
  margin: 6px 0;
}
/* Highlight active sidebar items without changing font */
.menu li.active {
    background: #e3f2fd; /* light highlight */
}

/* Keep Reports text same as other nav items */
.menu li#reports > span {
    color: #333;           /* same black as other non-active nav items */
    font-weight: 500;      /* same weight as other nav items */
}


/* Keep other active sidebar items highlighted normally */
.menu li.active:not(#reports) > span {
  font-weight: 700;
  color: var(--accent);
}


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

    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); display:flex; height:100vh; color:#333; font-size:18px; }

    main { flex:1; padding:20px; overflow-y:auto; }

    .topbar-right { display:flex; align-items:center; gap:15px; justify-content:flex-end; margin-bottom:10px; position:relative; z-index:100; }
    .profile-icon { width:36px; height:36px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer; transition:0.2s; }
    .profile-icon:hover { background:var(--accent); }

    .heading-bar { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:22px 26px; border-radius:var(--radius); box-shadow:0 0 10px rgba(0,0,0,0.06); margin-bottom:22px; }
    .heading-bar h1 { font-size:42px; font-weight:700; color:var(--primary); }

    .report-container, .chart-box, .summary { background:white; padding:20px 25px; border-radius:var(--radius); box-shadow:0 0 10px rgba(0,0,0,0.06); margin-bottom:20px; }
    .report-title { margin-bottom:14px; font-weight:700; font-size:17px; color:var(--primary); border-bottom:1px solid #e6eefb; padding-bottom:8px; }
    .report-body { display:flex; gap:25px; flex-wrap:wrap; justify-content:space-between; }

    .donut { width:120px; height:120px; border-radius:50%; background:conic-gradient(var(--approve) 0deg 120deg, var(--pending) 120deg 180deg, var(--reject) 180deg 360deg); margin:auto; position:relative; }
    .donut:after { content:""; position:absolute; top:35px; left:35px; width:50px; height:50px; background:#fff; border-radius:50%; }

    table { width:100%; border-collapse: separate; border-spacing:0 10px; font-size:17px; table-layout: fixed; }
    thead th { text-align:left; padding-bottom:8px; border-bottom:1px solid #e6eefb; font-weight:600; color:var(--primary); }
    td { background:white; padding:12px 15px; border-radius:8px; border:1px solid rgba(4,56,115,0.08); word-wrap:break-word; }

    .filter-group, .export-btn { display:flex; align-items:center; gap:8px; border-radius:8px; cursor:pointer; font-weight:600; transition:0.2s; }
    .filter-group { background:#f0f6ff; padding:8px 14px; border:1px solid rgba(4,56,115,0.08); justify-content:center; min-width:140px; color:var(--primary); }
    .filter-group:hover { background:#d7e6ff; }
    .export-btn { background:var(--accent); border:none; color:#fff; padding:8px 16px; font-size:18px; min-width:110px; justify-content:center; }
    .export-btn:hover { background:#3b7cd3; }
    .back-btn { background:#cccccc; color:#000; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-size:18px; font-weight:600; margin-bottom:20px; transition:0.2s; }
    .back-btn:hover { background:#b3b3b3; }

    /* Dropdown Menu */
    .has-dropdown { position:relative; }
    .has-dropdown .dropdown-menu { display:none; position:absolute; top:100%; left:0; background:white; list-style:none; padding:0; margin:0; width:220px; border-radius:var(--radius); box-shadow:0 4px 10px rgba(0,0,0,0.1); z-index:10; }
    .has-dropdown:hover .dropdown-menu { display:block; }
    .has-dropdown .dropdown-menu li { padding:12px 16px; cursor:pointer; transition:0.2s; }
    .has-dropdown .dropdown-menu li:hover { background-color:#f0f6ff; }

    .sidebar li.active > span { font-weight:700; color:var(--accent); }
  </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
  <div class="profile">
    <div class="icon"><img src="logo.png" alt="MediSync Logo" class="medisync-logo"></div>
    <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
  </div>
  <ul class="menu">
    <li id="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></li>
    <li id="inventory"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></li>
    <li class="has-dropdown active" id="reports">
  <i class="fa-solid fa-file-lines"></i>
  <span>Reports</span>
  <ul class="dropdown-menu">
    <li class="report-link" data-view="inventory-management">Inventory Management</li>
    <li class="report-link active" data-view="pos-requests">POS Exchange</li>
    <li class="report-link" data-view="expiration-wastage">Expiration / Wastage</li>
  </ul>
</li>

 
  

        </li>
        <?php if ($_SESSION['roleName'] === 'Admin'): ?>
        <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
        <?php endif; ?>
        <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
</aside>

<main class="main">
  <div class="topbar-right">
    <?php include 'notification_component.php'; ?>
    <div class="profile-icon"><i class="fa-solid fa-user"></i></div>
  </div>

  <div style="font-size:16px;font-weight:700;color:var(--primary)">POS Exchange Report</div>
  <div class="small-muted">Approval distribution and request logs</div>

  <div style="display:flex;gap:12px;margin-bottom:20px;">
    <div class="filter-group"><i class="fa-solid fa-calendar"></i> Date Range</div>
    <div class="filter-group"><i class="fa-solid fa-tags"></i> Item Category</div>
    <button class="export-btn"><i class="fa-solid fa-file-export"></i> Export</button>
  </div>

  <div class="report-body">
  <div class="chart-summary-wrapper">
    <div class="chart-box">
      <div class="report-title">Request Status</div>
      <div class="donut"></div>
      <div class="donut-legend">
        <span style="color:#3ecb57">● Approved</span> (35)
        <span style="color:#ffc107; margin-left:15px">● Pending</span> (15)
  
      </div>
    </div>

    <div class="summary">
      <div class="report-title">Summary</div>
      <p>Total Requests: <strong>50</strong></p>
      <p>Approved: <strong>35</strong></p>
      <p>Pending: <strong>15</strong></p>
    
    </div>
  </div>
</div>


  <div>
    <div class="report-title">POS Exchange Log</div>
    <table>
      <thead><tr><th>Item</th><th>Qty</th><th>Time and Date Requested</th></tr></thead>
      <tbody>
        <tr><td>Amoxicillin</td><td>200</td><td>04/09/18</td></tr>
        <tr><td>IV Saline</td><td>350</td><td>03/12/16</td></tr>
        <tr><td>Paracetamol</td><td>100</td><td>10/17/12</td></tr>
      </tbody>
    </table>
  </div>
</main>

<script src="sidebar.js"></script>
<script src="notification.js" defer></script>
<script>
  // Top-level nav
  const navMap = {
    "dashboard": "dashboard.php",
    "inventory": "Inventory.php",
    "users": "admin.php",
    "settings": "settings.php",
    "logout": "logout.php"
  };

  Object.keys(navMap).forEach(id => {
    const el = document.getElementById(id);
    if(el) el.addEventListener("click", () => window.location.href = navMap[id]);
  });

  // Report dropdown
  const reportLinks = {
      "inventory-management": "report_inventory.php",
      "pos-requests": "report_pos.php",
      "expiration-wastage": "report_expiration.php"
  };
  document.querySelectorAll(".report-link").forEach(link => {
      link.addEventListener("click", () => {
          const view = link.dataset.view;
          if(reportLinks[view]) window.location.href = reportLinks[view];
      });
  });

  // Highlight active nav
  const currentPage = "<?php echo $currentPage; ?>";
  if(currentPage.includes("dashboard")) document.getElementById("dashboard")?.classList.add("active");
  if(currentPage.includes("Inventory")) document.getElementById("inventory")?.classList.add("active");
  if(currentPage.includes("admin")) document.getElementById("users")?.classList.add("active");
  if(currentPage.includes("settings")) document.getElementById("settings")?.classList.add("active");
  if(currentPage.includes("report_pos")) document.querySelector(".report-link[data-view='pos-requests']")?.classList.add("active");
  $(".report-link").click(function() {
    const title = $(this).text();
    $("h1.heading-bar").text(title); // change the main heading
    $("#reports").addClass("active"); // ensure Reports stays active
});

</script>
</body>
</html>
