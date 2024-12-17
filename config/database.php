<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'papa.badu');
define('DB_PASS', 'password');
define('DB_NAME', 'webtech_fall2024_papa_badu');

// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'hospital_management');


try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}
?>
