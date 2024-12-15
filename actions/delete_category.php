<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/category_functions.php';

// Check if user is logged in and has the correct role (superadmin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    $_SESSION['error_message'] = 'Unauthorized action';
    header('Location: ../view/admin/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        if (empty($categoryId)) {
            throw new Exception('Category ID is required');
        }

        // Use the deleteCategory function from category_functions.php
        if (deleteCategory($conn, $categoryId)) {
            $_SESSION['success_message'] = 'Category deleted successfully';
        } else {
            error_log("Failed to delete category: ID $categoryId");
            throw new Exception('Failed to delete category');
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
}

// Redirect back to dashboard
header('Location: ../view/admin/dashboard.php');
exit();