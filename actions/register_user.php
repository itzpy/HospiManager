<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../db/database.php';

$response = ['success' => false, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['errors']['general'] = "Invalid request method";
    echo json_encode($response);
    exit();
}

$first_name = trim($_POST['first-name'] ?? '');
$last_name  = trim($_POST['last-name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm-password'] ?? '';

// Validation
if (empty($first_name) || strlen($first_name) < 2) {
    $response['errors']['first_name'] = empty($first_name) ? "First name is required" : "First name must be at least 2 characters";
}
if (empty($last_name) || strlen($last_name) < 2) {
    $response['errors']['last_name'] = empty($last_name) ? "Last name is required" : "Last name must be at least 2 characters";
}
if (empty($email)) {
    $response['errors']['email'] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['errors']['email'] = "Invalid email format";
}
if (empty($password)) {
    $response['errors']['password'] = "Password is required";
} elseif (strlen($password) < 8) {
    $response['errors']['password'] = "Password must be at least 8 characters";
}
if ($password !== $confirm) {
    $response['errors']['confirm_password'] = "Passwords do not match";
}

if (!empty($response['errors'])) {
    echo json_encode($response);
    exit();
}

// Check for existing user — prepared statement
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $response['errors']['email'] = "Email already exists";
    echo json_encode($response);
    $stmt->close();
    exit();
}
$stmt->close();

// Insert — prepared statement. New registrations default to 'staff' role.
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$role = 'staff';

$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    error_log("Registration DB error: " . $stmt->error);
    $response['errors']['general'] = "Registration failed. Please try again.";
}
$stmt->close();

echo json_encode($response);
