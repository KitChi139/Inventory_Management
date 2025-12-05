<?php
require 'db_connect.php';

if (!isset($_POST['requestID'])) {
    exit("INVALID");
}

$requestID = intval($_POST['requestID']);

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

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        UPDATE requests
        SET date_completed = NOW()
        WHERE request_id = ?
    ");
    $stmt->bind_param("i", $requestID);
    $stmt->execute();

    if ($row['status'] === "Approved") {
        $stmt = $conn->prepare("
            UPDATE requests
            SET status = 'Completed'
            WHERE request_id = ?
        ");
        $stmt->bind_param("i", $requestID);
        $stmt->execute();

    }

    $conn->commit();
    echo "OK";

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error completing request $requestID: " . $e->getMessage());
    echo "ERROR";
}
?>
