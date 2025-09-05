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
    sendJson(['results'=>[], 'error'=>'Invalid JSON body'], 400);
  }
  return $data ?: [];
}

// Read input
$in = getJsonBody();
$userId = $in['userId'] ?? null;
$search = trim($in['search'] ?? '');

// Validate required fields
if (!is_numeric($userId)) {
  sendJson(['results'=>[], 'error'=>'userId (int) required'], 400);
}

// DB
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
if ($conn->connect_error) {
  sendJson(['results'=>[], 'error'=>'DB connection failed'], 500);
}

// Prepare query
$stmt = $conn->prepare("SELECT Name FROM Contacts WHERE Name LIKE ? AND UserID = ?");
if (!$stmt) {
  $conn->close();
  sendJson(['results'=>[], 'error'=>'Prepare failed'], 500);
}
$like = "%".$search."%";
$stmt->bind_param("si", $like, $userId);

// Execute
if (!$stmt->execute()) {
  $stmt->close(); $conn->close();
  sendJson(['results'=>[], 'error'=>'Query failed'], 500);
}

$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
  $items[] = $row['Name'];
}

$stmt->close();
$conn->close();

// Always 200, empty list if none (matches your current API behavior)
sendJson(['results' => $items, 'error' => ''], 200);
