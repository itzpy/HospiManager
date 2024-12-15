<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/inventory_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $adjustType = $_POST['adjust_type'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $notes = trim($_POST['notes']);

        // Validate required fields
        if (empty($itemId)) {
            throw new Exception('Item ID is required');
        }
        if (!in_array($adjustType, ['add', 'remove'])) {
            throw new Exception('Invalid adjustment type');
        }
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than 0');
        }
        if (empty($notes)) {
            throw new Exception('Notes are required');
        }

        // Adjust the stock
        if (adjustStock($conn, $itemId, $quantity, $adjustType, $notes)) {
            echo json_encode([
                'success' => true,
                'message' => 'Stock adjusted successfully'
            ]);
        } else {
            throw new Exception('Failed to adjust stock');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
