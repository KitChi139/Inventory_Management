<?php
require 'db_connect.php';

if (isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);

    $sql = "
        SELECT 
            i.InventoryID,
            i.BatchNum,
            i.Quantity,
            i.ExpirationDate,
            i.Status AS InventoryStatus,
            i.SKU,
            u.UnitName AS Unit
        FROM inventory i
        JOIN products p ON i.ProductID = p.ProductID
        LEFT JOIN units u ON u.UnitID = p.UnitID
        WHERE i.ProductID = ?
        ORDER BY i.ExpirationDate ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='batches-table'>
                <thead>
                  <tr>
                    <th>Batch #</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Quantity per Unit</th>
                    <th>Expiration Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>";

        while ($row = $result->fetch_assoc()) {
            $unit = htmlspecialchars($row['Unit'] ?? '-');
            $quantity = (int)$row['Quantity'];

            $quantityPerUnit = $quantity;
            
            $expirationDate = $row['ExpirationDate'];
            if ($expirationDate && $expirationDate !== '0000-00-00' && $expirationDate !== null) {
                $expirationDate = date('Y-m-d', strtotime($expirationDate));
            } else {
                $expirationDate = '-';
            }
            
            echo "<tr>
                    <td>" . htmlspecialchars($row['BatchNum'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($row['SKU'] ?? '-') . "</td>
                    <td>{$quantity}</td>
                    <td>{$unit}</td>
                    <td>{$quantityPerUnit}</td>
                    <td>{$expirationDate}</td>
                    <td>" . htmlspecialchars($row['InventoryStatus'] ?? '-') . "</td>
                  </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p>No batches found for this product.</p>";
    }
}
?>
