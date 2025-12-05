<?php
require_once 'db_connect.php';

if (!isset($_POST['prefix']) || !isset($_POST['exp'])) {
    echo '0001';
    exit();
}

$prefix = trim($_POST['prefix']);
$exp = trim($_POST['exp']);

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM requests 
    WHERE BatchNum LIKE CONCAT(?, ?, '%')
");
$stmt->bind_param("ss", $prefix, $exp);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();
$seq = $result['total'] + 1;

echo str_pad($seq, 4, '0', STR_PAD_LEFT);
?>
