<?php
require_once 'db_connect.php';

$batchID = (int)$_POST['batchID'];

$sql = "SELECT
        r.request_id,
        p.ProductName,
        c.Category_Name,
        r.quantity,
        r.status,
        r.date_approved,
        r.date_declined,
        r.date_completed,
        com.companyName
    FROM requests r
    JOIN products p ON r.ProductID = p.ProductID
    LEFT JOIN categories c ON c.CategoryID = p.CategoryID
    LEFT JOIN suppliers s ON s.SupplierID = r.SupplierID
    LEFT JOIN company com ON com.comID = s.comID
    WHERE r.BatchID = $batchID
    ORDER BY p.ProductName ASC";

$result = $conn->query($sql);

$incompleteCount = 0;

ob_start();

if ($result->num_rows == 0) {
    echo "<tr><td colspan='9'>No items found.</td></tr>";
} else {
    while($row = $result->fetch_assoc()):
        if ($row["status"] !== "Completed" && !($row["status"] === "Rejected" && $row["date_declined"])) {
            $incompleteCount++;
        }
?>
<tr>
    <td><?= htmlspecialchars($row['ProductName']) ?></td>
    <td><?= htmlspecialchars($row['Category_Name'] ?? '-') ?></td>
    <td><?= (int)$row['quantity'] ?></td>
    <td><?= htmlspecialchars($row['status']) ?></td>
    <td><?= htmlspecialchars($row['companyName'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($row['date_approved'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['date_declined'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['date_completed'] ?? '-') ?></td>

    <td>
        <?php
        if (($row["status"] === "Approved") || ($row["status"] === "Rejected" && !$row["date_declined"])): ?>
            <button class="item-complete-btn" 
                    data-requestid="<?= $row['request_id'] ?>">
                Complete Item
            </button>
        <?php elseif ($row["status"] === "Completed"): ?>
            ✔ Completed
        <?php elseif($row["status"] === "Rejected"): ?>
            ✖ Rejected
        <?php else: ?>
            Pending
        <?php endif; ?>

    </td>
</tr>
<?php
    endwhile;
}

$html = ob_get_clean();

$batchStatusRow = $conn->query("SELECT status FROM batches WHERE BatchID = $batchID")->fetch_assoc();
$batchStatus = $batchStatusRow['status'] ?? 'Unknown';

echo json_encode([
    "html" => $html,
    "incomplete" => $incompleteCount,
    "batch_status" => $batchStatus
]);
?>
