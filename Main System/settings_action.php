<?php
require 'db_connect.php';

if (!isset($_SESSION)) session_start();

// Handle Add Category
if (isset($_POST['action']) && $_POST['action'] == 'add_category') {
    $name = trim($_POST['name']);
    if ($name != '') {
        $stmt = $conn->prepare("INSERT INTO categories (Category_Name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php");
    exit;
}

// Handle Add Unit
if (isset($_POST['action']) && $_POST['action'] == 'add_unit') {
    $name = trim($_POST['name']);
    if ($name != '') {
        $stmt = $conn->prepare("INSERT INTO units (UnitName) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_category') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM categories WHERE CategoryID = '$id'");
        header("Location: settings.php");
        exit();
    }

    if ($action === 'delete_unit') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM units WHERE UnitID = '$id'");
        header("Location: settings.php");
        exit();
    }
}

// Similarly, you can add update_category / update_unit / delete_category / delete_unit
?>
