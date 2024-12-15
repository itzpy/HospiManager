<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/item_functions.php';

// Check if user is logged in and has appropriate role (admin or superadmin)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $adjustType = trim($_POST['adjust_type'] ?? 'remove');
        $notes = trim($_POST['notes'] ?? '');

        // Validate required fields
        if (empty($itemId)) {
            throw new Exception('Item ID is required');
        }
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than 0');
        }
        if (empty($notes)) {
            throw new Exception('Notes are required to track inventory changes');
        }

        // Determine actual quantity based on adjustment type
        $actualQuantity = ($adjustType === 'remove') ? -$quantity : $quantity;

        // Remove or add to inventory
        if (removeFromInventory($conn, $itemId, $actualQuantity, $notes)) {
            echo json_encode([
                'success' => true,
                'message' => 'Inventory updated successfully'
            ]);
        } else {
            error_log("Failed to update inventory - Item ID: $itemId, Quantity: $actualQuantity, Type: $adjustType");
            throw new Exception('Failed to update inventory');
        }
    } catch (Exception $e) {
        error_log("Inventory Update Error: " . $e->getMessage());
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
