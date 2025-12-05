<?php
require 'db_connect.php';
if (!isset($_SESSION)) session_start();

// Get return tab
$return_tab = $_POST['return_tab'] ?? 'category';

// Handle Add Category
if (isset($_POST['action']) && $_POST['action'] == 'add_category') {
    $name = trim($_POST['name']);
    if ($name != '') {
        $stmt = $conn->prepare("INSERT INTO categories (Category_Name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php#$return_tab");
    exit;
}

// Handle Update Category
if (isset($_POST['action']) && $_POST['action'] == 'update_category') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    if ($name != '' && $id > 0) {
        $stmt = $conn->prepare("UPDATE categories SET Category_Name = ? WHERE CategoryID = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php#$return_tab");
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
    header("Location: settings.php#$return_tab");
    exit;
}

// Handle Update Unit
if (isset($_POST['action']) && $_POST['action'] == 'update_unit') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    if ($name != '' && $id > 0) {
        $stmt = $conn->prepare("UPDATE units SET UnitName = ? WHERE UnitID = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php#$return_tab");
    exit;
}

// Handle Delete Category
if (isset($_POST['action']) && $_POST['action'] == 'delete_category') {
    $id = intval($_POST['id']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE CategoryID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php#$return_tab");
    exit;
}

// Handle Delete Unit
if (isset($_POST['action']) && $_POST['action'] == 'delete_unit') {
    $id = intval($_POST['id']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM units WHERE UnitID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: settings.php#$return_tab");
    exit;
}
?>