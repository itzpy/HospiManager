<?php
session_start();
require_once '../db/database.php';
require_once '../functions/user_functions.php';

// Ensure JSON response
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Validation
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        if (empty($password)) {
            throw new Exception('Password is required');
        }

        // Check for existing user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception('Database error: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Invalid email or password');
        }

        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid email or password');
        }

        // Update last login timestamp
        updateLastLogin($conn, $user['user_id']);

        // Set session data
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => in_array($user['role'], ['admin', 'superadmin', 'staff']) 
                ? '../view/admin/admin_dashboard.php' 
                : '../view/admin/dashboard.php'
        ]);
        exit();

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}
