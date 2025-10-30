<?php
include 'db_connect.php';

$id = $_POST['id'];
$item_name = $_POST['item_name'];
$category = $_POST['category'];
$quantity = $_POST['current_stock'];
$max_stock = $_POST['max_stock'];
$price = $_POST['price'];
$expiration = !empty($_POST['expiration']) ? $_POST['expiration'] : NULL;

// Update item
$sql = "UPDATE inventory SET name=?, category=?, quantity=?, max_stock=?, price=?, expiration=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiddsi", $item_name, $category, $quantity, $max_stock, $price, $expiration, $id);

if($stmt->execute()){
    // Determine status
    if ($quantity == 0) {
        $status = "Out of Stock"; $status_class = "status-out";
    } elseif ($max_stock > 0 && $quantity < $max_stock * 0.2) {
        $status = "Low Stock"; $status_class = "status-low";
    } elseif ($quantity >= $max_stock) {
        $status = "Full Stock"; $status_class = "status-full";
    } else {
        $status = "Available"; $status_class = "status-ok";
    }

    echo json_encode([
        'success' => true,
        'message' => 'Item updated successfully.',
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
    echo json_encode(['success' => false, 'message' => 'Error updating item.']);
}
?>
