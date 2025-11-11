<?php
// db_connect.php
// Adjust credentials to your environment
$host = '127.0.0.1';
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
