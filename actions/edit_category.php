<?php
session_start();
require '../db/database.php'; // Include your database connection file
require '../functions/auth_functions.php'; // Include your authentication functions

// Check if user is logged in and has the correct role (super admin)
if (!isLoggedIn() || $_SESSION['user_role'] != 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $categoryId = mysqli_real_escape_string($conn, trim($_POST['id']));
    $categoryName = mysqli_real_escape_string($conn, trim($_POST['name']));

    // Prepare the SQL UPDATE statement
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("si", $categoryName, $categoryId);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update category.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>