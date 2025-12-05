<?php 
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$connection = new mysqli("localhost", "root", "", "inventory_db");
if ($connection->connect_error) {
    die("Connection Failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType =  $_POST['userType'];
    $var1 = trim($_POST['empNameorComName']);
    $var2 = trim($_POST['empNumorContPer']);
    $contact = trim($_POST['contNum']);
    $email = isset($_POST['eMail']) ? strtolower(trim($_POST['eMail'])) : null;
    $pass = $_POST['pass'];  

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


    try {
      // $checkstmt = $connection ->prepare("SELECT empID, empName, empNum FROM employee WHERE empID = ?");
      // $checkstmt
      
      $connection -> begin_transaction();
      if ($userType === 'employee') { // Employee

          $role = "employee";
          $status = "Active";

          $stmt1 = $connection -> prepare("INSERT INTO email (email) VALUES (?)");
          $stmt1 -> bind_param("s", $email);
          $stmt1 -> execute();
          $EID = $connection -> insert_id;
          $stmt1 -> close();

          $stmt2 = $connection -> prepare("INSERT INTO employee (empNum, empName) VALUES (?,?)");
          $stmt2 -> bind_param("ss", $var2, $var1);
          $stmt2 -> execute();
          $EMPID = $connection -> insert_id;
          $stmt2 -> close();

          $stmt3 = $connection -> prepare("SELECT * FROM userroles WHERE roleName = ?");
          $stmt3 -> bind_param("s", $role);
          $stmt3 -> execute();
          $ROW = $stmt3 -> get_result();
          $ROWS = $ROW -> fetch_assoc();
          $RID = $ROWS['roleID'];
          $stmt3 -> close();

          $stmt4 = $connection -> prepare("INSERT INTO users (username, password, roleID, status) VALUES (?, ?, ?, ?)" );
          $stmt4 -> bind_param("ssis", $email, $pass, $RID, $status);
          $stmt4 -> execute();
          $UID = $connection -> insert_id;
          $stmt4 -> close();

          $stmt5 = $connection -> prepare("INSERT INTO userinfo (userID, empID, emailID, cont_num) VALUES (?, ?, ?, ?)");
          $stmt5 -> bind_param("iiis", $UID, $EMPID, $EID, $contact);
          $stmt5 -> execute();
          $stmt5 -> close();

      } else { // Supplier

          $role = "supplier";
          $status = "Active";
          
          $stmt1 = $connection -> prepare("INSERT INTO email (email) VALUES (?)");
          $stmt1 -> bind_param("s", $email);
          $stmt1 -> execute();
          $EID = $connection -> insert_id;
          $stmt1 -> close();

          $stmt2 = $connection -> prepare("INSERT INTO company (comName, comPerson) VALUES (?,?)");
          $stmt2 -> bind_param("ss", $var1, $var2);
          $stmt2 -> execute();
          $EMPID = $connection -> insert_id;
          $stmt2 -> close();

          $stmt3 = $connection -> prepare("SELECT * FROM userroles WHERE roleName = ?");
          $stmt3 -> bind_param("s", $role);
          $stmt3 -> execute();
          $ROW = $stmt3 -> get_result();
          $ROWS = $ROW -> fetch_assoc();
          $RID = $ROWS['roleID'];
          $stmt3 -> close();

          $stmt4 = $connection -> prepare("INSERT INTO users (username, password, roleID, status) VALUES (?, ?, ?, ?)" );
          $stmt4 -> bind_param("ssis", $email, $pass, $RID, $status);
          $stmt4 -> execute();
          $UID = $connection -> insert_id;
          $stmt4 -> close();

          $stmt5 = $connection -> prepare("INSERT INTO userinfo (userID, comID, emailID, cont_num) VALUES (?, ?, ?, ?)");
          $stmt5 -> bind_param("iiis", $UID, $EMPID, $EID, $contact);
          $stmt5 -> execute();
          $stmt5 -> close();

      }
      $connection -> commit();
      $success = true;
    } catch (mysqli_sql_exception $e) {
      $connection -> rollback();
    echo "<b>MySQL Error:</b> " . $e->getMessage();
    exit;
}

}
$success = $success ?? false; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <header>Accounts

  <a href="logout.php"><i class="fa-solid fa-sign-out"></i><span>Log-Out</span></a>
  </header>

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
        <!-- Rows added dynamically -->
      </tbody>
    </table>
  </div>

  <!-- ADD MODAL -->
  <div class="modal" id="addModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
  <div class="title-section">
      <h2>Registration Form</h2>
      <p>Enter your details to Register</p>
    </div>

    <div class="form-box">
      <div class="button-group">
        <button id="employeeBtn" type="button" class="active">Employee</button>
        <button id="supplierBtn" type="button">Supplier</button>
      </div>

      <form id="registrationForm" method="post" action="registration.php">
      <input type="hidden" name="userType" id="userType" value="employee">  
        <div class="form-row">
          <div class="input-group">
            <label id="label1">Full Name:</label>
            <input name="empNameorComName" type="text" required>
          </div>
          <div class="input-group">
            <label id="label2">Employee Number:</label>
            <input name="empNumorContPer" type="text" required>
          </div>
        </div>

        <div class="form-row">
          <div class="input-group">
            <label>Contact Number:</label>
            <input name="contNum"  type="text" required>
          </div>
          <div class="input-group">
            <label>Email:</label>
            <input name="eMail" type="email" required>
          </div>
        </div>

        <div class="input-group full-width">
          <label>Password:</label>
          <input name="pass" type="password" required>
        </div>

        <div class="input-group full-width">
          <label>Confirm Password:</label>
          <input type="password" required>
        </div>
        <div class="forgot-link">
          <a href="login.php">Already have an account? Log-in Now.</a>
        </div>
        <div class="submit-container">
          <button type="submit" class="submit-btn">Submit</button>
        </div>
      </form>
    </div>

  <!-- EDIT MODAL -->
  <div class="modal" id="editModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
      <h3>Edit Account</h3>

      <div class="form-grid">
        <div class="field">
          <label class="field-label" for="editFullName">Full Name:</label>
          <input id="editFullName" type="text" placeholder="Full Name">
        </div>

        <div class="field">
          <label class="field-label" for="editEmpNumber">Employee Number:</label>
          <input id="editEmpNumber" type="text" placeholder="Employee Number">
        </div>

        <div class="field">
          <label class="field-label" for="editContact">Contact Number:</label>
          <input id="editContact" type="text" placeholder="Contact Number">
        </div>

        <div class="field">
          <label class="field-label" for="editEmail">Email:</label>
          <input id="editEmail" type="email" placeholder="Email Address">
        </div>
      </div>

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

  <!-- DELETE CONFIRM MODAL -->
  <div class="modal" id="deleteModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
      <h3>Confirm Delete</h3>
      <p>Are you sure you want to delete this account?</p>
      <div class="modal-actions">
        <button class="cancel-btn" id="closeDeleteModal">Cancel</button>
        <button class="save-btn" id="confirmDeleteBtn" style="background-color:#dc3545;">Delete</button>
      </div>
    </div>
  </div>

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
    const addRole = document.getElementById('addRole');
    const addPassword = document.getElementById('addPassword');
    const addConfirmPassword = document.getElementById('addConfirmPassword');

    // Edit form fields
    const editFullName = document.getElementById('editFullName');
    const editEmpNumber = document.getElementById('editEmpNumber');
    const editContact = document.getElementById('editContact');
    const editEmail = document.getElementById('editEmail');
    const editRole = document.getElementById('editRole');
    const editSaveBtn = document.getElementById('editSaveBtn');

    // Delete confirm
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

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
      addRole.value = '';
      addPassword.value = '';
      addConfirmPassword.value = '';
    }

    // create table row
    function createRow(data) {
      const tr = document.createElement('tr');
      tr.dataset.id = Date.now().toString();

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
      if (!addFullName.value.trim()) { alert('Please enter full name'); addFullName.focus(); return false; }
      if (!addEmail.value.trim()) { alert('Please enter email'); addEmail.focus(); return false; }
      if (!addRole.value) { alert('Please select a role'); addRole.focus(); return false; }
      if (addPassword.value !== addConfirmPassword.value) { alert('Passwords do not match'); addPassword.focus(); return false; }
      return true;
    }

    // open add modal
    openAddBtn.addEventListener('click', () => openModal(addModal));
    closeAddBtn.addEventListener('click', () => closeModal(addModal));

    // submit add
    addSubmitBtn.addEventListener('click', () => {
      if (!validateAdd()) return;

      const data = {
        name: addFullName.value.trim(),
        empNumber: addEmpNumber.value.trim(),
        contact: addContact.value.trim(),
        email: addEmail.value.trim(),
        role: addRole.value
      };

      const newRow = createRow(data);
      accountsTbody.appendChild(newRow);
      clearAddForm();
      closeModal(addModal);
    });

    // event delegation for edit/delete buttons inside tbody
    accountsTbody.addEventListener('click', (e) => {
      const tr = e.target.closest('tr');
      if (!tr) return;

      if (e.target.classList.contains('edit-btn')) {
        // fill edit form
        editingRow = tr;
        editFullName.value = tr.dataset.fullName || '';
        editEmpNumber.value = tr.dataset.empNumber || '';
        editContact.value = tr.dataset.contact || '';
        editEmail.value = tr.dataset.email || '';
        editRole.value = tr.dataset.role || '';
        openModal(editModal);
      }

      if (e.target.classList.contains('delete-btn')) {
        deletingRow = tr;
        openModal(deleteModal);
      }
    });

    // save edit
    editSaveBtn.addEventListener('click', () => {
      if (!editingRow) return;
      // basic validation
      if (!editFullName.value.trim()) { alert('Please enter full name'); editFullName.focus(); return; }
      if (!editEmail.value.trim()) { alert('Please enter email'); editEmail.focus(); return; }
      if (!editRole.value) { alert('Please select a role'); editRole.focus(); return; }

      // update cells and dataset
      editingRow.dataset.fullName = editFullName.value.trim();
      editingRow.dataset.empNumber = editEmpNumber.value.trim();
      editingRow.dataset.contact = editContact.value.trim();
      editingRow.dataset.email = editEmail.value.trim();
      editingRow.dataset.role = editRole.value;

      editingRow.querySelector('.cell-name').textContent = editingRow.dataset.fullName;
      editingRow.querySelector('.cell-email').textContent = editingRow.dataset.email;
      editingRow.querySelector('.cell-role').textContent = editingRow.dataset.role;

      editingRow = null;
      closeModal(editModal);
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

    // (optional) you can pre-populate the list here if you want; currently starts empty
    // Example:
    // accountsTbody.appendChild(createRow({name:'Sample User', empNumber:'001', contact:'0917', email:'sample@example.com', role:'Employee'}));
  </script>

</body>
</html>