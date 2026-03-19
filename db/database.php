<?php
require_once dirname(__DIR__) . '/config/env.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_errno) {
    if (APP_ENV === 'development') {
        die("Connection failed: " . $conn->connect_error);
    } else {
        error_log("DB connection failed: " . $conn->connect_error);
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'A server error occurred. Please try again later.']));
    }
}

$conn->set_charset('utf8mb4');
