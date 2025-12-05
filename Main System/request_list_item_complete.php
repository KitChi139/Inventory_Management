<?php
require 'db_connect.php';

if (!isset($_POST['requestID'])) {
    exit("INVALID");
}

$requestID = intval($_POST['requestID']);

// Get current status
$stmt = $conn->prepare("SELECT status FROM requests WHERE request_id = ?");
$stmt->bind_param("i", $requestID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit("NOT_FOUND");
}

$row = $result->fetch_assoc();

if ($row['status'] === "Pending") {
    exit("NOT_APPROVED");
}

if ($row['status'] === "Completed") {
    exit("ALREADY_DONE");
}

// Begin transaction for safety
$conn->begin_transaction();

try {
    // ✅ Set date_completed for all statuses
    $stmt = $conn->prepare("
        UPDATE requests
        SET date_completed = NOW()
        WHERE request_id = ?
    ");
    $stmt->bind_param("i", $requestID);
    $stmt->execute();

    // ✅ Only approved items are marked Completed
    if ($row['status'] === "Approved") {
        $stmt = $conn->prepare("
            UPDATE requests
            SET status = 'Completed'
            WHERE request_id = ?
        ");
        $stmt->bind_param("i", $requestID);
        $stmt->execute();

        // Optional: insert into inventory here if needed
        // INSERT INTO inventory (ProductID, Quantity, BatchNum, ExpirationDate) ...
    }

    $conn->commit();
    echo "OK";

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error completing request $requestID: " . $e->getMessage());
    echo "ERROR";
}
?>
