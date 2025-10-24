<?php
$connection = new mysqli("localhost", "root", "", "inventory_db");
if ($connection->connect_error) {
    die("Connection Failed: " . $connection->connect_error);
}

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if (empty($email) || empty($otp)) {
    echo "❌ Please fill in both email and OTP code.";
    exit;
}

// 1️⃣ Get email ID
$stmt = $connection->prepare("SELECT emailID FROM email WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($emailID);
if (!$stmt->fetch()) {
    echo "❌ Email not found.";
    exit;
}
$stmt->close();

// 2️⃣ Validate OTP
$check = $connection->prepare("SELECT otpID, verifyDate FROM otpverify WHERE emailID = ? AND otpCode = ? AND verification = 0 ORDER BY verifyDate DESC LIMIT 1");
$check->bind_param("is", $emailID, $otp);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->bind_result($otpID, $verifyDate);
    $check->fetch();

    // ✅ Check expiry (5 minutes)
    $otp_time = strtotime($verifyDate);
    if (time() - $otp_time > 300) { // 300 seconds = 5 minutes
        echo "❌ OTP has expired. Please request a new one.";
        exit;
    }

    // ✅ Mark as verified
    $update = $connection->prepare("UPDATE otpverify SET verification = 1 WHERE otpID = ?");
    $update->bind_param("i", $otpID);
    $update->execute();

    echo "✅ OTP verified successfully!";
} else {
    echo "❌ Invalid or incorrect OTP code.";
}
?>
