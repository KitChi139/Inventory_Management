<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once "../Configs/database.php";
require_once "../Models/inventory.php";
require_once "../Helpers/response.php";

$database = new Database();
$db = $database->connect();
$inventory = new Inventory($db);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  sendResponse(405, "Only PUT method is allowed");
}

// Read request body
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['InventoryID']) || empty($data['Quantity'])) {
  sendResponse(400, "InventoryID and Quantity (sold) are required");
}

$inventory->InventoryID = $data['InventoryID'];
$inventory->Quantity = $data['Quantity'];

if ($inventory->reduceStock()) {
  sendResponse(200, "Stock reduced by {$data['Quantity']}");
} else {
  sendResponse(400, "Not enough stock or invalid InventoryID");
}
?>
