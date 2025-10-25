<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$connection = new mysqli("localhost", "root", "", "inventory_db");
if ($connection->connect_error) {
    die("Connection Failed: " . $connection->connect_error);
}

$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    echo "❌ Please enter an email address.";
    exit;
}

// 1️⃣ Check if email exists
$stmt = $connection->prepare("SELECT emailID FROM email WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo "❌ Email cannot be found in our database. Please use a working Email account.";
    exit;
}
$stmt->bind_result($emailID);
$stmt->fetch();
$stmt->close();

// 2️⃣ Get user ID
$query = $connection->prepare("SELECT userID FROM userinfo WHERE emailID = ?");
$query->bind_param("i", $emailID);
$query->execute();
$query->bind_result($userID);
$query->fetch();
$query->close();

if (empty($userID)) {
    echo "❌ No user linked with this email.";
    exit;
}

// 3️⃣ Generate OTP
$otp = str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);

// 4️⃣ Insert OTP
$ins = $connection->prepare("INSERT INTO otpverify (userID, emailID, otpCode, verification, verifyDate) VALUES (?, ?, ?, 0, NOW())");
$ins->bind_param("iis", $userID, $emailID, $otp);
$ins->execute();
$ins->close();

// 5️⃣ Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'inventorysystem63@gmail.com'; // your Gmail
    $mail->Password = 'chug fpin kyya ztie';             // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('inventorysystem63@gmail.com', 'Inventory Management System');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your One-Time Password (OTP)';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif;'>
            <h2>Your One-Time Password</h2>
            <p>Use this code to verify your account:</p>
            <div style='font-size: 24px; font-weight: bold; color: #007bff;'>$otp</div>
        </div>";

    $mail->send();
    echo "✅ Please check your email for the reset code.";
} catch (Exception $e) {
    echo "❌ Email sending failed: " . $mail->ErrorInfo;
}
?>
