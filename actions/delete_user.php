<?php
session_start();
require_once '../db/database.php';

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

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit();
}

try {
    $userId = intval($data['user_id']);
    
    // Prevent deleting own account
    if ($userId === $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete user');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
