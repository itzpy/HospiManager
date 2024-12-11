<?php
session_start();
require '../db/database.php'; // Include your database connection file
require '../functions/auth_functions.php'; // Include your authentication functions

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action.']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Fetch the current password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (!password_verify($currentPassword, $hashedPassword)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit();
    }

    // Check if new password and confirm password match
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit();
    }

    // Hash the new password
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $newHashedPassword, $userId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to change password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>