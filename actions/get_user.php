<?php
// Disable all error reporting to prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

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

// Check if ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit();
}

try {
    $userId = intval($_GET['id']);
    
    // Validate database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get user data with more comprehensive query
    $stmt = $conn->prepare("SELECT 
        user_id, 
        first_name, 
        last_name, 
        email, 
        role 
    FROM users 
    WHERE user_id = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute statement failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $user = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
}
