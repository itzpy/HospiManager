<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure JSON response
header('Content-Type: application/json');

// Database connection
require_once '../db/database.php';

// Response array
$response = [
    'success' => false,
    'errors' => []
];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the email and password from the request
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    // Debug: Print out received POST data
    error_log("Received POST data:");
    error_log(print_r($_POST, true));

    // Validation
    if (empty($email)) {
        $response['errors']['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Invalid email format";
    }

    if (empty($password)) {
        $response['errors']['password'] = "Password is required";
    }

      // Check if there are any validation errors
      if (!empty($response['errors'])) {
        echo json_encode($response);
        exit();
    }

    // Check for existing user
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) === 0) {
        $response['errors']['general'] = "Invalid email or password";
        echo json_encode($response);
        exit();
    }

    // Fetch user data
    $user = mysqli_fetch_assoc($check_result);

    // Verify password
    if (!password_verify($password, $user['password'])) {
        $response['errors']['general'] = "Invalid email or password";
        echo json_encode($response);
        exit();
    }

    // Start session and set user data
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Login successful
    $response['success'] = true;
    $response['message'] = "Login successful. Redirecting to dashboard...";
    echo json_encode($response);
    exit();
} else {
    // If not a POST request
    $response['errors']['general'] = "Invalid request method";
    echo json_encode($response);
    exit();
}