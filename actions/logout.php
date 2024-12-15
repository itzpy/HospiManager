<?php
// Start session
session_start();

// Clear session data
$_SESSION = array();

// If a session cookie is used, destroy it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear any other cookies that might have been set
setcookie('remember_me', '', time() - 3600, '/');
setcookie('user_id', '', time() - 3600, '/');

// Prevent caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page with a fresh state
header("Location: ../view/login.php");
exit();
?>
