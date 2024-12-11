<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSuperAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'superadmin';
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}
?>