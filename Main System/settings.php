<?php
require_once 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$popupMessage = $_SESSION['popupMessage'] ?? '';
unset($_SESSION['popupMessage']);

$categories = $conn->query("SELECT CategoryID, Category_Name FROM categories ORDER BY Category_Name")->fetch_all(MYSQLI_ASSOC);

$units = $conn->query("SELECT UnitID, UnitName FROM units ORDER BY UnitName")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>
<link rel="stylesheet" href="styles/sidebar.css">
<link rel="stylesheet" href="styles/dashboard.css">
<link rel="stylesheet" href="styles/notification.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>

.table-container { max-height:400px; overflow-y:auto; border:1px solid #ccc; border-radius:8px; margin-top:10px; }
.table-container table { width:100%; border-collapse:collapse; }
.table-container thead th { position:sticky; top:0; background:#f1f1f1; z-index:10; padding:10px; text-align:left; }
.table-container tbody td { padding:10px; border-bottom:1px solid #ddd; }
.table-container tbody tr:hover { background:#f5f5f5; }
.tab { display:none; }
.tab.active { display:block; }
.tab-buttons { margin:20px 0; }
.tab-btn { padding:8px 16px; margin-right:5px; cursor:pointer; border:none; background:#f1f1f1; border-radius:5px; font-weight:600; }
.tab-btn.active { background:#0b66a1; color:white; }
.add-btn { background:#0b66a1; color:#fff; padding:8px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; margin-bottom:10px; }
.edit-btn:hover {
  background-color: #218838;
}
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  font-size: 15px;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s ease;
  color: #fff;
}
.edit-btn {
  background-color: #28a745;
}
.delete-btn {
  background-color: #dc3545;
}
.delete-btn:hover {
  background-color: #c82333;
}

.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); justify-content:center; align-items:center; z-index:2000; }
.modal-content { background:white; width:400px; padding:20px; border-radius:12px; }
.modal-content form { display:flex; flex-direction:column; gap:12px; }
.modal-content input[type="text"] { padding:8px 10px; border:1px solid #ccc; border-radius:6px; width:100%; box-sizing:border-box; }
.modal-actions { margin-top:15px; display:flex; justify-content:flex-end; gap:10px; }
.save-btn { background:#0b66a1; color:white; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; }
.save-btn:hover { background:#095086; }
.close-btn { background:#444; color:white; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; }
.close-btn:hover { background:#222; }
.has-dropdown { position: relative; }
.has-dropdown > a { display: flex; align-items: center; }
.has-dropdown .dropdown-menu { display:none; position:absolute; top:100%; left:0; background:#fff; list-style:none; padding:8px 0; margin:0; width:220px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); z-index:100; }
.has-dropdown.active .dropdown-menu { display:block; }
.has-dropdown .dropdown-menu li { padding:12px 16px; cursor:pointer; transition:0.2s; }
.has-dropdown .dropdown-menu li a { color: #333; text-decoration: none; display: block; }
.has-dropdown .dropdown-menu li:hover { background:#f0f6ff; }
.has-dropdown .dropdown-menu li.active-link { background:#e3f2fd; }
.has-dropdown .dropdown-menu li.active-link a { color:#043873; font-weight: 600; }
.menu li.active-link { background:#e3f2fd; color:black; }
.menu li.active-link i, .menu li.active-link span { color:#043873; }
.menu li a span {
    display: inline-block;
    margin-left: 0.5mm;
}

.tab-buttons {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #043873;
  color: #fff;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn:hover {
  background: #4f9cf9;
  transform: translateY(-1px);
}

.tab-btn.active {
  background: #0b66a1;
}

.card-header {
  display: flex;
  justify-content: space-between; 
  align-items: center;
  margin-bottom: 12px;
}

.add-btn {
  background: #043873;
  color: #fff;
  padding: 14px 22px;  
  font-size: 18px;     
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.add-btn:hover {
  background: #4f9cf9;
  transform: translateY(-1px);
}


</style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="profile">
        <div class="icon"><img src="logo.png" alt="MediSync Logo" class="medisync-logo"></div>
        <button class="toggle" id="toggleBtn"><i class="fa-solid fa-bars"></i></button>
    </div>
    <ul class="menu">
        <li id="dashboard" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active-link':'' ?>">
            <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
        </li>
        <li id="inventory" class="<?= basename($_SERVER['PHP_SELF'])=='Inventory.php'?'active-link':'' ?>">
            <a href="Inventory.php"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></a>
        </li>
         <li id="request" class="<?= basename($_SERVER['PHP_SELF'])=='request_list.php'?'active-link':'' ?>">
            <a href="request_list.php"><i class="fa-solid fa-file-pen"></i><span>Requests</span></a>
        </li>
        <li id="reports" class="has-dropdown <?= in_array(basename($_SERVER['PHP_SELF']), ['report_inventory.php','report_pos.php','report_expiration.php'])?'active-link':'' ?>">
            <a href="#"><i class="fa-solid fa-file-lines"></i><span>Reports</span></a>
            <ul class="dropdown-menu">
                <li class="<?= basename($_SERVER['PHP_SELF'])=='report_inventory.php'?'active-link':'' ?>"><a href="report_inventory.php">Inventory Management</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF'])=='report_expiration.php'?'active-link':'' ?>"><a href="report_expiration.php">Expiration / Wastage</a></li>
            </ul>
        </li>
        <?php if ($_SESSION['roleName']==='Admin'): ?>
        <li id="users" class="<?= basename($_SERVER['PHP_SELF'])=='admin.php'?'active-link':'' ?>">
            <a href="admin.php"><i class="fa-solid fa-users"></i><span>Users</span></a>
        </li>
        <?php endif; ?>
        <li id="settings" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active-link':'' ?>">
            <a href="settings.php"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
        </li>
        <li id="logout"><a href="logout.php"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></a></li>
    </ul>
</aside>

<main class="main">
    <div class="heading-bar"><h1>Settings</h1></div>

    <div class="tab-buttons">
        <button class="tab-btn active" onclick="switchTab('category', event)">
            <i class="fa-solid fa-layer-group"></i> Category Table
        </button>
        <button class="tab-btn" onclick="switchTab('unit', event)">
            <i class="fa-solid fa-ruler"></i> Unit Table
        </button>
    </div>

    <div id="category" class="tab active">
        <div class="card">
            <div class="card-header">
                <h3>Category Table</h3>
                <button class="add-btn" onclick="openModal('addCategoryModal')"><i class="fa-solid fa-plus"></i> Add Category</button>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th style="text-align:center;">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['CategoryID'] ?></td>
                            <td><?= htmlspecialchars($cat['Category_Name']) ?></td>
                            <td style="text-align:center;">
                                <button class="action-btn edit-btn" onclick="openEditModal('editCategoryModal','<?= addslashes($cat['Category_Name']) ?>', <?= $cat['CategoryID'] ?>)"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <form method="POST" action="settings_action.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="id" value="<?= $cat['CategoryID'] ?>">
                                    <input type="hidden" name="return_tab" value="category">
                                    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="unit" class="tab">
        <div class="card">
            <div class="card-header">
                <h3>Unit Table</h3>
                <button class="add-btn" onclick="openModal('addUnitModal')"><i class="fa-solid fa-plus"></i> Add Unit</button>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th style="text-align:center;">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($units as $unit): ?>
                        <tr>
                            <td><?= $unit['UnitID'] ?></td>
                            <td><?= htmlspecialchars($unit['UnitName']) ?></td>
                            <td style="text-align:center;">
                                <button class="action-btn edit-btn" onclick="openEditModal('editUnitModal','<?= addslashes($unit['UnitName']) ?>', <?= $unit['UnitID'] ?>)"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <form method="POST" action="settings_action.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_unit">
                                    <input type="hidden" name="id" value="<?= $unit['UnitID'] ?>">
                                    <input type="hidden" name="return_tab" value="unit">
                                    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i> Delete</button>
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

<?php
$modals = [
    ['id'=>'addCategoryModal','title'=>'Add Category','action'=>'add_category'],
    ['id'=>'editCategoryModal','title'=>'Edit Category','action'=>'update_category'],
    ['id'=>'addUnitModal','title'=>'Add Unit','action'=>'add_unit'],
    ['id'=>'editUnitModal','title'=>'Edit Unit','action'=>'update_unit']
];
foreach($modals as $m):
?>
<div class="modal" id="<?= $m['id'] ?>">
  <div class="modal-content">
    <h3><?= $m['title'] ?></h3>
    <form method="POST" action="settings_action.php">
        <input type="text" name="name" placeholder="<?= $m['title'] ?> Name" required>
        <input type="hidden" name="action" value="<?= $m['action'] ?>">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="return_tab" value="<?= strpos($m['id'], 'Unit') !== false ? 'unit' : 'category' ?>">
        <div class="modal-actions">
            <button type="button" class="close-btn" onclick="closeModal('<?= $m['id'] ?>')">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
        </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<script src="sidebar.js"></script>
<script>
function switchTab(tabName, event){
    document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}

function openModal(id){ document.getElementById(id).style.display="flex"; }
function closeModal(id){ document.getElementById(id).style.display="none"; }

function openEditModal(modalId, name, id){
    const modal = document.getElementById(modalId);
    modal.style.display='flex';
    modal.querySelector('input[name="name"]').value = name;
    modal.querySelector('input[name="id"]').value = id;
}

document.addEventListener("DOMContentLoaded", function(){

    const hash = window.location.hash.substring(1);
    if (hash === 'unit') {
        switchTab('unit', {currentTarget: document.querySelectorAll('.tab-btn')[1]});
    }

    const reportsItem = document.getElementById("reports");

    reportsItem.querySelectorAll(".dropdown-menu li").forEach(li => {
        const a = li.querySelector("a");
        if(a && a.href === window.location.href) {
            li.classList.add("active-link");
        }
    });

    reportsItem.querySelector("a").addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation();
        reportsItem.classList.toggle("active");
    });

    reportsItem.addEventListener("mouseenter", function(){
        reportsItem.classList.add("active");
    });

    reportsItem.addEventListener("mouseleave", function(){
        reportsItem.classList.remove("active");
    });$s

    document.addEventListener("click", function(e){
        if(!reportsItem.contains(e.target)) {
            reportsItem.classList.remove("active");
        }
    });
});
</script>
</body>
</html>