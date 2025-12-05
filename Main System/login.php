<?php 
require 'db_connect.php';
$connection = $conn;

$error = "";
$popupMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $connection->prepare(
        "SELECT u.*, ur.roleName, s.SupplierID 
        FROM users AS u
        JOIN userroles AS ur ON u.roleID = ur.roleID 
        JOIN userinfo AS ui on ui.userID = u.userID
        LEFT JOIN suppliers s on s.SupplierID = ui.SupplierID
        WHERE username = ?"
    );
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
                  header("Location: dashboard.php");
                  exit();
                } elseif ($user['roleName'] === 'Supplier') {
                      $_SESSION['SupplierID'] = $user['SupplierID'];
                  $_SESSION['popupMessage'] = "You have successfully logged in to the Supplier Portal!";
                    header("Location: supplier_portal_db.php");
                    exit();
                } elseif ($user['roleName'] === 'Admin') {
                  $_SESSION['popupMessage'] = "You have successfully logged in to the Admin Portal!";
                  header("Location:dashboard.php");
                  
                }
              }
                
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt -> close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediSync Login</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <style>
        :root {
          --primary-color: #043873;
          --primary-color2: #4f9cf9;
          --secondary-color: #ffe492;
          --bg-blue: #f3f7ff;
          --footer-bg: #003087;
          --footer-text: #ffffff;
          --footer-link: #d3d8e0;
          --text-dark: #333;
          --text-gray: #555;
          --text-light: #666;
          --hero-bg: #e6f0fa;
          --services-bg: #ffffff;
          --news-bg: #f8f9fa;
          --about-bg: #e9ecef;
          --contact-bg: #f0f8ff;
          --container-max: 1320px;
          --radius-large: 16px;
        }

        body{
          overflow: hidden;
          margin: 0;
          padding: 0;
      }

      .login-wrapper {
        display: flex;
        min-height: 100vh;
        background-color: #ffffff;
        font-family: "Inter", sans-serif;
      }

      .cross {
        position: absolute;
        top: 31%;
        left: 120px;
        width: 17%;
        height: auto;
        transform: translateY(-5px);
        filter: 
          drop-shadow(10px 10px 8px rgba(33, 63, 105, 0.3))
          drop-shadow(-10px -10px 8px rgba(255, 255, 255, 0.8))
      }

      .line{
        position: absolute;
        top: 39%;
        left: 7%;
        width: 44%;
        height: auto;
      }

      .login-left {
        flex: 1.5;
        background-color: #C5D6EE;
        display: flex;
        align-items: center;
        justify-content: center;



        background-image: 

          radial-gradient(circle at 20% 30%, rgba(255,255,255,0.08) 0%, transparent 60%),
          radial-gradient(circle at 80% 70%, rgba(255,255,255,0.06) 0%, transparent 60%),
          
          url("logo+.svg"),
          url("logo+.svg"),
          url("logo+.svg"),
          url("logo+.svg"),
          url("logo+.svg"),
          url("logo+.svg"),
          url("logo+.svg"),
          url("logo+.svg");

        background-size: 
          120px 120px,
          180px 180px,
          100px 100px,
          150px 150px,
          110px 110px,
          160px 160px,
          100px 100px,
          140px 140px;

        background-position: 
          10% 5%,
          110% -2%,
          45% 80%,
          2% 90%,
          50% 15%,
          80% 104%,
          70% -4%,
          -60px 220px;

        background-repeat: no-repeat;
        background-blend-mode: soft-light;

        
      }

      .login-brand {
        text-align: center;
        width: 65%;
      }

      .login-brand h1 {
        font-size: 4em;
        color: var(--primary-color);
        font-weight: 800;
        margin-top: 3rem;
        margin-bottom: 3rem;
        text-align: right;
        text-shadow: 
          3px 3px 2px rgba(4, 56, 115, 0.25);
      }

      .login-brand .info {
        font-size: 1.8rem;
        color: var(--primary-color);
        font-weight: 600;
        letter-spacing: 0.2rem;
        text-align: right;
        text-shadow: 
          3px 3px 2px rgba(4, 56, 115, 0.25);
      }



      .login-brand .copyright {
        font-size: 1rem;
        color: var(--primary-color);
        text-align: right;
        margin-top: -10px;
      }

      .login-right {
        flex: 1;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .login-box {
        width: 90%;
        max-width: 460px;
        background-color: #fff;
        padding: 50px;
        border-radius: 12px;
      }

      .login-box h2 {
        text-align: center;
        color: #043873;
        font-weight: 700;
        margin-bottom: 1.5rem;
      }

      .login-box form {
        display: flex;
        flex-direction: column;
      }

      .login-box label {
        font-size: 14px;
        color: #333;
        margin-bottom: 4px;
      }

      .show-pass {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 14px;
      }

      .show-pass label {
        color: #333;
        margin-left: 6px;
      }

      .login-box input[type="email"],
      .login-box input[type="password"],
      .login-box input[type="text"] {
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        margin-bottom: 15px;
        transition: all 0.2s ease-in-out;
        width: 100%;
        color: #333;
        background-color: #fff;
        outline: none;
      }

      .login-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 4px rgba(4, 56, 115, 0.2);
      }

      .login-box input {
        height: 40px; 
        line-height: 1.4;
      }

      .login-box button {
        background-color: var(--primary-color);
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .login-box button:hover {
        background-color: var(--primary-color);
      }

      .forgot {
        text-align: center;
        margin-top: 10px;
      }

      .forgot a {
        font-size: 13px;
        color: #043873;
        text-decoration: none;
      }

      .forgot a:hover {
        text-decoration: underline;
      }


      @media (max-width: 1200px) {
        .cross { width: 10%; }
        .line { width: 34%; }
      }

      @media (max-width: 992px) {
        .cross { width: 20%}
        .line { width: 20% }
        .login-brand h1 { font-size: 3.5rem; }
        .login-brand .info { font-size: 1.6rem; }
      }

      @media (max-width: 768px) {
        .login-wrapper {
          flex-direction: column;
        }

        .login-left {
          display: none;
        }

        .login-right {
          flex: unset;
          width: 100%;
        }

        .login-box {
          width: 100%;
          max-width: 90%;
          margin: 2rem auto;
          padding: 2rem;
        }
      }


      .login-box h2 {
        text-align: center;
        color: #043873;
        font-weight: 700;
        margin-bottom: 1.5rem;
      }

      .login-box form {
        display: flex;
        flex-direction: column;
      }

      .login-box label {
        font-size: 14px;
        color: #333;
        margin-bottom: 4px;
      }

      .show-pass {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 14px;
      }

      .show-pass label {
        color: #333;
        margin-left: 6px;
      }

      .login-box input[type="email"],
      .login-box input[type="password"],
      .login-box input[type="text"] {
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        margin-bottom: 15px;
        transition: all 0.2s ease-in-out;
        width: 100%;
        color: #333;
        background-color: #fff;
        outline: none;
      }

      .login-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 4px rgba(4, 56, 115, 0.2);
      }

      .login-box input {
        height: 40px; 
        line-height: 1.4;
      }

      .login-box button {
        background-color: var(--primary-color);
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .login-box button:hover {
        background-color: var(--primary-color);
      }

      .forgot {
        text-align: center;
        margin-top: 10px;
      }

      .forgot a {
        font-size: 13px;
        color: #043873;
        text-decoration: none;
      }

      .forgot a:hover {
        text-decoration: underline;
      }

      .error-message {
        color: #dc3545;
        font-size: 14px;
        margin-top: 10px;
        text-align: left;
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
        z-index: 1000;
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
        font-size: 20px;
        margin-bottom: 15px;
      }

      .popup-content button {
        background-color: #043873;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
      }

      @media (max-width: 1200px) {
        .cross { width: 10%; }
        .line { width: 34%; }
      }

      @media (max-width: 992px) {
        .cross { width: 20%}
        .line { width: 20% }
        .login-brand h1 { font-size: 3.5rem; }
        .login-brand .info { font-size: 1.6rem; }
      }

      @media (max-width: 768px) {
        .login-wrapper {
          flex-direction: column;
        }

        .login-left {
          display: none;
        }

        .login-right {
          flex: unset;
          width: 100%;
        }

        .login-box {
          width: 100%;
          max-width: 90%;
          margin: 2rem auto;
          padding: 2rem;
        }
      }
  </style>
  
</head>
<body>

<div class="login-wrapper">

  <div class="login-left">
    <img class="cross" src="logo+.svg" alt="Logo Cross" />
    <img class="line" src="logo-.svg" alt="Logo Line" />
    <div class="login-brand">
        <h1>Medi<span style="color: var(--primary-color2);">Sync</span></h1>
        <p class="info">Health Medical Center</p>
      <p class="copyright">© 2025 BSIT-3E. All rights reserved.</p>
    </div>
  </div>

  <div class="login-right">
    <div class="login-box">
      <h2>Log In</h2>
      <form method="post" action="login.php" id="loginForm">
        <label for="email">Email</label>
        <input type="text" name="username" id="email" placeholder="Enter your email" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>

        <div class="show-pass">
          <input type="checkbox" id="showPassword">
          <label for="showPassword">Show Password</label>
        </div>

        <button type="submit">LOG IN</button>

        <?php if (!empty($error)): ?>
          <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

      </form>
    </div>
  </div>

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
    const showPasswordCheckbox = document.getElementById("showPassword");
    const passwordInput = document.getElementById("password");

    if (message.trim() !== "") {
      popupMessage.textContent = message;
      popup.style.display = 'flex'; 
    }

    okBtn.addEventListener('click', () => {
      popup.style.display = 'none'; 
    });

    showPasswordCheckbox.addEventListener('change', () => {
      passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
    });
  </script>
  
</body>
</html>
