<?php
session_start();
require '../db/database.php'; // Include your database connection file
require '../functions/auth_functions.php'; // Include your authentication functions

// Check if user is logged in and has the correct role (super admin)
if (!isLoggedIn() || $_SESSION['user_role'] != 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
    exit();
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get the category ID from the request
    $categoryId = mysqli_real_escape_string($conn, trim($_GET['id']));

    // Prepare the SQL DELETE statement
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("i", $categoryId);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete category.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>