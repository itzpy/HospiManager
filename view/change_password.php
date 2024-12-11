<?php
session_start();
require_once '../functions/auth_functions.php';
require_once '../db/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../view/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Change Password</h2>
        <form id="changePasswordForm" method="POST" action="../actions/change_password.php">
            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="currentPassword" required>
            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword" required>
            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
    <script src="../assets/js/change_password.js"></script>
</body>
</html>