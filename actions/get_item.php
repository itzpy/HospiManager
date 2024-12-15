<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/item_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get and validate item ID
        $itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (empty($itemId)) {
            throw new Exception('Item ID is required');
        }

        // Get the item details
        $item = getItemById($conn, $itemId);
        
        if ($item) {
            echo json_encode([
                'success' => true,
                'item' => $item
            ]);
        } else {
            throw new Exception('Item not found');
        }
    } catch (Exception $e) {
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
