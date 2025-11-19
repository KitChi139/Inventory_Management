<?php
function sendResponse($status, $message, $data = null) {
  http_response_code($status);
  echo json_encode([
    "success" => $status >= 200 && $status < 300,
    "message" => $message,
    "data" => $data
  ]);
  exit;
}
?>
