<?php
require 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Protect page - require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit();
}

// Fetch popup message if any
$popupMessage = '';
if (isset($_SESSION['popupMessage'])) {
    $popupMessage = $_SESSION['popupMessage'];
    unset($_SESSION['popupMessage']);
}

// Fetch categories
$categories = [];
$res = $conn->query("SELECT CategoryID, Category_Name FROM categories ORDER BY Category_Name");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch units
$units = [];
$res = $conn->query("SELECT UnitID, UnitName FROM units ORDER BY UnitName");
while ($row = $res->fetch_assoc()) {
    $units[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<!-- STYLESHEETS -->
<link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="dashboard.css" />
    <link rel="stylesheet" href="notification.css"> 

<style>
/* Scrollable table container */
.table-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-top: 10px;
}
.table-container table {
    width: 100%;
    border-collapse: collapse;
}
.table-container thead th {
    position: sticky;
    top: 0;
    background-color: #f1f1f1;
    z-index: 10;
    padding: 10px;
    text-align: left;
}
.table-container tbody td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
.table-container tbody tr:hover {
    background-color: #f5f5f5;
}

/* Tabs */
.tab { display:none; }
.tab.active { display:block; }
.tab-buttons { margin:20px 0; }
.tab-btn { padding:8px 16px; margin-right:5px; cursor:pointer; border:none; background:#f1f1f1; border-radius:5px; font-weight:600; }
.tab-btn.active { background:#0b66a1; color:white; }

/* Buttons */
.add-btn {
    background-color: #0b66a1; 
    color: #fff; 
    padding: 8px 16px; 
    border: none; 
    border-radius: 6px; 
    font-weight: 600; 
    cursor: pointer; 
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
}
.add-btn:hover { background-color: #095086; }
.action-btn { padding:4px 8px; margin:0 2px; border:none; border-radius:5px; cursor:pointer; }
.edit-btn { background:#0b66a1; color:white; }
.delete-btn { background:#e05a47; color:white; }

/* Modals */
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); justify-content:center; align-items:center; z-index:2000; }
.modal-content { background:white; width:400px; padding:20px; border-radius:12px; }
.modal-content form { display:flex; flex-direction:column; gap:12px; }
.modal-content input[type="text"] { padding:8px 10px; border:1px solid #ccc; border-radius:6px; width:100%; box-sizing:border-box; }
.modal-actions { margin-top:15px; display:flex; justify-content:flex-end; gap:10px; }
.save-btn { background:#0b66a1; color:white; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; }
.save-btn:hover { background:#095086; }
.close-btn { background:#444; color:white; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; }
.close-btn:hover { background:#222; }
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
        <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
        <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
        <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
        <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
        <?php if ($_SESSION['roleName'] === 'Admin'): ?>
        <li id="users"><i class="fa-solid fa-users"></i><span>Users</span></li>
        <?php endif; ?>
        <li class="active" id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
    </ul>
</aside>

 <main class="main">
    <!-- Notification + Profile icon (top-right in main content) -->
          <!-- Notification + Profile icon (top-right in main content) -->
      <div class="topbar-right">
        <?php include 'notification_component.php'; ?>
        <div class="profile-icon">
          <i class="fa-solid fa-user"></i>
        </div>
      </div>

    <div class="heading-bar">
        <h1>Settings</h1>
    </div>

    <!-- TAB BUTTONS -->
    <div class="tab-buttons">
        <button class="tab-btn active" onclick="switchTab('category')">Category Table</button>
        <button class="tab-btn" onclick="switchTab('unit')">Unit Table</button>
    </div>

    <!-- CATEGORY TAB -->
    <div id="category" class="tab active">
        <div class="card">
            <button class="add-btn" onclick="openModal('addCategoryModal')">
                <i class="fa-solid fa-plus"></i> Add Category
            </button>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['CategoryID'] ?></td>
                            <td><?= htmlspecialchars($cat['Category_Name']) ?></td>
                            <td style="text-align:center;">
                                <button class="action-btn edit-btn" onclick="openModal('editCategoryModal')">Edit</button>
                              <form method="POST" action="settings_action.php" style="display:inline;">
    <input type="hidden" name="action" value="delete_category">
    <input type="hidden" name="id" value="<?= $cat['CategoryID'] ?>">
    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
</form>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- UNIT TAB -->
    <div id="unit" class="tab">
        <div class="card">
            <button class="add-btn" onclick="openModal('addUnitModal')">
                <i class="fa-solid fa-plus"></i> Add Unit
            </button>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Unit ID</th>
                            <th>Unit Name</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($units as $unit): ?>
                        <tr>
                            <td><?= $unit['UnitID'] ?></td>
                            <td><?= htmlspecialchars($unit['UnitName']) ?></td>
                            <td style="text-align:center;">
                                <button class="action-btn edit-btn" onclick="openModal('editUnitModal')">Edit</button>
                                <form method="POST" action="settings_action.php" style="display:inline;">
    <input type="hidden" name="action" value="delete_unit">
    <input type="hidden" name="id" value="<?= $unit['UnitID'] ?>">
    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this unit?')">Delete</button>
</form>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<!-- ADD CATEGORY -->
<div class="modal" id="addCategoryModal">
  <div class="modal-content">
    <h3>Add Category</h3>
    <form method="POST" action="settings_action.php">
      <input type="text" name="name" placeholder="Category Name" required>
      <input type="hidden" name="action" value="add_category">
      <div class="modal-actions">
        <button type="button" class="close-btn" onclick="closeModal('addCategoryModal')">Cancel</button>
        <button type="submit" class="save-btn">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT CATEGORY -->
<div class="modal" id="editCategoryModal">
  <div class="modal-content">
    <h3>Edit Category</h3>
    <form method="POST" action="settings_action.php">
      <input type="text" name="name" placeholder="Category Name" required>
      <input type="hidden" name="action" value="edit_category">
      <input type="hidden" name="id" value=""> <!-- dynamically set the ID -->
      <div class="modal-actions">
        <button type="button" class="close-btn" onclick="closeModal('editCategoryModal')">Cancel</button>
        <button type="submit" class="save-btn">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- ADD UNIT -->
<div class="modal" id="addUnitModal">
  <div class="modal-content">
    <h3>Add Unit</h3>
    <form method="POST" action="settings_action.php">
      <input type="text" name="name" placeholder="Unit Name" required>
      <input type="hidden" name="action" value="add_unit">
      <div class="modal-actions">
        <button type="button" class="close-btn" onclick="closeModal('addUnitModal')">Cancel</button>
        <button type="submit" class="save-btn">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT UNIT -->
<div class="modal" id="editUnitModal">
  <div class="modal-content">
    <h3>Edit Unit</h3>
    <form method="POST" action="settings_action.php">
      <input type="text" name="name" placeholder="Unit Name" required>
      <input type="hidden" name="action" value="edit_unit">
      <input type="hidden" name="id" value=""> <!-- dynamically set the ID -->
      <div class="modal-actions">
        <button type="button" class="close-btn" onclick="closeModal('editUnitModal')">Cancel</button>
        <button type="submit" class="save-btn">Update</button>
      </div>
    </form>
  </div>
</div>


<script src="sidebar.js"></script>
<script>
function switchTab(tabName) {
    document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    document.getElementById(tabName).classList.add("active");
    event.target.classList.add("active");
}
function openModal(id){ document.getElementById(id).style.display="flex"; }
function closeModal(id){ document.getElementById(id).style.display="none"; }

// Sidebar navigation
document.getElementById("dashboard").onclick = () => window.location.href = "dashboard.php";
document.getElementById("inventory").onclick = () => window.location.href = "Inventory.php";
document.getElementById("low-stock").onclick = () => window.location.href = "lowstock.php";
document.getElementById("request").onclick = () => window.location.href = "request_list.php";
document.getElementById("nav-suppliers").onclick = () => window.location.href = "supplier.php";
document.getElementById("reports").onclick = () => window.location.href = "report.php";
document.getElementById("users").onclick = () => window.location.href = "admin.php";
document.getElementById("settings").onclick = () => window.location.href = "settings.php";
document.getElementById("logout").onclick = () => window.location.href = "logout.php";


</script>

</body>
</html>
