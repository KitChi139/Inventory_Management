<?php
require 'db_connect.php';

$result = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name");

$suppliers = [];
while ($row = $result->fetch_assoc()) {
    $row['categories'] = $row['categories'] ? explode(",", $row['categories']) : [];
    $suppliers[] = $row;
}

header("Content-Type: application/json");
echo json_encode($suppliers);
