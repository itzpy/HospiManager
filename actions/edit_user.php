<?php
// Disable all error reporting to prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../db/database.php';
require_once 'functions.php';

// Ensure JSON response
header('Content-Type: application/json');

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied'
    ]);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Validate database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Validate required fields
    $requiredFields = ['user_id', 'first_name', 'last_name', 'email', 'role'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("$field is required");
        }
    }

    $userId = intval($_POST['user_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = !empty($_POST['password']) ? trim($_POST['password']) : null;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate role
    $validRoles = ['admin', 'superadmin', 'staff'];
    if (!in_array($role, $validRoles)) {
        throw new Exception('Invalid role');
    }

    // Prepare update query
    $updateQuery = "UPDATE users SET 
        first_name = ?, 
        last_name = ?, 
        email = ?, 
        role = ?";  // Removed last_update
    
    $paramTypes = "ssss";
    $params = [&$firstName, &$lastName, &$email, &$role];

    // Add password update if provided
    if ($password !== null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery .= ", password = ?";
        $paramTypes .= "s";
        $params[] = &$hashedPassword;
    }

    $updateQuery .= " WHERE user_id = ?";
    $paramTypes .= "i";
    $params[] = &$userId;

    // Prepare and execute update
    $stmt = $conn->prepare($updateQuery);
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    // Dynamically bind parameters
    call_user_func_array([$stmt, 'bind_param'], array_merge([$paramTypes], $params));

    if (!$stmt->execute()) {
        throw new Exception('Update failed: ' . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception('No user updated. User may not exist.');
    }

    // Close statement
    $stmt->close();

    // Log activity
    logActivity($conn, $_SESSION['user_id'], $userId, 'update', 0, "Updated user: $firstName $lastName");

    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
