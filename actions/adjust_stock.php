<?php
header('Content-Type: application/json');

// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Your existing stock adjustment logic here
    
    // On success
    echo json_encode([
        'success' => true,
        'message' => 'Stock adjusted successfully'
    ]);
    exit;
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Stock Adjustment Error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to adjust stock: ' . $e->getMessage()
    ]);
    exit;
}
