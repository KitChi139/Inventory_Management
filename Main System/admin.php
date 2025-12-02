  <?php
  session_start();
  if (!isset($_SESSION['loggedin']) || $_SESSION['roleName'] !== 'Admin') {
      header("Location: dashboard.php");
      exit();
  }
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  /* ---------- DB Connection ---------- */
  $connection = new mysqli("localhost", "root", "", "inventory_db");
  if ($connection->connect_error) {
      die("Connection Failed: " . $connection->connect_error);
  }

  /* ---------- Handle Registration POST ---------- */
  $success = false;
  $newUserData = null;
  $errorMsg = null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_submit'])) {
      $userType =  $_POST['userType'] ?? '';
      $var1 = trim($_POST['empNameorComName'] ?? '');
      $var2 = trim($_POST['empNumorContPer'] ?? '');
      $contact = trim($_POST['contNum'] ?? '');
      $email = isset($_POST['eMail']) ? strtolower(trim($_POST['email'])) : null;
      $pass = $_POST['pass'] ?? '';

      // Basic server-side validation
      if (!$var1 || !$var2 || !$contact || !$email || !$pass || !$userType) {
          $_SESSION['error'] = 'Please fill all required fields.';
      } else {
          mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
          try {
              $connection->begin_transaction();

              if ($userType === 'employee') { // Employee
                  $role = "Employee";
                  $status = "Active";

                  $stmt1 = $connection->prepare("INSERT INTO email (email) VALUES (?)");
                  $stmt1->bind_param("s", $email);
                  $stmt1->execute();
                  $EID = $connection->insert_id;
                  $stmt1->close();

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

                  // Prepare data for returning to client
                  $newUserData = [
                      'name' => $var1,
                      'empNumber' => $var2,
                      'contact' => $contact,
                      'email' => $email,
                      'role' => 'Employee'
                  ];
              } else { // Supplier
                  $role = "Supplier";
                  $status = "Active";

                  $stmt1 = $connection->prepare("INSERT INTO email (email) VALUES (?)");
                  $stmt1->bind_param("s", $email);
                  $stmt1->execute();
                  $EID = $connection->insert_id;
                  $stmt1->close();

                  $stmt2 = $connection->prepare("INSERT INTO company (comName, comPerson) VALUES (?, ?)");
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

                  $stmt5 = $connection->prepare("INSERT INTO userinfo (userID, comID, emailID, cont_num) VALUES (?, ?, ?, ?)");
                  $stmt5->bind_param("iiis", $UID, $EMPID, $EID, $contact);
                  $stmt5->execute();
                  $stmt5->close();

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

      try {
          $connection->begin_transaction();

          // Update email
          $stmt = $connection->prepare("
    UPDATE email e
    JOIN userinfo ui ON ui.emailID = e.emailID
    JOIN users u ON u.userID = ui.userID
    SET e.email = ?, u.username = ?
    WHERE ui.userID = ?
");
$stmt->bind_param("ssi", $email, $email, $id); // <-- same value for both
$stmt->execute();
$stmt->close();


          // Update employee/supplier name & ID
          $stmt = $connection->prepare("
            UPDATE employee emp
            JOIN userinfo ui ON ui.empID = emp.empID
            SET emp.empName = ?, emp.empNum = ?
            WHERE ui.userID = ?
          ");
          $stmt->bind_param("ssi", $name, $empNumber, $id);
          $stmt->execute();
          $stmt->close();

          // Update userinfo contact
          $stmt = $connection->prepare("
            UPDATE userinfo SET cont_num=? WHERE userID=?
          ");
          $stmt->bind_param("si", $contact, $id);
          $stmt->execute();
          $stmt->close();

          $connection->commit();
          $_SESSION['success'] = "Account updated";

      } catch (Exception $e) {
          $connection->rollback();
          $_SESSION['error'] = $e->getMessage();
      }

      header("Location: admin.php");
      exit();
  }

  /* ---------- Fetch existing accounts for display in table ---------- */
  $accounts = [];
  try {
    $sql = "
  SELECT u.userID, u.username, ur.roleName, ui.cont_num,
        emp.empName, emp.empNum, com.comName
  FROM users u
  JOIN userroles ur ON u.roleID = ur.roleID
  LEFT JOIN userinfo ui ON u.userID = ui.userID
  LEFT JOIN employee emp ON ui.empID = emp.empID
  LEFT JOIN company com ON ui.comID = com.comID
  WHERE ur.roleName != 'admin'
  ORDER BY u.userID DESC
  ";

      $res = $connection->query($sql);
      if ($res) {
          while ($r = $res->fetch_assoc()) {
              $displayName = $r['empName'] ?? $r['comName'] ?? '';
              $empNumber = $r['empNum'] ?? '';
              $accounts[] = [
                  'userID' => $r['userID'],
                  'name' => $displayName,
                  'empNumber' => $empNumber,
                  'contact' => $r['cont_num'] ?? '',
                  'email' => $r['username'],
                  'role' => ucfirst($r['roleName'])
              ];
          }
          $res->free();
      }
  } catch (Exception $e) {
      // ignore fetch errors for display; optionally log them
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
      /* Minimal styling for registration toggle inside modal to match registration.php look */
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
        <li id="low-stock"><i class="fa-solid fa-triangle-exclamation"></i><span>Low Stock</span></li>
        <li id="request"><i class="fa-solid fa-file-pen"></i><span>Requests</span></li>
        <li id="nav-suppliers"><i class="fa-solid fa-truck"></i><span>Suppliers</span></li>
        <li id="reports"><i class="fa-solid fa-file-lines"></i><span>Reports</span></li>
        <li id="users" class="active"><i class="fa-solid fa-users"></i><span>Users</span></li>
        <li id="settings"><i class="fa-solid fa-gear"></i><span>Settings</span></li>
        <li id="logout"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></li>
      </ul>
    </aside>


    <div class="main">
          <!-- Notification + Profile icon (top-right in main content) -->
      <div class="topbar-right">
        <?php include 'notification_component.php'; ?>
        <div class="profile-icon">
          <i class="fa-solid fa-user"></i>
        </div>
      </div>
          <div class="heading-bar">
        <h1>Accounts Overview</h1>   
      </div>

    <div class="container">
    <div class="container-header">
        <h2>Accounts</h2>
        <button class="add-btn" id="openModalBtn">Add Account</button>
      </div>
      
      <div class="search-bar">
        <input id="searchInput" type="text" placeholder="Search account...">
      </div>

      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="accountsTbody">
          <!-- Pre-populated server-side rows -->
          <?php foreach ($accounts as $acc): ?>
            <tr data-id="<?=$acc['userID']?>"
                data-full-name="<?= htmlspecialchars($acc['name']) ?>"
                data-emp-number="<?= htmlspecialchars($acc['empNumber']) ?>"
                data-contact="<?= htmlspecialchars($acc['contact']) ?>"
                data-email="<?= htmlspecialchars($acc['email']) ?>"
                data-role="<?= htmlspecialchars($acc['role']) ?>">
              <td class="cell-name"><?= htmlspecialchars($acc['name']) ?></td>
              <td class="cell-email"><?= htmlspecialchars($acc['email']) ?></td>
              <td class="cell-role"><?= htmlspecialchars($acc['role']) ?></td>
              <td>
                <button class="action-btn edit-btn">Edit</button>
                <button class="action-btn delete-btn">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

    <!-- ADD / REGISTRATION MODAL (replaces your old add modal) -->
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

    <!-- EDIT MODAL -->
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

        <!-- <div class="single-field">
          <label class="field-label" for="editRole">Role:</label>
          <select id="editRole">
            <option value="">Select Role</option>
            <option value="Employee">Employee</option>
            <option value="Supplier">Supplier</option>
          </select>
        </div> -->

        <div class="modal-actions">
          <button class="cancel-btn" id="closeEditModal">Cancel</button>
          <button class="save-btn" id="editSaveBtn">Save Changes</button>
        </div>
      </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
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

    <!-- Success Popup -->
    <div id="popup" class="popup">
      <div class="popup-content">
        <p>You have successfully registered!</p>
        <button id="okBtn">OK</button>
      </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

      <script src="sidebar.js"></script>

    <script>
      // DOM refs
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

      // Add form fields
      const addFullName = document.getElementById('addFullName');
      const addEmpNumber = document.getElementById('addEmpNumber');
      const addContact = document.getElementById('addContact');
      const addEmail = document.getElementById('addEmail');
      const addPassword = document.getElementById('addPassword');
      const addConfirmPassword = document.getElementById('addConfirmPassword');
      const userTypeInput = document.getElementById('userType');

      // Edit form fields
      const editFullName = document.getElementById('editFullName');
      const editEmpNumber = document.getElementById('editEmpNumber');
      const editContact = document.getElementById('editContact');
      const editEmail = document.getElementById('editEmail');
      // const editRole = document.getElementById('editRole');
      const editSaveBtn = document.getElementById('editSaveBtn');

      // Delete confirm
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

      // Employee / Supplier toggle
      const employeeBtn = document.getElementById('employeeBtn');
      const supplierBtn = document.getElementById('supplierBtn');
      const label1 = document.getElementById('label1');
      const label2 = document.getElementById('label2');

      // state
      let editingRow = null;
      let deletingRow = null;

      // helpers
      function openModal(modal) {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
      }
      function closeModal(modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
      }

      // clear add form
      function clearAddForm() {
        addFullName.value = '';
        addEmpNumber.value = '';
        addContact.value = '';
        addEmail.value = '';
        userTypeInput.value = 'employee';
        addPassword.value = '';
        addConfirmPassword.value = '';
        // reset labels
        label1.textContent = 'Full Name:';
        label2.textContent = 'Employee Number:';
        employeeBtn.classList.add('active');
        supplierBtn.classList.remove('active');
      }

      // create table row
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

      // validate add form (simple)
      function validateAdd() {
        if (!addFullName.value.trim()) { alert('Please enter full name / company name'); addFullName.focus(); return false; }
        if (!addEmail.value.trim()) { alert('Please enter email'); addEmail.focus(); return false; }
        if (!userTypeInput.value) { alert('Please select a role'); return false; }
        if (addPassword.value !== addConfirmPassword.value) { alert('Passwords do not match'); addPassword.focus(); return false; }
        return true;
      }

      // open add modal
      openAddBtn.addEventListener('click', () => {
        clearAddForm();
        openModal(addModal);
      });
      closeAddBtn.addEventListener('click', () => closeModal(addModal));

      // submit add: default form POST will handle server side; use client validation to catch early errors
      document.getElementById('registrationForm').addEventListener('submit', (e) => {
        if (!validateAdd()) {
          e.preventDefault();
          return false;
        }
        // allow normal form submit (post to admin.php)
      });

      // event delegation for edit/delete buttons inside tbody
      accountsTbody.addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (!tr) return;

        if (e.target.classList.contains('edit-btn')) {
          // fill edit form
          editingRow = tr;
          document.getElementById('editUserID').value = tr.dataset.id;
          editFullName.value = tr.dataset.fullName || '';
          editEmpNumber.value = tr.dataset.empNumber || '';
          editContact.value = tr.dataset.contact || '';
          editEmail.value = tr.dataset.email || '';
          // editRole.value = tr.dataset.role || '';
          openModal(editModal);
        }

        if (e.target.classList.contains('delete-btn')) {
          deletingRow = tr;
          openModal(deleteModal);
        }
      });

      // save edit
      // editSaveBtn.addEventListener('click', () => {
      //   if (!editingRow) return;
      //   // basic validation
      //   if (!editFullName.value.trim()) { alert('Please enter full name'); editFullName.focus(); return; }
      //   if (!editEmail.value.trim()) { alert('Please enter email'); editEmail.focus(); return; }
      //   // if (!editRole.value) { alert('Please select a role'); editRole.focus(); return; }

      //   // update cells and dataset
      //   editingRow.dataset.fullName = editFullName.value.trim();
      //   editingRow.dataset.empNumber = editEmpNumber.value.trim();
      //   editingRow.dataset.contact = editContact.value.trim();
      //   editingRow.dataset.email = editEmail.value.trim();
      //   // editingRow.dataset.role = editRole.value;

      //   editingRow.querySelector('.cell-name').textContent = editingRow.dataset.fullName;
      //   editingRow.querySelector('.cell-email').textContent = editingRow.dataset.email;
      //   editingRow.querySelector('.cell-role').textContent = editingRow.dataset.role;

      //   editingRow = null;
      //   closeModal(editModal);
      // });
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

          // Submit the form to backend
          editForm.submit();
      });

      closeEditBtn.addEventListener('click', () => { editingRow = null; closeModal(editModal); });

      // delete confirm
      confirmDeleteBtn.addEventListener('click', () => {
        if (!deletingRow) return;
        deletingRow.remove();
        deletingRow = null;
        closeModal(deleteModal);
      });
      closeDeleteBtn.addEventListener('click', () => { deletingRow = null; closeModal(deleteModal); });

      // click outside modal to close
      window.addEventListener('click', (e) => {
        if (e.target === addModal) closeModal(addModal);
        if (e.target === editModal) { editingRow = null; closeModal(editModal); }
        if (e.target === deleteModal) { deletingRow = null; closeModal(deleteModal); }
      });

      // search filter
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

      // Employee / Supplier toggle behavior (for modal)
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

      // Show registration success popup and append new row if server created user
      const registrationSuccess = <?= json_encode($success) ?>;
      const newUser = <?= json_encode($newUserData) ?>;

      const popup = document.getElementById('popup');
      const okBtn = document.getElementById('okBtn');
      if (registrationSuccess) {
      popup.style.display = 'flex';
    }

      okBtn.addEventListener('click', function() {
        popup.style.display = 'none';
        // Close the add modal if it's open
        closeModal(addModal);
      });
    </script>

  </body>
  </html>
