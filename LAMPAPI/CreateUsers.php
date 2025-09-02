<?php

header("Access-Control-Allow-Origin: http://138.197.87.182");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");


ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=utf-8");

// Debug messages collector
$debugMessages = [];

function debug($msg) {
    global $debugMessages;
    $timestamp = date("H:i:s");
    $debugMessages[] = "[$timestamp] $msg";
}

// Read and decode input
$rawInput = file_get_contents("php://input");
debug("RAW INPUT: $rawInput");

$inData = json_decode($rawInput, true);
$firstName = $inData["firstName"] ?? "";
$lastName  = $inData["lastName"] ?? "";
$login     = $inData["login"] ?? "";
$password  = $inData["password"] ?? "";

debug("Parsed Input → First: $firstName, Last: $lastName, Login: $login");

// Validate input
if (!$firstName || !$lastName || !$login || !$password) {
    debug("Missing required fields");
    returnWithError("All fields are required.");
    exit;
}

// Connect to database
$conn = new mysqli("localhost", "COP4331User", "COP4331Password", "COP4331");
if ($conn->connect_error) {
    debug("DB CONNECTION ERROR: " . $conn->connect_error);
    returnWithError("Database connection failed");
    exit;
}

// Check for duplicate login
$stmt = $conn->prepare("SELECT ID FROM Users WHERE Login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    debug("Username already exists");
    $stmt->close();
    $conn->close();
    returnWithError("Username already exists.");
    exit;
}
$stmt->close();

// Hash password
$hashedPassword = md5($password);
debug("Hashed Password: $hashedPassword");

// Insert new user
$stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $firstName, $lastName, $login, $hashedPassword);

if ($stmt->execute()) {
    debug("User created successfully");
    returnWithSuccess("User created successfully.");
} else {
    debug("Insert failed: " . $stmt->error);
    returnWithError("Insert failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Response helpers
function returnWithError($err) {
    global $debugMessages;
    echo json_encode([
        "error" => $err,
        "debug" => $debugMessages
    ]);
}

function returnWithSuccess($msg) {
    global $debugMessages;
    echo json_encode([
        "error" => "",
        "message" => $msg,
        "debug" => $debugMessages
    ]);
}
?>