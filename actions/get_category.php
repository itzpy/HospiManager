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

// Check if ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Category ID is required'
    ]);
    exit();
}

try {
    $categoryId = intval($_GET['id']);
    
    // Get category data
    $stmt = $conn->prepare("SELECT category_id, name, description FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Category not found');
    }
    
    $category = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'category' => $category
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
