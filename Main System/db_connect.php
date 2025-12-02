<?php
// db_connect.php
// Include session configuration first
require_once 'session_config.php';

// Adjust credentials to your environment
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'inventory_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function add_notification($conn, $type, $title, $details = "", $link = "") {
    $stmt = $conn->prepare("
        INSERT INTO notifications (type, title, details, link)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $type, $title, $details, $link);
    $stmt->execute();
}
