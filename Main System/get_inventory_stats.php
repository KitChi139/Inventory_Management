<?php
// get_inventory_stats.php
header('Content-Type: application/json');
require 'db_connect.php';

try {
  // Total items = total rows in inventory joined for visibility (distinct products optional)
  $res = $conn->query("SELECT COUNT(*) AS cnt FROM inventory");
  $total_items = (int)($res->fetch_assoc()['cnt'] ?? 0);

  // Low stock (rule: quantity < 5 and > 0)
  $res = $conn->query("SELECT COUNT(*) AS cnt FROM inventory WHERE Quantity > 0 AND Quantity < 5");
  $low_stock = (int)($res->fetch_assoc()['cnt'] ?? 0);

  // Pending requests
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
