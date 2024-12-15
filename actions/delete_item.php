<?php
// Strict error handling
ini_set('display_errors', 0);
error_reporting(0);

// Prevent any output before headers
ob_start();

// Start session
session_start();

// Disable output buffering and error display
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 'Off');
@ini_set('display_errors', 0);
@error_reporting(0);

// Clear any existing output
while (ob_get_level()) {
    ob_end_clean();
}

// Absolute path check
define('ABSPATH', true);

// Require necessary files
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/item_functions.php';

// Set headers immediately
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $additionalData = []) {
    // Ensure no previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Prepare response
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $additionalData);
    
    // Output JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(0);
}

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    sendJsonResponse(false, 'Unauthorized: Only superadmin can delete items');
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
    exit();
}

// Validate database connection
if (!$conn || $conn->connect_error) {
    sendJsonResponse(false, 'Database connection failed', [
        'debug' => [
            'connection_error' => $conn ? $conn->connect_error : 'Connection object is null'
        ]
    ]);
}

// Get and validate item ID
$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

if (empty($itemId)) {
    sendJsonResponse(false, 'Item ID is required', [
        'debug' => [
            'post_data' => $_POST
        ]
    ]);
}

try {
    // Verify item exists
    $checkQuery = "SELECT * FROM items WHERE item_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $itemId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        sendJsonResponse(false, 'Item not found', [
            'debug' => [
                'item_id' => $itemId
            ]
        ]);
    }
    
    // Fetch item details
    $itemDetails = $checkResult->fetch_assoc();

    // Attempt to delete the item
    $deleteResult = deleteItem($conn, $itemId);
    
    if ($deleteResult) {
        sendJsonResponse(true, 'Item deleted successfully', [
            'item_details' => $itemDetails
        ]);
    } else {
        sendJsonResponse(false, 'Failed to delete item', [
            'debug' => [
                'item_id' => $itemId,
                'item_details' => $itemDetails,
                'last_error' => $conn->error
            ]
        ]);
    }
} catch (Exception $e) {
    sendJsonResponse(false, 'An error occurred while deleting the item', [
        'debug' => [
            'exception_message' => $e->getMessage(),
            'item_id' => $itemId
        ]
    ]);
}

// Ensure no additional output
exit(0);