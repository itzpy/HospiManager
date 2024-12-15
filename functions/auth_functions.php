<?php
require_once dirname(__DIR__) . '/db/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSuperAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin';
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function checkUserCredentials($conn, $email, $password) {
    $sql = "SELECT user_id, password_hash FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            return $row['user_id'];
        }
    }
    return false;
}

function getUserRole($conn, $userId) {
    $sql = "SELECT role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['role'];
    }
    return null;
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
}

// Error handling function
function handleError($message, $redirectUrl = null) {
    $_SESSION['error'] = $message;
    if ($redirectUrl) {
        header("Location: $redirectUrl");
        exit();
    }
}
?>