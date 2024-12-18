<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log file path
$logFile = dirname(__FILE__) . '/../logs/delete_item_debug.log';

// Logging function
function debugLog($message) {
    global $logFile;
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, "$timestamp $message\n", FILE_APPEND);
}

// Log the incoming request
debugLog("Incoming delete request: " . print_r($_POST, true));
debugLog("Request URI: " . $_SERVER['REQUEST_URI']);
debugLog("Script Name: " . $_SERVER['SCRIPT_NAME']);

// Start output buffering immediately
ob_start();

// Start session
session_start();

// Debug session information
debugLog("Session User ID: " . ($_SESSION['user_id'] ?? 'Not Set'));
debugLog("Session User Role: " . ($_SESSION['role'] ?? 'Not Set'));

// Debug: Log all session variables
debugLog("All Session Variables: " . print_r($_SESSION, true));

// Disable output buffering and error display
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 'Off');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $additionalData = []) {
    // Clear any existing output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set JSON headers
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Prepare response
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $additionalData);
    
    // Log the response
    debugLog("Response: " . json_encode($response));
    
    // Output JSON response
    echo json_encode($response);
    exit;
}

// Check for POST data
if (!isset($_POST['item_id'])) {
    debugLog("No item_id provided in POST request");
    sendJsonResponse(false, 'No item ID provided', [
        'post_data' => $_POST
    ]);
}

// Validate item ID
$itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
if ($itemId === false || $itemId === null) {
    debugLog("Invalid item ID: " . $_POST['item_id']);
    sendJsonResponse(false, 'Invalid item ID', [
        'received_item_id' => $_POST['item_id']
    ]);
}

// Absolute path check
define('ABSPATH', true);

// Capture any potential errors
try {
    // Require necessary files
    require_once dirname(__FILE__) . '/../db/database.php';
    require_once dirname(__FILE__) . '/../functions/auth_functions.php';
    require_once dirname(__FILE__) . '/../functions/item_functions.php';

    // Check user authentication and authorization
    if (!isset($_SESSION['user_id'])) {
        debugLog("User not logged in");
        sendJsonResponse(false, 'User not logged in');
    }

    // Check user role (allow superadmin and admin to delete)
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'admin'])) {
        debugLog("Unauthorized delete attempt by user role: " . ($_SESSION['role'] ?? 'No Role'));
        debugLog("Unauthorized delete attempt by user ID: " . ($_SESSION['user_id'] ?? 'No User ID'));
        sendJsonResponse(false, 'You do not have permission to delete items');
    }

    // Attempt to delete the item
    try {
        $deleteResult = deleteItem($conn, $itemId);
        
        if ($deleteResult) {
            // Fetch additional details about the deleted item and related records
            debugLog("Item successfully deleted - Item ID: $itemId");
            sendJsonResponse(true, 'Item deleted successfully', [
                'item_id' => $itemId
            ]);
        } else {
            debugLog("Failed to delete item - Item ID: $itemId");
            sendJsonResponse(false, 'Failed to delete item', [
                'item_id' => $itemId
            ]);
        }
    } catch (Exception $e) {
        // Log any exceptions during deletion
        debugLog("Exception during item deletion: " . $e->getMessage());
        debugLog("Exception Trace: " . $e->getTraceAsString());
        
        sendJsonResponse(false, 'Error deleting item', [
            'error_message' => $e->getMessage(),
            'item_id' => $itemId
        ]);
    }
} catch (Exception $e) {
    // Catch any unexpected errors
    debugLog("Unexpected error: " . $e->getMessage());
    debugLog("Unexpected Error Trace: " . $e->getTraceAsString());
    
    sendJsonResponse(false, 'Unexpected error occurred', [
        'error_message' => $e->getMessage()
    ]);
}