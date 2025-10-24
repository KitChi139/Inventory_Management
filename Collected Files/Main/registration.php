<?php 
session_start();

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

    $connection -> begin_transaction();

    try {
      // $checkstmt = $connection ->prepare("SELECT empID, empName, empNum FROM employee WHERE empID = ?");
      // $checkstmt

      if ($userType === 'employee') { // Employee

          $role = "staff";

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

          $stmt3 = $connection -> prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)" );
          $stmt3 -> bind_param("sss", $email, $pass, $role);
          $stmt3 -> execute();
          $UID = $connection -> insert_id;
          $stmt3 -> close();

          $stmt4 = $connection -> prepare("INSERT INTO userinfo (userID, empID, emailID, cont_num) VALUES (?, ?, ?, ?)");
          $stmt4 -> bind_param("iiii", $UID, $EMPID, $EID, $contact);
          $stmt4 -> execute();
          $stmt4 -> close();

      } else { // Supplier

          $role = "supplier";
          
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

          $stmt3 = $connection -> prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)" );
          $stmt3 -> bind_param("sss", $email, $pass, $role);
          $stmt3 -> execute();
          $UID = $connection -> insert_id;
          $stmt3 -> close();

          $stmt4 = $connection -> prepare("INSERT INTO userinfo (userID, empID, emailID, cont_num) VALUES (?, ?, ?, ?)");
          $stmt4 -> bind_param("iiii", $UID, $EMPID, $EID, $contact);
          $stmt4 -> execute();
          $stmt4 -> close();

      }
          $connection -> commit();

    } catch (mysqli_sql_exception $e) {
      $_SESSION['error'] = "ERROR! " . $e->getMessage();
      $connection->rollback();
      header("Location: registration.php");
      exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Form</title>
  <link rel="stylesheet" href="registration.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <header class="header">
    <div class="header-content">
      <img src="photo.png">
      <h1>Generic Hospital</h1>
    </div>
  </header>

  <main class="main">
    <div class="title-section">
      <h2>Registration Form</h2>
      <p>Enter your details to Register</p>
    </div>

    <div class="form-box">
      <div class="button-group">
        <button id="employeeBtn" class="active">Employee</button>
        <button id="supplierBtn">Supplier</button>
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

        <div class="submit-container">
          <button type="submit" class="submit-btn">Submit</button>
        </div>
      </form>
    </div>

    <div id="popup" class="popup">
      <div class="popup-content">
        <p>You have successfully registered!</p>
        <button id="okBtn">OK</button>
      </div>
    </div>
  </main>

  <script>
    const form = document.getElementById('registrationForm');
    const employeeBtn = document.getElementById('employeeBtn');
    const supplierBtn = document.getElementById('supplierBtn');
    const popup = document.getElementById('popup');
    const okBtn = document.getElementById('okBtn');
    const label1 = document.getElementById('label1');
    const label2 = document.getElementById('label2');

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      popup.style.display = 'flex';
      form.reset();
    });

    okBtn.addEventListener('click', function() {
      popup.style.display = 'none';
    });

    employeeBtn.addEventListener('click', () => {
      employeeBtn.classList.add('active');
      supplierBtn.classList.remove('active');
      label1.textContent = 'Full Name:';
      label2.textContent = 'Employee Number:';
      document.getElementById('userType').value = 'employee';
    });

    supplierBtn.addEventListener('click', () => {
      supplierBtn.classList.add('active');
      employeeBtn.classList.remove('active');
      label1.textContent = 'Company Name:';
      label2.textContent = 'Contact Person:';
      document.getElementById('userType').value = 'supplier';
    });
  </script>
</body>
</html>
