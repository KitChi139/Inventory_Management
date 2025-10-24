<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$message = '';

// Handle Send Code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If "Send Code" button clicked
    if (isset($_POST['send_code'])) {
        $_SESSION['otp_email'] = $_POST['email']; // store the email
        ob_start();
        include 'send_otp.php'; // send email logic
        $message = ob_get_clean();
        $_SESSION['otp_sent'] = true;
    }

    // If "Verify Code" button clicked
    if (isset($_POST['verify_code'])) {
        $_POST['email'] = $_SESSION['otp_email'] ?? $_POST['email']; // reuse session email
        ob_start();
        include 'verify_otp.php'; // verify logic
        $verifyResult = ob_get_clean();

        if (stripos($verifyResult, 'success') !== false) {
            // OTP verified successfully
            $_SESSION['otp_verified'] = true;
            header("Location: ../changepassword/change-password.php");
            exit;
        } else {
            // verification failed
            $message = $verifyResult;
        }
    }

    // Optional reset session if user wants to change email
    if (isset($_POST['reset_session'])) {
        session_destroy();
        header("Location: otp.php");
        exit;
    }
}

// Keep current state
$storedEmail = $_SESSION['otp_email'] ?? '';
$isSent = isset($_SESSION['otp_sent']) && $_SESSION['otp_sent'] === true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OTP Verification</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="otp-container">
    <div class="otp-card">
      <h2>OTP Verification</h2>
      <p class="subtext">Enter your email to receive a One-Time Password.</p>

      <?php if (!empty($message)): ?>
        <div class="message-box" style="margin-bottom:15px; color:#043873; font-weight:500;">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="email">Email Confirmation</label>
          <div class="email-row">
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($storedEmail) ?>"
                   <?= $isSent ? 'readonly' : '' ?>
                   placeholder="Enter your email address" required>
            <button type="submit" name="send_code" class="send-btn">
              <?= $isSent ? 'Resend Code' : 'Send Code' ?>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="otp">OTP Code</label>
          <input type="text" id="otp" name="otp" maxlength="10" placeholder="Enter OTP code">
        </div>

        <button type="submit" name="verify_code" class="verify-btn">Submit</button>
      </form>

      <?php if ($isSent): ?>
        <form method="POST" style="margin-top:10px;">
          <button type="submit" name="reset_session" class="verify-btn"
                  style="background-color:#4f9cf9;">Use Different Email</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
