<?php

header('Content-Type: application/json');
require 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$itemsJson = $_POST['items'] ?? '[]';
$items = json_decode($itemsJson, true);
if (!is_array($items) || empty($items)) {
  echo json_encode(['success'=>false, 'message'=>'No items provided.']);
  exit;
}

$requester = $_SESSION['username'] ?? 'Anonymous';

try {
  $conn->begin_transaction();

    $createdAt = date("Y-m-d H:i:s");
    $stmtBatch = $conn->prepare("INSERT INTO batches (request_date, status) VALUES (?, 'Pending')");
    $stmtBatch->bind_param('s', $createdAt);
    $stmtBatch->execute();
    $batchId = $stmtBatch->insert_id;
    $stmtBatch->close();


  $stmt = $conn->prepare("INSERT INTO requests (ProductID, BatchID, quantity, SupplierID, requester, status) VALUES (?, ?, ?, ?, ?, 'Pending')");

  foreach ($items as $it) {
    $productId = (int)($it['productId'] ?? 0);
    $qty       = (int)($it['quantity'] ?? 0);
    $SupplierID = (int)($it['supplier'] ?? 0);
    if ($productId <= 0 || $qty <= 0) continue;

    $stmt->bind_param('iiiis', $productId, $batchId, $qty, $SupplierID, $requester);
    $stmt->execute();
  }
  $stmt->close();

  $conn->commit();

  echo json_encode(['success'=>true]);

} catch (Throwable $e) {
  $conn->rollback();  
  echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
