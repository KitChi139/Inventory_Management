<?php
include 'db_connect.php';

// Get values from POST
$item_name = $_POST['item_name'] ?? '';
$category = $_POST['category'] ?? '';
$quantity = $_POST['current_stock'] ?? 0;
$max_stock = $_POST['max_stock'] ?? 0;
$price = $_POST['price'] ?? 0;
$expiration = !empty($_POST['expiration']) ? $_POST['expiration'] : NULL;

// Validate
if (empty($item_name) || empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit;
}

// Insert new record
$stmt = $conn->prepare("INSERT INTO inventory (name, category, quantity, max_stock, price, expiration) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssidds", $item_name, $category, $quantity, $max_stock, $price, $expiration);

if ($stmt->execute()) {
    $id = $stmt->insert_id;

    // Determine stock status
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

    echo json_encode([
        'success' => true,
        'message' => 'Item added successfully',
        'item' => [
            'id' => $id,
            'name' => $item_name,
            'category' => $category,
            'quantity' => $quantity,
            'max_stock' => $max_stock,
            'price' => $price,
            'expiration' => $expiration,
            'status' => $status,
            'status_class' => $status_class
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add item']);
}
$stmt->close();
$conn->close();
exit;
?>
