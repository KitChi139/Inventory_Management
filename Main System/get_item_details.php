<?php
require 'db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "Missing ID"]);
    exit;
}

$id = intval($_GET['id']);

$sql = "
    SELECT 
        i.InventoryID,
        i.Quantity,
        i.ExpirationDate,
        p.ProductName,
        p.Min_stock,
        c.Category_Name,
        u.UnitName,
        s.supplier_name,
        s.contact_person,
        s.phone,
        s.email
    FROM inventory i
    JOIN products p ON p.ProductID = i.ProductID
    LEFT JOIN categories c ON c.CategoryID = p.CategoryID
    LEFT JOIN units u ON u.UnitID = p.UnitID
    LEFT JOIN suppliers s ON s.supplier_id = 1
    WHERE i.InventoryID = $id
";

$res = $conn->query($sql);

if ($res->num_rows == 0) {
    echo json_encode(["error" => "Item not found"]);
    exit;
}

echo json_encode($res->fetch_assoc());
?>
