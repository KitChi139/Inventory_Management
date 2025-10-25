<?php 
session_start();

$connection = new mysqli("localhost", "root", "", "inventory_db");
if ($connection->connect_error) {
    die("Connection Failed: " . $connection->connect_error);
}

$error = "";
$popupMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $connection -> prepare("SELECT u.*, ur.roleName 
                                    FROM users AS U 
                                    JOIN userroles AS ur on u.roleID = ur.roleID 
                                    WHERE username = ? ");
    $stmt -> bind_param("s", $username);
    $stmt -> execute();

    $result = $stmt->get_result();
    if ($result -> num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['roleName'] = $user['roleName'];
            if (!isset($_SESSION['popupMessage'])) {
              if ($user['roleName'] === 'Employee') {
                  $_SESSION['popupMessage'] = "You have successfully logged in to the Inventory Management!";
                } elseif ($user['roleName'] === 'Supplier') {
                  $_SESSION['popupMessage'] = "You have successfully logged in to the Supplier Portal!";
                }
              }
                
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt -> close();
    $connection -> close();
}
if (isset($_SESSION['popupMessage'])) {
    $popupMessage = $_SESSION['popupMessage'];
    unset($_SESSION['popupMessage']); 
    session_write_close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>

  <!-- Font Awesome for icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />

  <style>
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #d9d9d9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .header {
      background-color: #4f9cf9;
      padding: 15px 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      position: fixed;  
      top: 0;           
      left: 0;
      width: 100%;     
      z-index: 1000;    
    }

    .header-content {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
    }

    .header h1 {
      color: black;
      font-size: 28px;
      font-weight: 600;
      text-align: center;
      flex: 1;
    }

    .container {
      text-align: center;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      width: 400px;
      padding: 40px 35px;
    }

    h2 {
      font-weight: 600;
      color: #001f3f;
      margin-bottom: 6px;
    }

    .subtitle {
      font-size: 14px;
      color: #555;
      margin-bottom: 30px;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
      text-align: left;
    }

    .input-group label {
      display: block;
      font-size: 14px;
      color: #000;
      margin-bottom: 5px;
    }

    .input-group input {
      width: 100%;
      border: none;
      border-bottom: 1px solid #000;
      padding: 8px 35px 8px 5px;
      outline: none;
      background: transparent;
      font-size: 14px;
    }

    .input-group input:focus {
      border-bottom: 1px solid #003366;
    }

    /* FIXED ICON ALIGNMENT */
    .input-group .icon {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-35%);
      color: #001f3f;
      font-size: 16px;
    }

    .btn {
      width: 100%;
      background-color: #003366;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 10px 0;
      font-size: 15px;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.3s;
    }

    .btn:hover {
      background-color: #002b5b;
    }

    @media (max-width: 480px) {
      .container {
        width: 90%;
        padding: 30px 20px;
      }
    }

    .popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
}

.popup-content {
  background-color: #f3f6fa;
  padding: 30px 50px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.popup-content p {
  color: #043873;
  font-size: 16px;
  margin-bottom: 15px;
}

  </style>
</head>

<body>
    <header class="header">
    <div class="header-content">
      <img src="photo.png">
      <h1>Generic Hospital</h1>
    </div>
  </header>
  <div class="container">
    <h2>Sign in to your Account</h2>
    <p class="subtitle">Enter your details to access the portal</p>

    <form method="post" action="login.php">
      <div class="input-group">
        <label>Username / Email Address</label>
        <input type="text" name="username"/>
        <i class="fa-solid fa-user icon"></i>
      </div>

      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password"/>
        <i class="fa-solid fa-lock icon"></i>
      </div>
      <div class="forgot-link">
        <a href="otp/otp.php">Forgot Password?</a>
      </div>
      <div class="forgot-link">
        <a href="registration.php">Don't have an account?</a>
      </div>
      <button type="submit" class="btn">Login</button>
    </form>
    <?php if (!empty($error)): ?>
      <p style="color:red; margin-top:15px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
  </div>
 <div id="popup" class="popup">
    <div class="popup-content">
      <p id="popupMessage"></p>
      <button id="okBtn">OK</button>
    </div>
  </div>

  <script>
    const popup = document.getElementById('popup');
    const popupMessage = document.getElementById('popupMessage');
    const okBtn = document.getElementById('okBtn');
    const message = "<?= $popupMessage ?>";

    if (message.trim() !== "") {
      popupMessage.textContent = message;
      popup.style.display = 'flex'; // show the popup
    }

    okBtn.addEventListener('click', () => {
      popup.style.display = 'none'; // close when OK is clicked
    });
  </script>
  
</body>
</html>