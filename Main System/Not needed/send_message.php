<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $subject  = $conn->real_escape_string($_POST['subject']);
    $message  = $conn->real_escape_string($_POST['message']);
    $supplier = $conn->real_escape_string($_POST['supplier']);
    $batch    = $conn->real_escape_string($_POST['batch']);

    // Generate preview text (limit to 50 chars)
    $preview = substr($message, 0, 50) . (strlen($message) > 50 ? "..." : "");

    // Insert into messages table
    $sql = "
        INSERT INTO messages (header, preview, supplier, batch, message, status, date_created)
        VALUES ('$subject', '$preview', '$supplier', '$batch', '$message', 'Pending', NOW())
    ";

    if ($conn->query($sql)) {

        // Optional: insert notification
        $notif_text = "New message sent to supplier: $supplier (Batch $batch)";

        $conn->query("
            INSERT INTO notifications (type, message, is_read, created_at)
            VALUES ('message', '$notif_text', 0, NOW())
        ");

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "error" => $conn->error]);
    }
}
?>
