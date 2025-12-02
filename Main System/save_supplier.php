<?php
require 'db_connect.php';

$id = $_POST['supplier_id'] ?? "";
$name = $_POST['name'];
$contact = $_POST['contact'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$categories = $_POST['categories'];
$status = $_POST['status'] ?? "Active";
$rating = $_POST['rating'] ?? 0;
$items = $_POST['items'] ?? 0;

if ($id == "") {
    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO suppliers (supplier_name, contact_person, phone, email, categories, rating, items, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssdss", $name, $contact, $phone, $email, $categories, $rating, $items, $status);
} else {
    // UPDATE
    $stmt = $conn->prepare("
        UPDATE suppliers 
        SET supplier_name=?, contact_person=?, phone=?, email=?, categories=?, rating=?, items=?, status=?
        WHERE supplier_id=?
    ");
    $stmt->bind_param("sssssdssi", $name, $contact, $phone, $email, $categories, $rating, $items, $status, $id);
}

echo json_encode(["success" => $stmt->execute()]);
