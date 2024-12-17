<?php
session_start();
require_once '../config/database.php';

// Ensure no output before JSON
ob_clean();

// Ensure JSON content type
header('Content-Type: application/json');

// Response array
$response = [
    'success' => false,
    'message' => '',
    'role' => ''
];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    // Validate email
    if (!$email) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    try {
        // Check if connection exists
        if (!isset($conn) || $conn->connect_error) {
            $response['message'] = 'Database connection error.';
            error_log('Database connection not established');
            echo json_encode($response);
            exit;
        }

        // Prepare SQL to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, first_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Log query details for debugging
        error_log("Login attempt for email: $email");
        error_log("Number of rows found: " . $result->num_rows);

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Log user details for debugging
            error_log("User found - ID: " . $user['user_id'] . ", Role: " . $user['role']);

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];

                // Prepare successful response
                $response['success'] = true;
                $response['role'] = $user['role'];
                $response['message'] = 'Login successful!';

                // Log successful login
                error_log("Successful login for user: " . $user['first_name'] . " (Role: " . $user['role'] . ")");
            } else {
                $response['message'] = 'Invalid password.';
                error_log("Invalid password attempt for email: $email");
            }
        } else {
            $response['message'] = 'No user found with this email.';
            error_log("No user found for email: $email");
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Login error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
    error_log("Invalid login request method");
}

// Send JSON response
echo json_encode($response);
$conn->close();
?>
