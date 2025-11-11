<?php
// quick_request.php
header('Content-Type: application/json');
require 'db_connect.php';
session_start();

$itemsJson = $_POST['items'] ?? '[]';
$items = json_decode($itemsJson, true);
if (!is_array($items) || empty($items)) {
  echo json_encode(['success'=>false, 'message'=>'No items provided.']);
  exit;
}

$requester = $_SESSION['username'] ?? 'Anonymous';

try {
  $conn->begin_transaction();

  $stmt = $conn->prepare("INSERT INTO requests (ProductID, quantity, requester, status) VALUES (?, ?, ?, 'Pending')");

  foreach ($items as $it) {
    $productId = (int)($it['productId'] ?? 0);
    $qty       = (int)($it['quantity'] ?? 0);
    if ($productId <= 0 || $qty <= 0) continue;

    $stmt->bind_param('iis', $productId, $qty, $requester);
    $stmt->execute();
  }
  $stmt->close();

  $conn->commit();

  echo json_encode(['success'=>true]);

} catch (Throwable $e) {
  if ($conn->errno) $conn->rollback();
  echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
