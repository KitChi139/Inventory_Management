<?php
require 'db_connect.php';

// Get latest counts
$newMessages = (int)$conn->query("SELECT COUNT(*) AS total FROM messages WHERE status='Pending'")->fetch_assoc()['total'];
$lowStock    = (int)$conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];

$conn->query("
    UPDATE notification_state 
    SET last_message_count=$newMessages, last_lowstock_count=$lowStock
    WHERE id=1
");

echo "OK";
