<?php
// inventory_functions.php
include 'db_connect.php';

function getInventoryStats() {
    global $conn;

    $inventory = $conn->query("SELECT * FROM inventory")->fetch_all(MYSQLI_ASSOC);

    $total_items = count($inventory);
    $low_stock = $out_stock = $in_stock = $total_value = 0;
    $categories = [];
    $expirations = [];

    foreach ($inventory as $item) {
        $qty = (int)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $max_stock = (int)($item['max_stock'] ?? 0);
        $category = $item['category'] ?: 'Uncategorized';
        $expiration = $item['expiration'] ?? null;

        // Status
        $status = 'Available';
        if ($qty == 0) $status = 'Out of Stock';
        elseif ($max_stock > 0 && $qty < $max_stock * 0.2) $status = 'Low Stock';
        elseif ($max_stock > 0 && $qty >= $max_stock) $status = 'Full Stock';

        $inventory_item = $item;
        $inventory_item['status'] = $status;

        // Count stats
        if ($status === 'Low Stock') $low_stock++;
        if ($status === 'Out of Stock') $out_stock++;
        if ($status === 'Available' || $status === 'Full Stock') $in_stock++;

        $total_value += $qty * $price;

        // Categories
        $categories[$category] = ($categories[$category] ?? 0) + $qty;

        // Expirations
        if ($expiration) $expirations[] = ['name'=>$item['name'], 'date'=>$expiration];
    }

    return [
        'inventory' => $inventory,
        'total_items' => $total_items,
        'low_stock' => $low_stock,
        'out_stock' => $out_stock,
        'in_stock' => $in_stock,
        'total_value' => $total_value,
        'categories' => $categories,
        'expirations' => $expirations
    ];
}
