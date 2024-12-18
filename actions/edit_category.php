<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/category_functions.php';

// Check if user is logged in and has the correct role (superadmin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'category');

        // Debug logging
        error_log("Edit Category Attempt:");
        error_log("Category ID: $categoryId");
        error_log("Name: $name");
        error_log("Description: $description");
        error_log("Icon: $icon");
        error_log("POST Data: " . print_r($_POST, true));

        if (empty($categoryId)) {
            throw new Exception('Category ID is required');
        }

        if (empty($name)) {
            throw new Exception('Category name is required');
        }

        // Update function to include icon
        $updateResult = updateCategory($conn, $categoryId, $name, $description, $icon);
        error_log("Update Result: " . ($updateResult ? 'Success' : 'Failure'));

        if ($updateResult) {
            $_SESSION['success_message'] = 'Category updated successfully';
            header('Location: ../view/admin/dashboard.php');
            exit();
        } else {
            error_log("Failed to update category: ID $categoryId, Name $name");
            throw new Exception('Failed to update category');
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: ../view/admin/dashboard.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: ../view/admin/dashboard.php');
    exit();
}