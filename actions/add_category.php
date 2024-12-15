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

// Validate input
if (!isset($_POST['name']) || !isset($_POST['description'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Name and description are required'
    ]);
    exit();
}

try {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Check if category name already exists
    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Category name already exists');
    }
    
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to add category');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Category added successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}