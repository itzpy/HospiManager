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

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse the incoming DELETE request
    parse_str(file_get_contents("php://input"), $_DELETE);
    
    // Get the user ID from the request
    $userId = $_DELETE['id'];

    // Check if userId is set
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User  ID is missing.']);
        exit();
    }

    // Call the function to delete the user
    if (deleteUser ($userId)) {
        echo json_encode(['success' => true, 'message' => 'User  deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Function to delete a user from the database
function deleteUser ($userId) {
    global $conn; // Get the database connection

    // Prepare the SQL DELETE statement
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the user ID parameter
    $stmt->bind_param("i", $userId); // "i" indicates that the parameter is an integer

    // Execute the statement
    return $stmt->execute(); 
}
?>