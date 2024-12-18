<?php
// Disable all error output
@ini_set('display_errors', 0);
@error_reporting(0);

session_start();
header('Content-Type: application/json');

require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/inventory_functions.php';

// Function to log errors safely
function safeErrorLog($message) {
    $logFile = dirname(__FILE__) . '/stock_adjustment_log.txt';
    @file_put_contents($logFile, 
        date('Y-m-d H:i:s') . " - " . $message . "\n", 
        FILE_APPEND
    );
}

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, 'Unauthorized action');
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
        $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
        $action = filter_input(INPUT_POST, 'adjust_type', FILTER_SANITIZE_STRING);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

        // Detailed logging for debugging
        safeErrorLog("Stock Adjustment Request: " . json_encode([
            'item_id' => $itemId,
            'action' => $action,
            'quantity' => $quantity,
            'notes' => $notes,
            'user_role' => $userRole
        ]));

        // Validate required fields
        if ($itemId === false || $itemId === null) {
            throw new Exception('Invalid item ID');
        }
        if ($quantity === false || $quantity === null || $quantity <= 0) {
            throw new Exception('Quantity must be a positive number');
        }
        if (empty($notes)) {
            throw new Exception('Notes are required');
        }
        if (!in_array($action, ['add', 'remove'])) {
            throw new Exception('Invalid stock action');
        }

        // Prepare notes with user context
        $notes = "Stock {$action}d by {$userRole}: {$notes}";
        
        // Attempt to adjust stock
        $adjustmentResult = adjustStock($conn, $itemId, $quantity, $action, $notes);
        
        if ($adjustmentResult) {
            // Get updated item details
            $updatedItem = getItemById($conn, $itemId);
            
            sendJsonResponse(true, "Stock {$action}d successfully", [
                'newQuantity' => $updatedItem['quantity']
            ]);
        } else {
            sendJsonResponse(false, "Failed to {$action} stock. Please check your input.");
        }
    } catch (Exception $e) {
        // Log the error safely
        safeErrorLog("Stock Adjustment Error: " . $e->getMessage());

        // Return error response
        sendJsonResponse(false, $e->getMessage());
    }
} else {
    // Log invalid request method safely
    safeErrorLog("Invalid Request Method");

    // Return error for invalid request method
    sendJsonResponse(false, 'Invalid request method');
}
