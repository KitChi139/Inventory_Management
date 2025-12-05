<?php
require 'db_connect.php';

$list = [];

$msg = $conn->query("
    SELECT 
        message_id AS id,
        'message' AS type,
        header AS title,
        supplier AS source,
        date_created AS created_at
    FROM messages
    ORDER BY date_created DESC
    LIMIT 3
");

while ($m = $msg->fetch_assoc()) {
    $list[] = [
        "type" => "message",
        "title" => $m['title'],
        "source" => $m['source'],
        "created_at" => $m['created_at'],
        "link" => "message_list.php"
    ];
}

$low = $conn->query("
    SELECT
        ProductName AS title,
        Quantity AS qty
    FROM inventory
    WHERE Quantity <= 5
    ORDER BY Quantity ASC
    LIMIT 3
");

while ($l = $low->fetch_assoc()) {
    $list[] = [
        "type" => "lowstock",
        "title" => $l['title'] . " (" . $l['qty'] . " left)",
        "source" => "System",
        "created_at" => date("Y-m-d H:i:s"),
        "link" => "lowstock.php"
    ];
}

usort($list, function($a, $b){
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});


$list = array_slice($list, 0, 3);

echo json_encode($list);
?>
