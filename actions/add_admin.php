<?php
session_start();
require '../db/database.php'; // Include your database connection file
require '../functions/auth_functions.php'; // Include your authentication functions
require '../functions/user_functions.php'; // Include your user functions

// Check if user is logged in and has the correct role (super admin)
if (!isLoggedIn() || $_SESSION['user_role'] != 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $firstName = mysqli_real_escape_string($conn, trim($_POST['firstName']));
    $lastName = mysqli_real_escape_string($conn, trim($_POST['lastName']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $defaultPassword = 'Admin@10!'; // Default password

    // Hash the default password
    $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

    // Prepare the SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Admin added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add admin.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>