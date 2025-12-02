<?php
require 'db_connect.php';

// GET CURRENT COUNTS
$newMessages = (int)$conn->query("SELECT COUNT(*) AS total FROM messages WHERE status='Pending'")->fetch_assoc()['total'];
$lowStock    = (int)$conn->query("SELECT COUNT(*) AS total FROM inventory WHERE Quantity <= 5")->fetch_assoc()['total'];

// FETCH LAST SAVED COUNTS
$state = $conn->query("SELECT last_message_count, last_lowstock_count FROM notification_state WHERE id=1")->fetch_assoc();

$playSound = false;

// Detect changes
if ($newMessages > $state['last_message_count'] || $lowStock > $state['last_lowstock_count']) {
    $playSound = true;
}

// OUTPUT
echo json_encode([
    "messages" => $newMessages,
    "lowstock" => $lowStock,
    "playSound" => $playSound
]);

if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1");
    echo "ok";
    exit;
}


?>
