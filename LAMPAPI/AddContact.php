<?php
// CORS
header("Access-Control-Allow-Origin: http://137.184.185.65");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// Helpers
function sendJson($obj, $status = 200) {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($obj);
  exit;
}

function getJsonBody() {
  $raw = file_get_contents('php://input');
  if ($raw === '' || $raw === false) return [];
  $data = json_decode($raw, true);
  if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(['error' => 'Invalid JSON body'], 400);
  }
  return $data ?: [];
}

// Read input
$in = getJsonBody();
$contact = isset($in['contact']) ? trim($in['contact']) : '';
$userId  = $in['userId'] ?? null;

// Validate
if ($contact === '' || !is_numeric($userId)) {
  sendJson(['error' => 'userId (int) and contact (non-empty string) required'], 400);
}

// DB
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
  sendJson(['error' => 'DB connection failed'], 500);
}

// Insert
$stmt = $conn->prepare("INSERT INTO Contacts (UserID, Name) VALUES (?, ?)");
if (!$stmt) {
  $conn->close();
  sendJson(['error' => 'Prepare failed'], 500);
}
$stmt->bind_param("is", $userId, $contact);

if (!$stmt->execute()) {
  $err = $stmt->error;
  $stmt->close();
  $conn->close();
  sendJson(['error' => "Insert failed: $err"], 500);
}

$newId = $stmt->insert_id;
$stmt->close();
$conn->close();

// Success: return created contact info
sendJson([
  'id' => (int)$newId,
  'userId' => (int)$userId,
  'name' => $contact,
  'error' => ''
], 201);
