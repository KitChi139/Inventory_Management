<?php
require 'db_connect.php';

$id = $_POST['supplier_id'];

$stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id=?");
$stmt->bind_param("i", $id);

echo json_encode(["success" => $stmt->execute()]);
