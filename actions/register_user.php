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

// Check if form is submitted via POST
if (isset($_POST)) {
    // Sanitize and validate inputs
    // Change these to match the form input names exactly
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first-name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last-name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Debug: Print out received POST data
    error_log("Received POST data:");
    error_log(print_r($_POST, true));

    // Rest of the code remains the same...
    // Validation
    $errors = [];

    // Validate First Name
    if (empty($first_name)) {
        $errors['first_name'] = "First name is required";
    } elseif (strlen($first_name) < 2) {
        $errors['first_name'] = "First name must be at least 2 characters";
    }

    // Validate Last Name
    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required";
    } elseif (strlen($last_name) < 2) {
        $errors['last_name'] = "Last name must be at least 2 characters";
    }

    // Validate Email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    // Validate Password
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }

    // Confirm Password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // Check if there are any validation errors
    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit();
    }

    // Check for existing user
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $response['errors']['email'] = "Email already exists";
        echo json_encode($response);
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Prepare SQL insert statement
    $insert_query = "INSERT INTO users (
        fname, 
        lname, 
        email, 
        password, 
        role, 
        created_at, 
        updated_at
    ) VALUES (
        '$first_name', 
        '$last_name', 
        '$email', 
        '$hashed_password', 
        2, 
        NOW(), 
        NOW()
    )";

    // Execute the query
    if (mysqli_query($conn, $insert_query)) {
        // Registration successful
        $response['success'] = true;
        echo json_encode($response);
        exit();
    } else {
        // Database insertion error
        $response['errors']['general'] = "Registration failed: " . mysqli_error($conn);
        echo json_encode($response);
        exit();
    }
}else{
    // If not a POST request
    $response['errors']['general'] = "Invalid request method";
    echo json_encode($response);
    exit();
}

