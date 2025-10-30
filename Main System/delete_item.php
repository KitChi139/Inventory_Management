<?php
include 'db_connect.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    $stmt->close();
}
$conn->close();
?>
