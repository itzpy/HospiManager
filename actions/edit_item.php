<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/item_functions.php';

// Check if user is logged in and has the correct role (superadmin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $unit = trim($_POST['unit']);

        // Debug logging
        error_log("Edit Item Request Details:");
        error_log("Item ID: $itemId");
        error_log("Category ID: $categoryId");
        error_log("Name: $name");
        error_log("Description: $description");
        error_log("Quantity: $quantity");
        error_log("Unit: $unit");

        // Validate required fields
        if (empty($itemId)) {
            throw new Exception('Item ID is required');
        }
        if (empty($categoryId)) {
            throw new Exception('Category is required');
        }
        if (empty($name)) {
            throw new Exception('Item name is required');
        }
        if ($quantity < 0) {
            throw new Exception('Quantity cannot be negative');
        }
        if (empty($unit)) {
            throw new Exception('Unit of measurement is required');
        }

        // Update the item
        $updateResult = updateItem($conn, $itemId, $categoryId, $name, $description, $quantity, $unit);
        
        if ($updateResult) {
            echo json_encode([
                'success' => true,
                'message' => 'Item updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update item. Check server logs for details.');
        }
    } catch (Exception $e) {
        error_log("Edit Item Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
