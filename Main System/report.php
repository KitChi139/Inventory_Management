<?php
require 'db_connect.php';

// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Report</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="sidebar.css" />
  <link rel="stylesheet" href="notification.css" />

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

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background-color: var(--bg);
  display: flex;
  height: 100vh;
  color: #333;
  font-size: 18px;
}

/* Sidebar CSS moved to sidebar.css */

/* TOPBAR Right (Notification + Profile) */
.topbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
  justify-content: flex-end;
  margin-bottom: 10px;
  position: relative;
  z-index: 100;
}

/* Notification component is handled by notification.css */
.notif-wrap {
  position: relative;
  display: inline-block;
}

.profile-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--primary);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  cursor: pointer;
  transition: 0.2s;
}

.profile-icon:hover {
  background: var(--accent);
}

/* Heading Bar */
.heading-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  padding: 22px 26px;
  border-radius: var(--radius);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.06);
  margin-bottom: 22px;
}

.heading-bar h1 {
  font-size: 42px;
  font-weight: 700;
  color: var(--primary);
}

/* CARDS */
.cards-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
  margin-top: 10px;
}

.card {
  background: #d9d9d9;
  padding: 35px 25px;
  border-radius: 8px;
  text-align: center;
  cursor: pointer;
  transition: 0.2s;
}

.card:hover {
  background: #c8c8c8;
  transform: translateY(-3px);
}

.card .emoji {
  font-size: 46px;
  margin-bottom: 10px;
}

/* REPORT CONTAINER */
.report-container {
  background: white;
  padding: 20px 25px;
  border-radius: var(--radius);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.06);
  color: #333;
  font-size: 18px;
  max-width: 100%;
}

/* Report header flex & spacing */
.report-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 12px;
}

.report-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 250px;
  flex-grow: 1;
}

.title-icon {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--accent), #1b68d6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}

.filter-group {
  background: #f0f6ff;
  padding: 8px 14px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
  color: var(--primary);
  font-weight: 600;
  border: 1px solid rgba(4, 56, 115, 0.08);
  min-width: 140px;
  justify-content: center;
  white-space: nowrap;
  transition: background-color 0.2s;
}

.filter-group:hover {
  background-color: #d7e6ff;
}

.export-btn {
  padding: 8px 16px;
  background: var(--accent);
  border: none;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 18px;
  min-width: 110px;
  transition: background-color 0.2s;
}

.export-btn:hover {
  background-color: #3b7cd3;
}

.back-btn {
  background: #cccccc;
  color: #000;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  transition: background-color 0.2s;
}

.back-btn:hover {
  background-color: #b3b3b3;
}

/* report body for charts and summary */
.report-body {
  display: flex;
  gap: 25px;
  margin-bottom: 25px;
  flex-wrap: wrap;
  justify-content: space-between;
}

.chart-box,
.summary {
  flex: 1 1 280px;
  min-width: 280px;
  padding: 18px 20px;
  border-radius: 10px;
  background: #f8fbff;
  border: 1px solid rgba(4, 56, 115, 0.06);
  box-sizing: border-box;
  font-size: 18px;
}

/* Report titles */
.report-title {
  margin-bottom: 14px;
  font-weight: 700;
  font-size: 17px;
  color: var(--primary);
  border-bottom: 1px solid #e6eefb;
  padding-bottom: 8px;
}

/* Bars */
.bar-group {
  margin-bottom: 14px;
}

.bar-label {
  font-size: 17px;
  margin-bottom: 6px;
  color: var(--primary);
  font-weight: 600;
}

.bar,
.bar2 {
  height: 10px;
  background: var(--accent);
  border-radius: 6px;
}

.bar2 {
  background: #7ebaff;
}

/* Donut chart */
.donut {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: conic-gradient(
    var(--approve) 0deg 120deg,
    var(--pending) 120deg 180deg,
    var(--reject) 180deg 360deg
  );
  margin: auto;
  position: relative;
}

.donut:after {
  content: "";
  position: absolute;
  top: 35px;
  left: 35px;
  width: 50px;
  height: 50px;
  background: #fff;
  border-radius: 50%;
}

/* Timeline rows */
.timeline-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.timeline-line {
  height: 8px;
  border-radius: 6px;
  background: var(--accent);
  flex-grow: 1;
}

/* Tables */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
  font-size: 17px;
  table-layout: fixed;
}

