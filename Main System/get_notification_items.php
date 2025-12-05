<?php
require "db_connect.php";

$msg = $conn->query("
    SELECT 'message' AS type, header, supplier, date_created 
    FROM messages
    ORDER BY date_created DESC
    LIMIT 3
");

$low = $conn->query("
    SELECT 'lowstock' AS type, p.ProductName AS product, i.Quantity, i.ExpirationDate 
    FROM inventory i
    JOIN products p ON p.ProductID = i.ProductID
    WHERE i.Quantity <= 5
    ORDER BY i.Quantity ASC
    LIMIT 3
");

$output = "";

while ($m = $msg->fetch_assoc()) {
    $output .= "
    <div class='notif-item' onclick=\"window.location='message_list.php'\">
        <div class='notif-avatar'>MS</div>
        <div class='notif-info'>
            <span class='notif-title'>{$m['header']}</span>
            <span class='notif-time'>" . date("M d, Y", strtotime($m['date_created'])) . "</span>
        </div>
        <span class='notif-badge-msg'>Message</span>
    </div>";
}

while ($l = $low->fetch_assoc()) {
    $output .= "
    <div class='notif-item' onclick=\"window.location='lowstock.php'\">
        <div class='notif-avatar'>LS</div>
        <div class='notif-info'>
            <span class='notif-title'>{$l['product']} is low</span>
            <span class='notif-time'>Qty: {$l['Quantity']}</span>
        </div>
        <span class='notif-badge-low'>Low Stock</span>
    </div>";
}

echo $output ?: "<div style='padding:12px; text-align:center;'>No notifications</div>";
