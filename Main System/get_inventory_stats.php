<?php

header('Content-Type: application/json');
require 'db_connect.php';

try {

  $res = $conn->query("SELECT COUNT(*) AS cnt FROM inventory");
  $total_items = (int)($res->fetch_assoc()['cnt'] ?? 0);

  $res = $conn->query("SELECT COUNT(*) AS cnt FROM inventory WHERE Quantity > 0 AND Quantity < 5");
  $low_stock = (int)($res->fetch_assoc()['cnt'] ?? 0);

  $res = $conn->query("SELECT COUNT(*) AS cnt FROM requests WHERE status = 'Pending'");
  $pending_requests = (int)($res->fetch_assoc()['cnt'] ?? 0);

  echo json_encode([
    'success'=>true,
    'total_items'=>$total_items,
    'low_stock'=>$low_stock,
    'pending_requests'=>$pending_requests
  ]);

} catch (Throwable $e) {
  echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
