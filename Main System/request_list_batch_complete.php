<?php
require_once 'db_connect.php';

if (!isset($_POST['batchID'])) {
    echo "ERROR: No batch ID received";
    exit;
}

$batchID = intval($_POST['batchID']);

$check = $conn->prepare("
    SELECT COUNT(*) 
    FROM requests 
    WHERE BatchID = ? AND status = 'Approved' AND date_completed IS NULL
");
$check->bind_param("i", $batchID);
$check->execute();
$check->bind_result($notCompleted);
$check->fetch();
$check->close();

if ($notCompleted > 0) {
    echo "INCOMPLETE";
    exit;
}

$conn->begin_transaction();

try {

    $conn->query("
        UPDATE batches
        SET complete_date = NOW(),
        status = 'Completed'
        WHERE BatchID = $batchID
    ");

    $approvedItems = $conn->query("
        SELECT r.ProductID, r.quantity, r.BatchNum, r.ExpirationDate, p.Min_stock, p.Max_stock
        FROM requests r
        JOIN products p ON r.ProductID = p.ProductID
        WHERE r.BatchID = $batchID AND r.status = 'Completed'
    ");

    $insertStmt = $conn->prepare("
        INSERT INTO inventory (ProductID, Quantity, BatchNum, ExpirationDate, Status)
        VALUES (?, ?, ?, ?, ?)
    ");

    while ($item = $approvedItems->fetch_assoc()) {

        $status = ($item['quantity'] > $item['Max_stock']) ? 'Overstock' : 'In Stock';

        $checkExist = $conn->prepare("
            SELECT COUNT(*) FROM inventory 
            WHERE ProductID = ? AND BatchNum = ?
        ");
        $checkExist->bind_param("is", $item['ProductID'], $item['BatchNum']);
        $checkExist->execute();
        $checkExist->bind_result($exists);
        $checkExist->fetch();
        $checkExist->close();

        if ($exists == 0) {
            $insertStmt->bind_param(
                "iisss",
                $item['ProductID'],
                $item['quantity'],
                $item['BatchNum'],
                $item['ExpirationDate'],
                $status
            );
            $insertStmt->execute();
        }
    }

    $conn->commit();
    echo "OK";

} catch (Exception $e) {
    $conn->rollback();
    error_log("Batch completion error: " . $e->getMessage());
    echo "ERROR";
}
