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
    debugLog("Sending response: " . json_encode($response));
    
    // Output JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(0);
}

// Absolute path check
define('ABSPATH', true);

// Capture any potential errors
try {
    // Require necessary files
    require_once dirname(__FILE__) . '/../db/database.php';
    require_once dirname(__FILE__) . '/../functions/auth_functions.php';
    require_once dirname(__FILE__) . '/../functions/item_functions.php';

    // Log database connection status
    debugLog("Database connection status: " . ($conn ? "Connected" : "Failed"));
    
    // Validate user access
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        debugLog("Unauthorized access attempt. Session role: " . ($_SESSION['role'] ?? 'not set'));
        sendJsonResponse(false, 'Unauthorized: Only superadmin can delete items', [
            'session_role' => $_SESSION['role'] ?? 'not set'
        ]);
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        debugLog("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        sendJsonResponse(false, 'Invalid request method');
    }

    // Validate database connection
    if (!$conn) {
        debugLog("Database connection failed: " . mysqli_connect_error());
        sendJsonResponse(false, 'Database connection failed', [
            'error' => mysqli_connect_error(),
            'conn_errno' => mysqli_connect_errno()
        ]);
    }

    // Validate item ID
    if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id'])) {
        debugLog("Invalid item ID received: " . ($_POST['item_id'] ?? 'not set'));
        sendJsonResponse(false, 'Invalid item ID', [
            'received_item_id' => $_POST['item_id'] ?? 'not set'
        ]);
    }

    $itemId = intval($_POST['item_id']);

    // Comprehensive debug information
    $debugInfo = [
        'item_id' => $itemId,
        'session_role' => $_SESSION['role'] ?? 'not set',
        'session_user_id' => $_SESSION['user_id'] ?? 'not set'
    ];

    // Check item existence and get full details
    $checkQuery = "SELECT * FROM items WHERE item_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $itemId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $debugInfo['error'] = 'No item found with the given ID';
        debugLog("Item not found. Item ID: $itemId");
        sendJsonResponse(false, 'Item not found', $debugInfo);
    }

    // Fetch item details for logging
    $itemDetails = $checkResult->fetch_assoc();
    $debugInfo['item_details'] = $itemDetails;

    // Check related records
    $relatedQueries = [
        'activity_log_count' => "SELECT COUNT(*) as count FROM activity_log WHERE item_id = ?",
        'inventory_transactions_count' => "SELECT COUNT(*) as count FROM inventory_transactions WHERE item_id = ?"
    ];

    $relatedRecords = [];
    foreach ($relatedQueries as $key => $query) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $relatedRecords[$key] = $result['count'];
    }
    $debugInfo['related_records'] = $relatedRecords;

    // Attempt to delete the item
    try {
        $deleteResult = deleteItem($conn, $itemId);
        
        if ($deleteResult) {
            // Fetch additional details about the deleted item and related records
            $itemDetailsQuery = "SELECT * FROM items WHERE item_id = ? AND is_deleted = 1";
            $itemDetailsStmt = $conn->prepare($itemDetailsQuery);
            $itemDetailsStmt->bind_param("i", $itemId);
            $itemDetailsStmt->execute();
            $itemDetailsResult = $itemDetailsStmt->get_result();
            $itemDetails = $itemDetailsResult->fetch_assoc() ?: null;

            // Count related records
            $activityLogCountQuery = "SELECT COUNT(*) as count FROM activity_log WHERE item_id = ?";
            $activityLogCountStmt = $conn->prepare($activityLogCountQuery);
            $activityLogCountStmt->bind_param("i", $itemId);
            $activityLogCountStmt->execute();
            $activityLogCountResult = $activityLogCountStmt->get_result();
            $activityLogCount = $activityLogCountResult->fetch_assoc()['count'];

            $inventoryTransactionsCountQuery = "SHOW TABLES LIKE 'inventory_transactions'";
            $inventoryTransactionsTableResult = $conn->query($inventoryTransactionsCountQuery);
            
            $inventoryTransactionsCount = 0;
            if ($inventoryTransactionsTableResult->num_rows > 0) {
                $inventoryTransactionsCountQuery = "SELECT COUNT(*) as count FROM inventory_transactions WHERE item_id = ?";
                $inventoryTransactionsCountStmt = $conn->prepare($inventoryTransactionsCountQuery);
                $inventoryTransactionsCountStmt->bind_param("i", $itemId);
                $inventoryTransactionsCountStmt->execute();
                $inventoryTransactionsCountResult = $inventoryTransactionsCountStmt->get_result();
                $inventoryTransactionsCount = $inventoryTransactionsCountResult->fetch_assoc()['count'];
            }

            // Prepare response with detailed information
            $response = [
                'success' => true,
                'message' => 'Item deleted successfully',
                'item_id' => $itemId,
                'session_role' => $_SESSION['role'] ?? 'unknown',
                'session_user_id' => $_SESSION['user_id'] ?? 0,
                'item_details' => $itemDetails,
                'related_records' => [
                    'activity_log_count' => $activityLogCount,
                    'inventory_transactions_count' => $inventoryTransactionsCount
                ]
            ];

            // Log successful deletion
            $logMessage = date('[Y-m-d H:i:s] ') . "Item deleted successfully. Item ID: $itemId\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        } else {
            // Add MySQL error details
            $debugInfo['mysql_error'] = [
                'errno' => $conn->errno,
                'error' => $conn->error
            ];
            
            debugLog("Failed to delete item. Item ID: $itemId. Error: " . $conn->error);
            sendJsonResponse(false, 'Failed to delete item', $debugInfo);
        }
    } catch (Exception $e) {
        // Enhanced error logging
        $errorMessage = $e->getMessage();
        error_log("Item Deletion Error: $errorMessage");
        
        $response = [
            'success' => false,
            'message' => 'Unexpected error occurred',
            'error_message' => $errorMessage,
            'error_trace' => $e->getTraceAsString()
        ];
    }
} catch (Exception $e) {
    // Catch any unexpected errors
    debugLog("Unexpected error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'Unexpected error occurred', [
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString()
    ]);
} finally {
    // Ensure all output is cleared
    while (ob_get_level()) {
        ob_end_clean();
    }
    exit(0);
}