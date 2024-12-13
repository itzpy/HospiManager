<?php
session_start();
require '../db/database.php'; // Include your database connection file
require '../functions/auth_functions.php'; // Include your authentication functions

// Check if user is logged in and has the correct role (admin or superadmin)
if (!isLoggedIn() || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $itemId = mysqli_real_escape_string($conn, trim($_POST['item']));
    $quantity = mysqli_real_escape_string($conn, trim($_POST['quantity']));

    // Prepare the SQL UPDATE statement
    $stmt = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ii", $quantity, $itemId);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item quantity deducted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to deduct item quantity.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>