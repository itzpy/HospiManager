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
    // Get the email from the request
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Validation
    if (empty($email)) {
        $response['errors']['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Invalid email format";
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
        $response['errors']['general'] = "No account found with that email";
        echo json_encode($response);
        exit();
    }

    // Generate a unique token
    $token = bin2hex(random_bytes(50));

    // Store the token in the database with an expiration time
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $insert_query = "INSERT INTO password_resets (email, token, expires_at) VALUES ('$email', '$token', '$expiry')";
    if (mysqli_query($conn, $insert_query)) {
        // Send the reset link to the user's email
        $reset_link = "http://yourdomain.com/view/reset_password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: $reset_link";
        $headers = "From: no-reply@yourdomain.com";

        if (mail($email, $subject, $message, $headers)) {
            $response['success'] = true;
            $response['message'] = "Password reset link has been sent to your email.";
        } else {
            $response['errors']['general'] = "Failed to send email. Please try again.";
        }
    } else {
        $response['errors']['general'] = "Failed to process request. Please try again.";
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Hospital Management System</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="wrapper">
        <div class="form-box">
            <div class="login-container">
                <header>Forgot Password</header>
                <form id="forgotPasswordForm">
                    <div class="input-box">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="input-field"
                            placeholder="Enter your email"
                            required
                        />
                    </div>
                    <span id="emailError" class="error-message"></span>
                    <button type="submit" class="submit">Send Reset Link</button>
                </form>
            </div>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../assets/js/forgot_password.js"></script>
</body>
</html>