thead th {
  text-align: left;
  padding-bottom: 8px;
  border-bottom: 1px solid #e6eefb;
  font-weight: 600;
  color: var(--primary);
}

td {
  background: white;
  padding: 12px 15px;
  border-radius: 8px;
  border: 1px solid rgba(4, 56, 115, 0.08);
  word-wrap: break-word;
}

/* Profile icon */
.profile-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  cursor: pointer;
  transition: 0.2s;
}

.profile-icon:hover {
  background: var(--accent);
}

/* ===== SUPPLIER BARS ===== */
.supplier-bars {
  width: 100%;
  margin-top: 10px;
}

.bar-row {
  display: grid;
  grid-template-columns: 120px 1fr 40px;
  align-items: center;
  margin-bottom: 10px;
  font-size: 17px;
}

.bar-label {
  color: var(--primary);
  font-weight: 600;
}

.bar-track {
  height: 14px;
  background: #e8f2ff;
  border-radius: 20px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 8px;
  color: white;
  font-weight: 600;
  font-size: 14px;
  border-radius: 20px;
  background: linear-gradient(to right, #cbe7ff, #4f9cf9);
}

.bar-count {
  text-align: right;
  font-weight: 600;
  color: var(--primary);
}

/* ===== EXPIRATION TIMELINE CARDS ===== */
.expire-list {
  margin-top: 15px;
}

.expire-item {
  background: #f8fbff;
  padding: 15px 20px;
  border-radius: 8px;
  border: 1px solid rgba(4, 56, 115, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.expire-name {
  font-weight: 700;
  color: var(--primary);
  font-size: 18px;
}

.expire-batch {
  font-size: 14px;
  color: #666;
}

.expire-date {
  font-weight: 600;
  color: var(--primary);
  background: #eef5ff;
  padding: 6px 12px;
  border-radius: 6px;
  min-width: 100px;
  text-align: center;
}

  </style>
</head>

<body>

  <!-- SIDEBAR -->
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
      <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
      <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
      <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
      <li id="reports" class="active"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
      <?php if ($_SESSION['roleName'] === 'Admin'): ?>
      <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
      <?php endif; ?>    
      <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
      <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- Notification + Profile icon (top-right in main content) -->
    <div class="topbar-right">
      <!-- 🔔 NOTIFICATION SYSTEM -->
      <div class="notif-wrap" id="notifWrap">
          <button class="notif-btn" id="notifBtn" aria-expanded="false">
              <i class="fa-solid fa-bell"></i>
              <span class="notif-count" id="notifCount" style="display:none;">0</span>
          </button>

          <div class="notif-dd" id="notifDropdown">
              <div class="dd-header">
                  <h4>Notifications</h4>

                  <div class="notif-settings">
                      <input type="checkbox" id="notifyToggle" checked>
                      <label for="notifyToggle">Notify</label>
                  </div>
              </div>

              <div class="dd-list" id="notifList">
                  <div style="padding:14px; color:#666;">Loading...</div>
              </div>

              <div class="notif-footer">
                  <a href="message_list.php">View All Notifications</a>
              </div>
          </div>
      </div>
      <div class="profile-icon">
        <i class="fa-solid fa-user"></i>
      </div>
    </div>

    <!-- Heading Bar -->
    <div class="heading-bar">
      <h1 id="view-title">Report</h1>   
    </div>

    <!-- CARDS -->
    <div id="view-content" class="cards-container">
      <div class="card" data-view="inventory-management">
        <div class="emoji">📦</div>
        <h3>Inventory Management</h3>
      </div>

      <div class="card" data-view="pos-requests">
        <div class="emoji">📊</div>
        <h3>POS Exchange</h3>
      </div>

      <div class="card" data-view="supplier-vendor">
        <div class="emoji">🚚</div>
        <h3>Supplier / Vendor</h3>
      </div>

      <div class="card" data-view="expiration-wastage">
        <div class="emoji">⏳</div>
        <h3>Expiration / Wastage</h3>
      </div>
    </div>
  </main>

<script>
/* PURE CSS REPORTS WITH BACK BUTTON ------------- */

const backBtn = `
<button class="back-btn" onclick="goBack()">
  <i class="fa-solid fa-arrow-left"></i> Back
</button>
`;

function goBack() {
  location.reload(); // resets view without refreshing whole page context
}

const views = {

"inventory-management": `
${backBtn}
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
      <div class="filter-group"><i class="fa-solid fa-calendar"></i> Date Range</div>
      <div class="filter-group"><i class="fa-solid fa-tags"></i>Item Category</div>
      <button class="export-btn"><i class="fa-solid fa-file-export"></i> Export</button>
    </div>
  </div>

  <div class="report-body">

    <div class="chart-box">
      <div class="report-title">Weekly Movement</div>

      <div class="bar-group">
        <div class="bar-label">Week 1</div>
        <div class="bar" style="width:55%;"></div>
      </div>

      <div class="bar-group">
        <div class="bar-label">Week 2</div>
        <div class="bar2" style="width:80%;"></div>
      </div>

      <div class="bar-group">
        <div class="bar-label">Week 3</div>
        <div class="bar" style="width:40%;"></div>
      </div>

    </div>

    <div class="summary">
      <div class="report-title">Summary</div>
      <p style="font-size:18px;">Total Stock IN: <strong id="stock-in">650</strong></p>
      <p style="font-size:18px;">Total Stock OUT: <strong id="stock-out">750</strong></p>
      <p style="font-size:18px;">Net Change: <strong id="net-change">-100</strong></p>
      <p style="font-size:18px;">Total Expired: <strong>15</strong></p>
    </div>

  </div>

  <div>
    <div class="report-title">Frequently Moved Items</div>
    <table>
      <thead><tr><th>Item</th><th>Batch</th><th>IN</th><th>OUT</th><th>Net</th></tr></thead>
      <tbody>
        <tr><td>Amoxicillin</td><td>AMX2309B</td><td class="stock-in">200</td><td class="stock-out">150</td><td class="net-change">50</td></tr>
        <tr><td>IV Saline</td><td>IVS2401B</td><td class="stock-in">350</td><td class="stock-out">400</td><td class="net-change">-50</td></tr>
        <tr><td>Paracetamol</td><td>PCM2312C</td><td class="stock-in">100</td><td class="stock-out">200</td><td class="net-change">-100</td></tr>
      </tbody>
    </table>
  </div>

</div>
`,
"pos-requests": `
${backBtn}
<div class="report-container">

  <div class="report-header">
    <div class="report-left">
      <div class="title-icon"><i class="fa-solid fa-building"></i></div>
      <div>
        <div style="font-size:16px;font-weight:700;color:var(--primary)">POS Exchange Report</div>
        <div class="small-muted">Approval distribution and request logs</div>
      </div>
    </div>

    <div style="display:flex;gap:12px">
      <div class="filter-group"><i class="fa-solid fa-calendar"></i> Date Range</div>
      <div class="filter-group"><i class="fa-solid fa-tags"></i> Item Category</div>
      <button class="export-btn"><i class="fa-solid fa-file-export"></i> Export</button>
    </div>
  </div>

  <div class="report-body">

    <div class="chart-box">
      <div class="report-title">Request Status</div>
      <div class="donut"></div>
      <div style="margin-top:10px;font-size:16px">
        <strong style="color:var(--approve)">● Approved</strong> (35)<br>
        <strong style="color:var(--pending)">● Pending</strong> (15)
      </div>
    </div>

    <div class="summary">
      <div class="report-title">Summary</div>
      <p style="font-size:18px;">Total Requests: <strong>50</strong></p>
      <p style="font-size:18px;">Approved: <strong>35</strong></p>
      <p style="font-size:18px;">Pending: <strong>15</strong></p>
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

</div>
`,
"supplier-vendor": `
${backBtn}
<div class="report-container">

  <div class="report-header">
    <div class="report-left">
      <div class="title-icon"><i class="fa-solid fa-truck"></i></div>
      <div>
        <div style="font-size:16px;font-weight:700;color:var(--primary)">Supplier / Vendor Report</div>
        <div class="small-muted">Order distribution and activity logs</div>
      </div>
    </div>

    <div class="filter-group"><i class="fa-solid fa-calendar"></i> Date Range</div>
    <div class="filter-group"><i class="fa-solid fa-tags"></i> Item Category</div>
    <div class="filter-group"><i class="fa-solid fa-tags"></i> Supplier</div>
    <button class="export-btn"><i class="fa-solid fa-file-export"></i> Export</button>
  </div>

  <div class="report-body">

    <!-- SUPPLIER BARS (UPDATED TO MATCH SCREENSHOT) -->
    <div class="report-container">
      <div class="report-title">Supplier Transactions Distribution</div>

      <div class="supplier-bars">
        ${[
          ["A Corp", 500],
          ["B Corp", 400],
          ["C Corp", 100],
          ["D Corp", 80],
          ["E Corp", 40],
          ["F Corp", 30],
          ["G Corp", 20],
          ["H Corp", 10],
          ["I Corp", 5],
          ["J Corp", 4],
          ["K Corp", 3],
        ].map(([name, value]) => `
          <div class="bar-row">
            <div class="bar-label">${name}</div>

            <div class="bar-track">
              <div class="bar-fill" style="width:${(value / 500) * 100}%;">
                ${value}
              </div>
            </div>

            <div class="bar-count">${value}</div>
          </div>
        `).join("")}
      </div>
    </div>

    <div class="summary">
      <div class="report-title">Summary</div>
      <p>Total Suppliers: <strong>12</strong></p>
      <p>Total Orders: <strong>50</strong></p>
    </div>

  </div>

  <div>
    <div class="report-title">Supplier List</div>
    <table>
      <thead><tr><th>Supplier</th><th>Batch</th><th>Item</th><th>Total Orders</th><th>Last Order</th></tr></thead>
      <tbody>
        <tr><td>Mediline</td><td>GLV1204M</td><td>Gloves</td><td>120</td><td>Mar 12, 2025</td></tr>
        <tr><td>Pfizer</td><td>IP1303P</td><td>Ibuprofen</td><td>60</td><td>Mar 14, 2025</td></tr>
        <tr><td>Vendor A</td><td>IVF2012A</td><td>IV Fluids</td><td>30</td><td>Mar 16, 2025</td></tr>
      </tbody>
    </table>
  </div>

</div>
`,

"expiration-wastage": `
${backBtn}
<div class="report-container">

  <div class="report-header">
    <div class="report-left">
      <div class="title-icon"><i class="fa-solid fa-hourglass-half"></i></div>
      <div>
        <div style="font-size:16px;font-weight:700;color:var(--primary)">Expiration / Wastage Report</div>
        <div class="small-muted">Timeline of item expiry and wastage</div>
      </div>
    </div>

    <div class="filter-group"><i class="fa-solid fa-calendar"></i> Date Range</div>
    <div class="filter-group"><i class="fa-solid fa-tags"></i> Item Category</div>
    <div class="filter-group"><i class="fa-solid fa-tags"></i> Severity</div>
    <button class="export-btn"><i class="fa-solid fa-file-export"></i> Export</button>
  </div>

  <!-- EXPIRATION TIMELINE -->
  <div class="report-container">
    <div class="report-title">Expiration Timeline</div>

    <div class="expire-list">
      ${[
        ["Paracetamol", "PCM2312C", "2025-11-09"],
        ["Amoxicillin", "AMX2309B", "2025-10-01"],
        ["Metformin", "MET2410A", "2025-10-31"],
        ["Aspirin", "ASA2207X", "2025-12-25"],
        ["Omeprazole", "OME2503Z", "2025-11-03"],
      ].map(([item, batch, expiry]) => `
        <div class="expire-item">
          <div>
            <div class="expire-name">${item} (${batch})</div>
            <div class="expire-batch">Batch ${batch}</div>
          </div>
          <div class="expire-date">${expiry}</div>
        </div>
      `).join("")}
    </div>
  </div>

  <!-- SUMMARY (RESTORED) -->
  <div class="summary" style="margin-top:20px;">
    <div class="report-title">Summary</div>
    <p>Near Expiry: <strong>50</strong></p>
    <p>Expired: <strong>20</strong></p>
    <p>Total Risk Items: <strong>70</strong></p>
  </div>

  <!-- EXPIRATION TABLE -->
  <div style="margin-top:20px;">
    <div class="report-title">Expiration List</div>
    <table>
      <thead><tr><th>Item</th><th>Batch</th><th>Qty</th><th>Category</th><th>Expiry</th></tr></thead>
      <tbody>
        <tr><td>Amoxicillin</td><td>AMX2309B</td><td>200</td><td>Antibiotics</td><td>04/10/18</td></tr>
        <tr><td>IV Saline</td><td>IVS2401B</td><td>350</td><td>IV Fluids</td><td>04/13/18</td></tr>
        <tr><td>Paracetamol</td><td>PCM2312C</td><td>100</td><td>Analgesics</td><td>04/22/18</td></tr>
      </tbody>
    </table>
  </div>

</div>
`,

};

/* VIEW SWITCHING ------------------- */
$(".card").on("click", function() {
  const view = $(this).data("view");
  $("#view-title").text($(this).find("h3").text());
  $("#view-content").removeClass("cards-container").html(views[view]);
});

/* VALIDATE INVENTORY REPORT SUMMARY */
function validateInventoryReport() {
  // Validate summary values
  const stockIn = parseInt(document.getElementById('stock-in')?.textContent || '650');
  const stockOut = parseInt(document.getElementById('stock-out')?.textContent || '750');
  const netChange = parseInt(document.getElementById('net-change')?.textContent || '-100');
  
  // Mark as invalid if negative
  if (stockIn < 0 || stockOut < 0 || netChange < 0) {
    if (stockIn < 0) {
      const el = document.getElementById('stock-in');
      if (el) {
        el.style.color = '#dc3545';
        el.textContent = 'Invalid';
      }
    }
    if (stockOut < 0) {
      const el = document.getElementById('stock-out');
      if (el) {
        el.style.color = '#dc3545';
        el.textContent = 'Invalid';
      }
    }
    if (netChange < 0) {
      const el = document.getElementById('net-change');
      if (el) {
        el.style.color = '#dc3545';
        el.textContent = 'Invalid';
      }
    }
  } else {
    // Remove + sign from positive net change
    if (netChange > 0) {
      const el = document.getElementById('net-change');
      if (el) {
        el.textContent = netChange.toString();
      }
    }
  }
  
  // Validate table rows
  document.querySelectorAll('.stock-in, .stock-out, .net-change').forEach(cell => {
    const value = parseInt(cell.textContent);
    if (value < 0) {
      cell.style.color = '#dc3545';
      cell.textContent = 'Invalid';
    } else if (cell.classList.contains('net-change') && value > 0) {
      // Remove + sign from positive net change
      cell.textContent = value.toString();
    }
  });
}

/* NAVIGATION HANDLERS */
$(document).ready(function() {
  // Sidebar toggle handled by sidebar.js

  // Navigation handlers
  $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
  $("#inventory").click(function(){ window.location.href = "Inventory.php"; });
  $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });
  $("#request").click(function(){ window.location.href = "request_list.php"; });
  $("#nav-suppliers").click(function(){ window.location.href = "supplier.php"; });
  $("#reports").click(function(){ window.location.href = "report.html"; });
  $("#users").click(function(){ window.location.href = "admin.php"; });
      $("#settings").click(function(){ window.location.href = "settings.php"; });
  $("#logout").click(function(){ window.location.href = "logout.php"; });
  
  // Validate inventory report when view is loaded
  $(".card").on("click", function() {
    setTimeout(validateInventoryReport, 100);
  });
});
</script>
<script src="sidebar.js"></script>
<script src="notification.js" defer></script>
<script>
// 🔽 OPEN/CLOSE DROPDOWN
$(document).on("click", "#notifBtn", function(e) {
    e.stopPropagation();
    $("#notifDropdown").toggleClass("show");

    if ($("#notifDropdown").hasClass("show")) {
        loadNotificationDropdown();
    }
});

// Close dropdown when clicking outside
$(document).click(function(e) {
    if (!$(e.target).closest("#notifWrap").length) {
        $("#notifDropdown").removeClass("show");
    }
});

// 🔄 AUTO UPDATE BADGE
function loadNotifications() {
    $.ajax({
        url: "get_notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            let total = data.messages + data.lowstock;

            if (total > 0) {
                $("#notifCount").text(total).show();
            } else {
                $("#notifCount").hide();
            }
        }
    });
}

// 🔽 LOAD 3 MOST RECENT ITEMS INSIDE DROPDOWN
function loadNotificationDropdown() {
    $.ajax({
        url: "get_notification_items.php",
        method: "GET",
        success: function(html) {
            $("#notifList").html(html);
        }
    });
}

// Auto-refresh notifications every 10 seconds
loadNotifications();
setInterval(loadNotifications, 10000);
</script>

</body>
</html>
