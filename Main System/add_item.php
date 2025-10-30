<?php
include 'db_connect.php';

// Collect POST data
$name = $_POST['item_name'];
$category = $_POST['category'];
$quantity = (int)$_POST['current_stock'];
$max_stock = (int)$_POST['max_stock'];
$price = (float)$_POST['price'];
$expiration = $_POST['expiration'] ?? null;

// Insert new item
$stmt = $conn->prepare("INSERT INTO inventory (name, category, quantity, max_stock, price, expiration) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssidds", $name, $category, $quantity, $max_stock, $price, $expiration);
$success = $stmt->execute();

if ($success) {
    $id = $conn->insert_id;

    // Determine status
    if ($quantity == 0) {
        $status = "Out of Stock";
        $status_class = "status-out";
    } elseif ($max_stock > 0 && $quantity < $max_stock * 0.2) {
        $status = "Low Stock";
        $status_class = "status-low";
    } elseif ($quantity >= $max_stock) {
        $status = "Full Stock";
        $status_class = "status-full";
    } else {
        $status = "Available";
        $status_class = "status-ok";
    }

    $item = [
        'id' => $id,
        'name' => $name,
        'category' => $category,
        'quantity' => $quantity,
        'max_stock' => $max_stock,
        'price' => $price,
        'expiration' => $expiration,
        'status' => $status,
        'status_class' => $status_class
    ];

    echo json_encode(['success'=>true, 'message'=>'Item added successfully!', 'item'=>$item]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Failed to add item.']);
}
