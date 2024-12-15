<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/inventory_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all incoming POST data
file_put_contents('stock_adjustment_log.txt', 
    date('Y-m-d H:i:s') . " - Incoming Request:\n" . 
    print_r($_POST, true) . 
    "\nSession Data:\n" . 
    print_r($_SESSION, true) . 
    "\n\n", 
    FILE_APPEND
);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

// Get user role
$userRole = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate session and user role
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            throw new Exception('User not authenticated');
        }

        // Get and validate form data
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $action = $_POST['adjust_type'] ?? 'remove';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $notes = trim($_POST['notes'] ?? '');

        // Log received data for debugging
        file_put_contents('stock_adjustment_log.txt', 
            date('Y-m-d H:i:s') . " - Processed Data:\n" . 
            "Item ID: $itemId\n" .
            "Action: $action\n" .
            "Quantity: $quantity\n" .
            "Notes: $notes\n" .
            "User Role: {$_SESSION['role']}\n\n", 
            FILE_APPEND
        );

        // Validate required fields
        if (empty($itemId)) {
            throw new Exception('Item ID is required');
        }
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than 0');
        }
        if (empty($notes)) {
            throw new Exception('Notes are required');
        }

        // Restrict actions based on user role
        $userRole = $_SESSION['role'];
        
        // Validate action
        if (!in_array($action, ['add', 'remove'])) {
            throw new Exception('Invalid stock action');
        }

        // Prepare notes with user context
        $notes = "Stock {$action}d by {$userRole}: {$notes}";
        
        // Attempt to adjust stock
        $adjustmentResult = adjustStock($conn, $itemId, $quantity, $action, $notes);
        
        // Prepare response
        header('Content-Type: application/json');
        
        if ($adjustmentResult) {
            // Get updated item details
            $updatedItem = getItemById($conn, $itemId);
            
            echo json_encode([
                'success' => true, 
                'message' => "Stock {$action}d successfully", 
                'newQuantity' => $updatedItem['quantity']
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => "Failed to {$action} stock. Please check your input."
            ]);
        }
        exit();
    } catch (Exception $e) {
        // Log the error
        file_put_contents('stock_adjustment_log.txt', 
            date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n\n", 
            FILE_APPEND
        );

        // Return error response
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
} else {
    // Log invalid request method
    file_put_contents('stock_adjustment_log.txt', 
        date('Y-m-d H:i:s') . " - Invalid Request Method\n\n", 
        FILE_APPEND
    );

    // Return error for invalid request method
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
