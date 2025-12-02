<?php
// notifications.php
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Get counts for "new" items
// You can tune the logic of what counts as "new". Here we count:
// - Messages with status = 'Pending' (as new messages)
// - Inventory items with Quantity <= 5 (as low stock)
$msgCountRes = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE status = 'Pending'");
$msgCount = (int)($msgCountRes->fetch_assoc()['cnt'] ?? 0);

$lowRes = $conn->query("SELECT COUNT(*) AS cnt FROM inventory WHERE Quantity <= 5");
$lowCount = (int)($lowRes->fetch_assoc()['cnt'] ?? 0);

// Build a combined "recent alerts" list (limit 3) mixing messages and lowstock
// Use UNION with a `type` column so frontend can colorize
$alerts = [];
$sql = "
  (SELECT 
     message_id AS id,
     header AS title,
     preview AS body,
     supplier AS meta,
     date_created AS date_at,
     'message' AS type
   FROM messages
   WHERE 1
   ORDER BY date_created DESC
   LIMIT 3)
  UNION
  (SELECT
     InventoryID AS id,
     CONCAT(ProductName, ' — low stock') AS title,
     CONCAT('Qty: ', Quantity, ' | Batch: ', IFNULL(BatchNum, 'N/A')) AS body,
     '' AS meta,
     DateUpdated AS date_at,
     'lowstock' AS type
   FROM inventory i
   JOIN products p ON p.ProductID = i.ProductID
   WHERE i.Quantity <= 5
   ORDER BY DateUpdated DESC
   LIMIT 3)
  ORDER BY date_at DESC
  LIMIT 3
";

$res = $conn->query($sql);
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $alerts[] = [
      'id' => $r['id'],
      'title' => $r['title'],
      'body' => $r['body'],
      'meta' => $r['meta'],
      'date_at' => $r['date_at'],
      'type' => $r['type']
    ];
  }
}

// Last notification timestamp (for the frontend to compare)
$lastRow = $conn->query("SELECT GREATEST(
    IFNULL((SELECT MAX(date_created) FROM messages), '1970-01-01 00:00:00'),
    IFNULL((SELECT MAX(DateUpdated) FROM inventory), '1970-01-01 00:00:00')
  ) AS lasttime")->fetch_assoc();
$lastTime = $lastRow['lasttime'] ?? date('Y-m-d H:i:s');

echo json_encode([
  'status' => 'ok',
  'counts' => [
    'messages' => $msgCount,
    'lowstock' => $lowCount,
    'total' => $msgCount + $lowCount
  ],
  'alerts' => $alerts,
  'last_time' => $lastTime
], JSON_UNESCAPED_UNICODE);
