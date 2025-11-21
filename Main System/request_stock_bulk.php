<?php
require 'db_connect.php';
session_start();

$productIds = $_POST['product_id'] ?? [];
$quantities = $_POST['quantity'] ?? [];

if (!is_array($productIds) || !is_array($quantities)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// If you have login system replace with: $_SESSION['username']
$requester = "Admin";

foreach ($productIds as $index => $productId) {

    $qty = (int)$quantities[$index];

    if ($qty <= 0) continue;

    $stmt = $conn->prepare("
        INSERT INTO requests (ProductID, quantity, requester)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $productId, $qty, $requester);
    $stmt->execute();
}

echo json_encode(['success' => true]);
?>
