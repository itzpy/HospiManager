<?php
session_start();
require_once '../config/database.php';
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
    if (!in_array($role, ['admin', 'superadmin'])) {
        throw new Exception('Invalid role');
    }

    // Check if email exists for other users
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email already exists');
    }

    // Update user
    if (updateUser($conn, $userId, $firstName, $lastName, $email, $role, $password)) {
        // Log activity
        logActivity($conn, $_SESSION['user_id'], $userId, 'update', 0, "Updated user: $firstName $lastName");
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update user');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
