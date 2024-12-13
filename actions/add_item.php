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
    $itemName = mysqli_real_escape_string($conn, trim($_POST['itemName']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $quantity = mysqli_real_escape_string($conn, trim($_POST['quantity']));

    // Prepare the SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO items (name, category, quantity) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ssi", $itemName, $category, $quantity);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?><?php
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
    $itemName = mysqli_real_escape_string($conn, trim($_POST['itemName']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $quantity = mysqli_real_escape_string($conn, trim($_POST['quantity']));

    // Prepare the SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO items (name, category, quantity) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ssi", $itemName, $category, $quantity);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>