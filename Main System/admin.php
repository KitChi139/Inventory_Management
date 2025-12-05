  <?php
  session_start();
  if (!isset($_SESSION['loggedin']) || $_SESSION['roleName'] !== 'Admin') {
      header("Location: dashboard.php");
      exit();
  }
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);


  $connection = new mysqli("localhost", "root", "", "inventory_db");
  if ($connection->connect_error) {
      die("Connection Failed: " . $connection->connect_error);
  }

  $success = false;
  $newUserData = null;
  $errorMsg = null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_submit'])) {
      $userType =  $_POST['userType'] ?? '';
      $var1 = trim($_POST['empNameorComName'] ?? '');
      $var2 = trim($_POST['empNumorContPer'] ?? '');
      $contact = trim($_POST['contNum'] ?? '');
     $email = isset($_POST['eMail']) ? strtolower(trim($_POST['eMail'])) : '';

      $pass = $_POST['pass'] ?? '';

        $errors = [];

        if (!$var1) $errors[] = ($userType === 'employee') ? 'Employee Name is required.' : 'Company Name is required.';
        if (!$var2) $errors[] = ($userType === 'employee') ? 'Employee Number is required.' : 'Contact Person is required.';
        if (!$contact) $errors[] = 'Contact Number is required.';
        if (!$email) $errors[] = 'Email is required.';
        if (!$pass) $errors[] = 'Password is required.';
        if (!$userType) $errors[] = 'User Type is required.';

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if ($contact && !preg_match('/^\d{7,15}$/', $contact)) {
            $errors[] = 'Contact number must be 7-15 digits.';
        }

        if ($pass && strlen($pass) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
        } else {
          mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
          try {
              $connection->begin_transaction();

              if ($userType === 'employee') { // Employee
                  $role = "Employee";
                  $status = "Active";

                  $stmt = $connection->prepare("SELECT COUNT(*) FROM email WHERE email=?");
                  $stmt->bind_param("s", $email);
                  $stmt->execute();
                  $stmt->bind_result($count);
                  $stmt->fetch();
                  $stmt->close();

                  if ($count > 0) {
                      $_SESSION['error'] = "Email already exists.";
                      header("Location: admin.php"); 
                      exit(); 
                  }
                  $stmt1 = $connection->prepare("INSERT INTO email (email) VALUES (?)");
                  $stmt1->bind_param("s", $email);
                  $stmt1->execute();
                  $EID = $connection->insert_id;
                  $stmt1->close();
                  $stmt = $connection->prepare("SELECT COUNT(*) FROM employee WHERE empNum = ?");
                  $stmt->bind_param("s", $var2);
                  $stmt->execute();
                  $stmt->bind_result($empCount);
                  $stmt->fetch();
                  $stmt->close();

                  if ($empCount > 0) {
                      $_SESSION['error'] = "Employee Number already exists.";
                      header("Location: admin.php");
                      exit();
                  }
                  $stmt2 = $connection->prepare("INSERT INTO employee (empNum, empName) VALUES (?, ?)");
                  $stmt2->bind_param("ss", $var2, $var1);
                  $stmt2->execute();
                  $EMPID = $connection->insert_id;
                  $stmt2->close();

                  $stmt3 = $connection->prepare("SELECT * FROM userroles WHERE roleName = ?");
                  $stmt3->bind_param("s", $role);
                  $stmt3->execute();
                  $ROW = $stmt3->get_result();
                  $ROWS = $ROW->fetch_assoc();
                  $RID = $ROWS['roleID'];
                  $stmt3->close();

                  $stmt4 = $connection->prepare("INSERT INTO users (username, password, roleID, status) VALUES (?, ?, ?, ?)");
                  $stmt4->bind_param("ssis", $email, $pass, $RID, $status);
                  $stmt4->execute();
                  $UID = $connection->insert_id;
                  $stmt4->close();

                  $stmt5 = $connection->prepare("INSERT INTO userinfo (userID, empID, emailID, cont_num) VALUES (?, ?, ?, ?)");
                  $stmt5->bind_param("iiis", $UID, $EMPID, $EID, $contact);
                  $stmt5->execute();
                  $stmt5->close();

                  $newUserData = [
                      'name' => $var1,
                      'empNumber' => $var2,
                      'contact' => $contact,
                      'email' => $email,
                      'role' => 'Employee'
                  ];
              } else { 
                  $role = "Supplier";
                  $status = "Active";

                  $stmt1 = $connection->prepare("INSERT INTO email (email) VALUES (?)");
                  $stmt1->bind_param("s", $email);
                  $stmt1->execute();
                  $EID = $connection->insert_id;
                  $stmt1->close();

                  $stmt = $connection->prepare("SELECT COUNT(*) FROM email WHERE email=?");
                  $stmt->bind_param("s", $email);
                  $stmt->execute();
                  $stmt->bind_result($count);
                  $stmt->fetch();
                  $stmt->close();

                  if ($count > 0) {
                      $_SESSION['error'] = "Email already exists.";
                      header("Location: admin.php"); 
                      exit(); 
                  }

                  $stmt2 = $connection->prepare("INSERT INTO company (companyName, companyPerson) VALUES (?, ?)");
                  $stmt2->bind_param("ss", $var1, $var2);
                  $stmt2->execute();
                  $EMPID = $connection->insert_id;
                  $stmt2->close();

                  $stmt3 = $connection->prepare("SELECT * FROM userroles WHERE roleName = ?");
                  $stmt3->bind_param("s", $role);
                  $stmt3->execute();
                  $ROW = $stmt3->get_result();
                  $ROWS = $ROW->fetch_assoc();
                  $RID = $ROWS['roleID'];
                  $stmt3->close();

                  $stmt4 = $connection->prepare("INSERT INTO users (username, password, roleID, status) VALUES (?, ?, ?, ?)");
                  $stmt4->bind_param("ssis", $email, $pass, $RID, $status);
                  $stmt4->execute();
                  $UID = $connection->insert_id;
                  $stmt4->close();

                  $stmt5 = $connection->prepare("INSERT INTO suppliers (comID, PhoneNumber, Status) VALUES (?, ?, 'Active')");
                  $stmt5->bind_param("is", $EMPID, $contact);
                  $stmt5->execute();
                  $SupplierID = $connection->insert_id;
                  $stmt5->close();

                  $stmt6 = $connection->prepare("INSERT INTO userinfo (userID, SupplierID, emailID, cont_num) VALUES (?, ?, ?, ?)");
                  $stmt6->bind_param("iiis", $UID, $SupplierID, $EID, $contact);
                  $stmt6->execute();
                  $stmt6->close();

                  $newUserData = [
                      'name' => $var1,
                      'empNumber' => $var2,
                      'contact' => $contact,
                      'email' => $email,
                      'role' => 'Supplier'
                  ];
              }

              $connection->commit();
              $success = true;
          } catch (mysqli_sql_exception $e) {
              $connection->rollback();
              $errorMsg = "MySQL Error: " . $e->getMessage();
              $_SESSION['error'] = $errorMsg;
          }
      }
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_submit'])) {

    $id = intval($_POST['userID']);
    $name = trim($_POST['editFullName']);
    $empNumber = trim($_POST['editEmpNumber']);
    $contact = trim($_POST['editContact']);
    $email = trim($_POST['editEmail']);
    $role = trim($_POST['editRole']); 

    $errors = [];
    if (!$id) $errors[] = 'Invalid user ID.';
    if (!$name) $errors[] = 'Name is required.';
    if (!$empNumber) $errors[] = 'Employee/Company Number is required.';
    if (!$contact || !preg_match('/^\d{7,15}$/', $contact)) $errors[] = 'Contact number must be 7-15 digits.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header("Location: admin.php");
        exit();
    }

    try {
        $connection->begin_transaction();

        $stmt = $connection->prepare("
            SELECT COUNT(*) FROM email e
            JOIN userinfo ui ON e.emailID = ui.emailID
            WHERE e.email = ? AND ui.userID != ?
        ");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $stmt->bind_result($emailCount);
        $stmt->fetch();
        $stmt->close();

        if ($emailCount > 0) {
            $_SESSION['error'] = "Email already exists.";
            $connection->rollback();
            header("Location: admin.php");
            exit();
        }

        $stmt = $connection->prepare("
            UPDATE email e
            JOIN userinfo ui ON ui.emailID = e.emailID
            JOIN users u ON u.userID = ui.userID
            SET e.email = ?, u.username = ?
            WHERE ui.userID = ?
        ");
        $stmt->bind_param("ssi", $email, $email, $id);
        $stmt->execute();
        $stmt->close();

        if ($role === 'Employee') {

            $stmt = $connection->prepare("
                UPDATE employee emp
                JOIN userinfo ui ON ui.empID = emp.empID
                SET emp.empName = ?, emp.empNum = ?
                WHERE ui.userID = ?
            ");
        } else { 

            $stmt = $connection->prepare("
                UPDATE company c
                JOIN suppliers s ON s.comID = c.comID
                JOIN userinfo ui ON ui.SupplierID = s.SupplierID
                SET c.companyName = ?, c.companyPerson = ?
                WHERE ui.userID = ?
            ");
        }
        $stmt->bind_param("ssi", $name, $empNumber, $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $connection->prepare("
            UPDATE userinfo SET cont_num=? WHERE userID=?
        ");
        $stmt->bind_param("si", $contact, $id);
        $stmt->execute();
        $stmt->close();

        $connection->commit();
        $_SESSION['success'] = "Account updated successfully";

    } catch (Exception $e) {
        $connection->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_user'])) {
    $userID = intval($_POST['userID']);
    if ($userID > 0) {
        $stmt = $connection->prepare("UPDATE users SET status='Disabled' WHERE userID=?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->close();
    }
    exit('success');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reactivate_user'])) {
    $userID = intval($_POST['userID']);
    if ($userID > 0) {
        $stmt = $connection->prepare("UPDATE users SET status='Active' WHERE userID=?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->close();
    }
    exit('success');
}

  $accounts = [];
  try {
$sql = "
SELECT u.userID, u.username, u.status, ur.roleName, ui.cont_num,
       emp.empName, emp.empNum, com.companyName
FROM users u
JOIN userroles ur ON u.roleID = ur.roleID
LEFT JOIN userinfo ui ON u.userID = ui.userID
LEFT JOIN employee emp ON ui.empID = emp.empID
LEFT JOIN suppliers s ON ui.SupplierID = s.SupplierID
LEFT JOIN company com ON s.comID = com.comID
WHERE ur.roleName != 'admin' AND ur.roleName != 'supplier'
ORDER BY u.userID DESC
";
            $res = $connection->query($sql);
            if ($res) {
                while ($r = $res->fetch_assoc()) {
          $displayName = $r['empName'] ?? $r['companyName'] ?? '';
          $empNumber = $r['empNum'] ?? '';
          $status = $r['status'] ?? 'Active';

          $rowClass = ($status === 'Disabled') ? 'disabled-row' : '';

          $accounts[] = [
              'userID' => $r['userID'],
              'name' => $displayName,
              'empNumber' => $empNumber,
              'contact' => $r['cont_num'] ?? '',
              'email' => $r['username'],
              'role' => ucfirst($r['roleName']),
              'status' => $status,
              'rowClass' => $rowClass  
          ];
      }
          $res->free();
      }
  } catch (Exception $e) {

  }

  $connection->close();
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="dashboard.css" />
    <link rel="stylesheet" href="notification.css">
      <style>
          .has-dropdown {
  position: relative;
}

.has-dropdown .dropdown-menu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  list-style: none;
  padding: 0;
  margin: 0;
  width: 220px;
  border-radius: var(--radius);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  z-index: 10;
}

.has-dropdown:hover .dropdown-menu {
  display: block;
}

.has-dropdown .dropdown-menu li {
  padding: 12px 16px;
  cursor: pointer;
  transition: 0.2s;
}

.has-dropdown .dropdown-menu li:hover {
  background-color: #f0f6ff;
}

      .button-group { display:flex; gap:8px; margin-bottom:12px; }
      .button-group button { padding:8px 12px; border:1px solid #ccc; background:#fff; cursor:pointer; border-radius:6px; }
      .button-group button.active { background:#007bff; color:#fff; border-color:#007bff; }
      .form-box { padding:10px 0; }
      .form-row { display:flex; gap:12px; }
      .input-group { display:flex; flex-direction:column; margin-bottom:8px; flex:1; }
      .input-group input, .input-group select { padding:8px; border-radius:4px; border:1px solid #ccc; }
      .full-width { width:100%; }
      .popup { display:none; position:fixed; inset:0; align-items:center; justify-content:center; background:rgba(0,0,0,0.4); }
      .popup-content { background:#fff; padding:20px; border-radius:8px; text-align:center; }
      .error-message { color:#b00; padding:8px; }
    </style>

  </head>
  <body>
    
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
          <li class="has-dropdown">
  <i class="fa-solid fa-file-lines"></i>
  <span>Reports</span>
  <ul class="dropdown-menu">
    <li class="report-link" data-view="inventory-management">Inventory Management</li>
    <li class="report-link" data-view="expiration-wastage">Expiration / Wastage</li>
</ul>
</li>
        <li id="users" class="active"><i class="fa-solid fa-users"></i><span>Users</span></li>
        <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
      </ul>
    </aside>


    <div class="main">
          <div class="heading-bar">
        <h1>Accounts Overview</h1><div class="topbar-right">
        <?php include 'notification_component.php'; ?>
        <div class="profile-icon">
          <i class="fa-solid fa-user"></i>
        </div>
      </div>   
      </div>

    <div class="container">
    <div class="container-header">
        <h2>Accounts</h2>
        <a href="#" class="btn add-account" id="openModalBtn">
  <i class="fa-solid fa-plus"></i> Add Account
</a>

      </div>
      
      <div class="search-bar">
        <input id="searchInput" type="text" placeholder="Search account...">
      </div>

      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Employee Number</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="accountsTbody">
          <?php foreach ($accounts as $acc): ?>
          <tr class="<?= $acc['rowClass'] ?>"
              data-id="<?= $acc['userID'] ?>"
              data-full-name="<?= htmlspecialchars($acc['name']) ?>"
              data-emp-number="<?= htmlspecialchars($acc['empNumber']) ?>"
              data-contact="<?= htmlspecialchars($acc['contact']) ?>"
              data-email="<?= htmlspecialchars($acc['email']) ?>"
              data-role="<?= htmlspecialchars($acc['role']) ?>"
              data-status="<?= htmlspecialchars($acc['status'] ?? 'Active') ?>">
            <td class="cell-name"><?= htmlspecialchars($acc['name']) ?></td>
            <td class="cell-email"><?= htmlspecialchars($acc['email']) ?></td>
            <td class="cell-email"><?= htmlspecialchars($acc['contact']) ?></td>
            <td class="cell-email"><?= htmlspecialchars($acc['empNumber'] ?? '--') ?></td>
            <td class="cell-role"><?= htmlspecialchars($acc['role']) ?></td>
            <td class="cell-status"><?= htmlspecialchars($acc['status'] ?? 'Active') ?></td>
            <td>
            <?php if($acc['status'] === 'Disabled'): ?>
                <button class="action-btn reactivate-btn">Reactivate</button>
            <?php else: ?>
                <button class="action-btn edit-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                <button class="action-btn delete-btn"><i class="fa-solid fa-trash"></i> Delete</button>
            <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </div>

    <div class="modal" id="addModal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none;">
      <div class="modal-content">
        <h3>Register New Account</h3>

        <div class="form-box">
          <div class="button-group">
            <button id="employeeBtn" type="button" class="active">Employee</button>
            
            <button id="supplierBtn" type="button">Supplier</button>
           
          </div>

          <form id="registrationForm" method="post" action="admin.php">
            <input type="hidden" name="userType" id="userType" value="employee">
            <div class="form-row">
              <div class="input-group">
                <label id="label1">Full Name:</label>
                <input name="empNameorComName" id="addFullName" type="text" required>
              </div>
              <div class="input-group">
                <label id="label2">Employee Number:</label>
                <input name="empNumorContPer" id="addEmpNumber" type="text" required>
              </div>
            </div>

            <div class="form-row">
              <div class="input-group">
                <label>Contact Number:</label>
                <input name="contNum" id="addContact" type="text" required>
              </div>
              <div class="input-group">
                <label>Email:</label>
                <input name="eMail" id="addEmail" type="email" required>
              </div>
            </div>

            <div class="input-group full-width">
              <label>Password:</label>
              <input name="pass" id="addPassword" type="password" required>
            </div>

            <div class="input-group full-width">
              <label>Confirm Password:</label>
              <input id="addConfirmPassword" type="password" required>
            </div>

            <div class="modal-actions" style="margin-top:12px;">
              <button type="button" class="cancel-btn" id="closeModalBtn">Cancel</button>
              <button type="submit" class="save-btn" id="addSubmitBtn" name="registration_submit">Submit</button>
            </div>
          </form>
        </div>

      </div>
    </div>


    <div class="modal" id="editModal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none;">
      <div class="modal-content">
        <h3>Edit Account</h3>
        <form id="editForm" method="POST" action="admin.php">
        <div class="form-grid">
          <input type="hidden" name="userID" id="editUserID">
      <input type="hidden" name="edit_submit" value="1">
          <div class="field">
            <label class="field-label" for="editFullName">Full Name:</label>
            <input id="editFullName" name="editFullName" type="text" placeholder="Full Name">
          </div>

          <div class="field">
            <label class="field-label" for="editEmpNumber">Employee Number:</label>
            <input id="editEmpNumber" name="editEmpNumber" type="text" placeholder="Employee Number">
          </div>

          <div class="field">
            <label class="field-label" for="editContact">Contact Number:</label>
            <input id="editContact" name="editContact" type="text" placeholder="Contact Number">
          </div>

          <div class="field">
            <label class="field-label" for="editEmail">Email:</label>
            <input id="editEmail" name="editEmail" type="email" placeholder="Email Address">
          </div>
        </div>
          </form>

        <div class="single-field">
          <label class="field-label" for="editRole">Role:</label>
          <select id="editRole">
            <option value="">Select Role</option>
            <option value="Employee">Employee</option>
            <option value="Supplier">Supplier</option>
          </select>
        </div>

        <div class="modal-actions">
          <button class="cancel-btn" id="closeEditModal">Cancel</button>
          <button class="save-btn" id="editSaveBtn">Save Changes</button>
        </div>
      </div>
    </div>

    <div class="modal" id="deleteModal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none;">
      <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this account?</p>
        <div class="modal-actions">
          <button class="cancel-btn" id="closeDeleteModal">Cancel</button>
          <button class="save-btn" id="confirmDeleteBtn" style="background-color:#dc3545;">Delete</button>
        </div>
      </div>
    </div>

  <div class="modal" id="errorModal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none;">
    <div class="modal-content">
      <h3>Error</h3>
      <p id="errorMessage" style="color:#b00;"></p>
      <div class="modal-actions">
        <button class="save-btn" id="closeErrorModal" style="background-color:#007bff;">OK</button>
      </div>
    </div>
  </div>

    <div id="popup" class="popup">
      <div class="popup-content">
        <p>You have successfully registered!</p>
        <button id="okBtn">OK</button>
      </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const errorModal = document.getElementById('errorModal');
    const errorMessage = document.getElementById('errorMessage');
    const closeErrorBtn = document.getElementById('closeErrorModal');

    errorMessage.innerHTML = <?= json_encode($_SESSION['error']) ?>;
    errorModal.style.display = 'flex';
    errorModal.setAttribute('aria-hidden', 'false');

    closeErrorBtn.addEventListener('click', () => {
      errorModal.style.display = 'none';
      errorModal.setAttribute('aria-hidden', 'true');
    });

    window.addEventListener('click', (e) => {
      if (e.target === errorModal) {
        errorModal.style.display = 'none';
        errorModal.setAttribute('aria-hidden', 'true');
      }
    });
  });
</script>
<?php unset($_SESSION['error']); endif; ?>

    <script src="sidebar.js"></script>

    <script>

      const addModal = document.getElementById('addModal');
      const editModal = document.getElementById('editModal');
      const deleteModal = document.getElementById('deleteModal');

      const openAddBtn = document.getElementById('openModalBtn');
      const closeAddBtn = document.getElementById('closeModalBtn');
      const closeEditBtn = document.getElementById('closeEditModal');
      const closeDeleteBtn = document.getElementById('closeDeleteModal');

      const addSubmitBtn = document.getElementById('addSubmitBtn');
      const accountsTbody = document.getElementById('accountsTbody');
      const searchInput = document.getElementById('searchInput');

      const addFullName = document.getElementById('addFullName');
      const addEmpNumber = document.getElementById('addEmpNumber');
      const addContact = document.getElementById('addContact');
      const addEmail = document.getElementById('addEmail');
      const addPassword = document.getElementById('addPassword');
      const addConfirmPassword = document.getElementById('addConfirmPassword');
      const userTypeInput = document.getElementById('userType');

      const editFullName = document.getElementById('editFullName');
      const editEmpNumber = document.getElementById('editEmpNumber');
      const editContact = document.getElementById('editContact');
      const editEmail = document.getElementById('editEmail');

      const editSaveBtn = document.getElementById('editSaveBtn');


      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');


      const employeeBtn = document.getElementById('employeeBtn');
      const supplierBtn = document.getElementById('supplierBtn');
      const label1 = document.getElementById('label1');
      const label2 = document.getElementById('label2');

      let editingRow = null;
      let deletingRow = null;

      function openModal(modal) {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
      }
      function closeModal(modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
      }

      function clearAddForm() {
        addFullName.value = '';
        addEmpNumber.value = '';
        addContact.value = '';
        addEmail.value = '';
        userTypeInput.value = 'employee';
        addPassword.value = '';
        addConfirmPassword.value = '';

        label1.textContent = 'Full Name:';
        label2.textContent = 'Employee Number:';
        employeeBtn.classList.add('active');
        supplierBtn.classList.remove('active');
      }

      function createRow(data) {
        const tr = document.createElement('tr');
        tr.dataset.id = data.id;
        tr.innerHTML = `
          <td class="cell-name"></td>
          <td class="cell-email"></td>
          <td class="cell-role"></td>
          <td>
            <button class="action-btn edit-btn">Edit</button>
            <button class="action-btn delete-btn">Delete</button>
          </td>
        `;

        tr.querySelector('.cell-name').textContent = data.name;
        tr.querySelector('.cell-email').textContent = data.email;
        tr.querySelector('.cell-role').textContent = data.role;

        // store additional data attributes for edit convenience
        tr.dataset.fullName = data.name;
        tr.dataset.empNumber = data.empNumber || '';
        tr.dataset.contact = data.contact || '';
        tr.dataset.email = data.email;
        tr.dataset.role = data.role;

        return tr;
      }

      function validateAdd() {
        if (!addFullName.value.trim()) { alert('Please enter full name / company name'); addFullName.focus(); return false; }
        if (!addEmail.value.trim()) { alert('Please enter email'); addEmail.focus(); return false; }
        if (!userTypeInput.value) { alert('Please select a role'); return false; }
        if (addPassword.value !== addConfirmPassword.value) { alert('Passwords do not match'); addPassword.focus(); return false; }
        return true;
      }

      openAddBtn.addEventListener('click', () => {
        clearAddForm();
        openModal(addModal);
      });
      closeAddBtn.addEventListener('click', () => closeModal(addModal));

      document.getElementById('registrationForm').addEventListener('submit', (e) => {
        if (!validateAdd()) {
          e.preventDefault();
          return false;
        }

      });

      accountsTbody.addEventListener('click', (e) => {
    const tr = e.target.closest('tr');
    if (!tr) return;
    const userID = tr.dataset.id;
    const actionsCell = tr.querySelector('td:last-child');
    const statusCell = tr.querySelector('.cell-status');

    if (e.target.classList.contains('edit-btn')) {
        editingRow = tr;
        document.getElementById('editUserID').value = tr.dataset.id;
        editFullName.value = tr.dataset.fullName || '';
        editEmpNumber.value = tr.dataset.empNumber || '';
        editContact.value = tr.dataset.contact || '';
        editEmail.value = tr.dataset.email || '';
        openModal(editModal);
    }

    if (e.target.classList.contains('delete-btn')) {
        if (!confirm("Are you sure you want to deactivate this account?")) return;
        fetch('admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `deactivate_user=1&userID=${userID}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                statusCell.textContent = 'Disabled';
                tr.classList.add('disabled-row');

                actionsCell.innerHTML = `<button class="action-btn reactivate-btn">Reactivate</button>`;
            } else {
                alert('Error deactivating account: ' + data);
            }
        }).catch(err => { console.error(err); alert('Error deactivating account.'); });
    }

    if (e.target.classList.contains('reactivate-btn')) {
        if (!confirm("Are you sure you want to reactivate this account?")) return;
        fetch('admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `reactivate_user=1&userID=${userID}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                statusCell.textContent = 'Active';
                tr.classList.remove('disabled-row');

                actionsCell.innerHTML = `
                    <button class="action-btn edit-btn">Edit</button>
                    <button class="action-btn delete-btn">Delete</button>
                `;
            } else {
                alert('Failed to reactivate user: ' + data);
            }
        }).catch(err => { console.error(err); alert('Error reactivating user.'); });
    }
});

      editSaveBtn.addEventListener('click', () => {
        if (!editingRow) return;

        if (!editFullName.value.trim()) { alert('Please enter full name'); editFullName.focus(); return; }
        if (!editEmail.value.trim()) { alert('Please enter email'); editEmail.focus(); return; }

        // update cells and dataset
        editingRow.dataset.fullName = editFullName.value.trim();
        editingRow.dataset.empNumber = editEmpNumber.value.trim();
        editingRow.dataset.contact = editContact.value.trim();
        editingRow.dataset.email = editEmail.value.trim();


        editingRow.querySelector('.cell-name').textContent = editingRow.dataset.fullName;
        editingRow.querySelector('.cell-email').textContent = editingRow.dataset.email;
        editingRow.querySelector('.cell-role').textContent = editingRow.dataset.role;

        editingRow = null;
        closeModal(editModal);
      });
      editSaveBtn.addEventListener('click', () => {
          const editForm = document.getElementById('editForm');

          if (!editFullName.value.trim()) { 
              alert('Please enter full name'); 
              editFullName.focus(); 
              return; 
          }
          if (!editEmail.value.trim()) { 
              alert('Please enter email'); 
              editEmail.focus(); 
              return; 
          }

          editForm.submit();
      });

      closeEditBtn.addEventListener('click', () => { editingRow = null; closeModal(editModal); });


      confirmDeleteBtn.addEventListener('click', () => {
    if (!deletingRow) return;
    
    const userID = deletingRow.dataset.id;

    fetch('admin.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `deactivate_user=1&userID=${userID}`
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {

    if (data.trim() === 'success') {
        deletingRow.querySelector('.cell-status').textContent = 'Disabled';
        deletingRow.classList.add('disabled-row'); 
    }        
    } else {
            alert('Error deactivating account: ' + data);
        }
        deletingRow = null;
        closeModal(deleteModal);
    })
    .catch(err => {
        alert('Error deactivating account');
        console.error(err);
    });
});
            closeDeleteBtn.addEventListener('click', () => { deletingRow = null; closeModal(deleteModal); });

      window.addEventListener('click', (e) => {
        if (e.target === addModal) closeModal(addModal);
        if (e.target === editModal) { editingRow = null; closeModal(editModal); }
        if (e.target === deleteModal) { deletingRow = null; closeModal(deleteModal); }
      });


      searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        Array.from(accountsTbody.children).forEach(tr => {
          const name = (tr.dataset.fullName || '').toLowerCase();
          const email = (tr.dataset.email || '').toLowerCase();
          const role = (tr.dataset.role || '').toLowerCase();
          const match = !q || name.includes(q) || email.includes(q) || role.includes(q);
          tr.style.display = match ? '' : 'none';
        });
      });


      employeeBtn.addEventListener('click', () => {
        employeeBtn.classList.add('active');
        supplierBtn.classList.remove('active');
        label1.textContent = 'Full Name:';
        label2.textContent = 'Employee Number:';
        userTypeInput.value = 'employee';
      });

      supplierBtn.addEventListener('click', () => {
        supplierBtn.classList.add('active');
        employeeBtn.classList.remove('active');
        label1.textContent = 'Company Name:';
        label2.textContent = 'Contact Person:';
        userTypeInput.value = 'supplier';
      });

      $(document).ready(function () {
        //Navigation
        $("#dashboard").click(function(){ window.location.href = "dashboard.php"; });
        $("#inventory").click(function(){ window.location.href = "Inventory.php";});
        $("#low-stock").click(function(){ window.location.href = "lowstock.php"; });
        $("#request").click(function(){ window.location.href = "request_list.php"; });
        $("#nav-suppliers").click(function(){ window.location.href ="supplier.php"; });
        $("#reports").click(function(){ window.location.href = "report.php"; });
        $("#users").click(function(){ window.location.href = "admin.php"; });
        $("#settings").click(function(){ window.location.href = "settings.php"; });
        $("#logout").click(function(){ window.location.href = "logout.php"; });
      });

      const registrationSuccess = <?= json_encode($success) ?>;
      const newUser = <?= json_encode($newUserData) ?>;

      const popup = document.getElementById('popup');
      const okBtn = document.getElementById('okBtn');
      if (registrationSuccess) {
      popup.style.display = 'flex';
    }

      okBtn.addEventListener('click', function() {
        popup.style.display = 'none';

        closeModal(addModal);
      });

$("#reports").click(function(e){
    e.stopPropagation();
    $(this).toggleClass("active");
});

$(document).click(function(e){
    if(!$(e.target).closest("#reports").length){
        $("#reports").removeClass("active");
    }
});


$(".report-link").click(function(){
    const view = $(this).data("view");
    $("#view-title").text($(this).text());
    $("#view-content").removeClass("cards-container").html(views[view]);
    validateInventoryReport();
    $("#reports").removeClass("active"); 
});

$(".report-link").click(function(e){
    e.stopPropagation(); 
    const view = $(this).data("view");
    $("#view-title").text($(this).text());
    $("#view-content").removeClass("cards-container").html(views[view]);
    validateInventoryReport(); 
});

const reportLinks = {
    "inventory-management": "report_inventory.php",
    "pos-requests": "report_pos.php",
    "expiration-wastage": "report_expiration.php"
};


document.querySelectorAll(".report-link").forEach(link => {
    link.addEventListener("click", () => {
        const view = link.dataset.view;
        const href = reportLinks[view];
        if(href) window.location.href = href;
    });
});

    </script>

  </body>
  </html>
