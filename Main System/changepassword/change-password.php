<?php
session_start();

// Block direct access if OTP not verified
if (empty($_SESSION['otp_verified']) || empty($_SESSION['otp_email'])) {
    header("Location: ../otp/otp.php");
    exit;
}

$message = '';
$email = $_SESSION['otp_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword === '' || $confirmPassword === '') {
        $message = '<span style="color:red;">Please fill in all fields.</span>';
    } elseif ($newPassword !== $confirmPassword) {
        $message = '<span style="color:red;">Passwords do not match.</span>';
    } else {
        $conn = new mysqli("localhost", "root", "", "inventory_db");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // $hashed = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            UPDATE users u
            JOIN userinfo ui ON u.userID = ui.userID
            JOIN email e ON ui.emailID = e.emailID
            SET u.password = ?
            WHERE e.email = ?
        ");
        $stmt->bind_param("ss", $newPassword, $email);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = '<span style="color:green;">Password successfully changed! You can now log in.</span>';
            session_destroy(); // clear OTP session after success
            header("Location: ../Login.php");
        } else {
            $message = '<span style="color:red;">Error: Unable to update password.</span>';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password - Generic Hospital</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="change-password.css">
</head>
<body>
  <header>
    <div class="logo">
      <img src="your-logo.png" alt="Hospital Logo">
    </div>
    <h1>Generic Hospital</h1>
  </header>

  <main>
    <div class="container">
      <h2>Change Password</h2>

      <form method="POST" id="passwordForm">
        <label for="new-password">New Password:</label>
        <input type="password" id="new-password" name="new_password" placeholder="Enter new password" required>

        <label for="confirm-password">Confirm New Password:</label>
        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm new password" required>

        <button type="submit" class="btn">Submit</button>
      </form>

      <?php if (!empty($message)): ?>
        <p id="message" style="margin-top:15px;"><?= $message ?></p>
      <?php endif; ?>

      <div class="back">
        <a href="../login.php">Back to Login</a>
      </div>
    </div>
  </main>
</body>
</html>
