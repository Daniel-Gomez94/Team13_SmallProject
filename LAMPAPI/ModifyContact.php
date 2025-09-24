<?php
// configure cross-origin resource sharing headers
header("Access-Control-Allow-Origin: http://www.myphonebook.xyz");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// helper functions for json handling
function sendJson($obj, $status = 200) {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($obj);
  exit;
}
function getJsonBody() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    sendJson(['error' => 'Invalid JSON body'], 400);
  }
  return $data ?: [];
}

// read and validate input parameters
$in = getJsonBody();
$id        = $in['id'] ?? null;        // contact id to update
$userId    = $in['userId'] ?? null;    // owner user id
$firstName = isset($in['firstName']) ? trim($in['firstName']) : '';
$lastName  = isset($in['lastName'])  ? trim($in['lastName'])  : '';
$email     = isset($in['email'])     ? trim($in['email'])     : '';
$phone     = isset($in['phone'])     ? trim($in['phone'])     : '';

if (!is_numeric($id) || !is_numeric($userId) || $firstName === '' || $lastName === '') {
  sendJson(['error' => 'id, userId, firstName, and lastName are required'], 400);
}

// establish database connection
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "Smallproject");
if ($conn->connect_error) { sendJson(['error' => 'database connection failed'], 500); }

// verify contact exists and belongs to user
$chk = $conn->prepare("SELECT ID FROM Contacts WHERE ID = ? AND UserID = ? LIMIT 1");
if (!$chk) { $conn->close(); sendJson(['error' => 'prepare failed'], 500); }
$chk->bind_param("ii", $id, $userId);
$chk->execute();
$exists = $chk->get_result()->fetch_assoc();
$chk->close();
if (!$exists) {
  $conn->close();
  sendJson(['error' => 'contact not found for this userId'], 404);
}

// update contact record
$stmt = $conn->prepare(
  "UPDATE Contacts SET FirstName = ?, LastName = ?, Email = ?, Phone = ? WHERE ID = ? AND UserID = ?"
);
if (!$stmt) { $conn->close(); sendJson(['error' => 'prepare failed'], 500); }

$stmt->bind_param("ssssii", $firstName, $lastName, $email, $phone, $id, $userId);
if (!$stmt->execute()) {
  $err = $stmt->error; $stmt->close(); $conn->close();
  sendJson(['error' => "update failed: $err"], 500);
}

$affected = $stmt->affected_rows;
$stmt->close(); $conn->close();

if ($affected !== 1) {
  sendJson(['error' => 'contact not updated'], 500);
}

// return success response
sendJson([
  'id'        => (int)$id,
  'userId'    => (int)$userId,
  'firstName' => $firstName,
  'lastName'  => $lastName,
  'email'     => $email,
  'phone'     => $phone,
  'error'     => ''
], 200);