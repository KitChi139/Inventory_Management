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
            i.SKU
        FROM inventory i
        JOIN products p ON i.ProductID = p.ProductID
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
                    <th>Expiration Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['BatchNum']}</td>
                    <td>{$row['SKU']}</td>
                    <td>{$row['Quantity']}</td>
                    <td>{$row['ExpirationDate']}</td>
                    <td>{$row['InventoryStatus']}</td>
                  </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p>No batches found for this product.</p>";
    }
}
?>
