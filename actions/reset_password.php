<?php
session_start();
require '../db/database.php'; // Include your database connection file

// Ensure JSON response
header('Content-Type: application/json');

// Response array
$response = [
    'success' => false,
    'errors' => []
];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $token = mysqli_real_escape_string($conn, trim($_POST['token']));
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validation
    if (empty($newPassword)) {
        $response['errors']['newPassword'] = "New password is required";
    } elseif (strlen($newPassword) < 8) {
        $response['errors']['newPassword'] = "Password must be at least 8 characters";
    }

    if ($newPassword !== $confirmPassword) {
        $response['errors']['confirmPassword'] = "Passwords do not match";
    }

    // Check if there are any validation errors
    if (!empty($response['errors'])) {
        echo json_encode($response);
        exit();
    }

    // Check for valid token
    $check_query = "SELECT * FROM password_resets WHERE token = '$token' AND expires_at > NOW()";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) === 0) {
        $response['errors']['general'] = "Invalid or expired token";
        echo json_encode($response);
        exit();
    }

    // Fetch user email
    $reset = mysqli_fetch_assoc($check_result);
    $email = $reset['email'];

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the user's password in the database
    $update_query = "UPDATE users SET password = '$hashedPassword' WHERE email = '$email'";
    if (mysqli_query($conn, $update_query)) {
        // Delete the token from the database
        $delete_query = "DELETE FROM password_resets WHERE token = '$token'";
        mysqli_query($conn, $delete_query);

        $response['success'] = true;
        $response['message'] = "Password has been reset successfully.";
    } else {
        $response['errors']['general'] = "Failed to reset password. Please try again.";
    }

    echo json_encode($response);
    exit();
} else {
    // If not a POST request
    $response['errors']['general'] = "Invalid request method";
    echo json_encode($response);
    exit();
}
?>