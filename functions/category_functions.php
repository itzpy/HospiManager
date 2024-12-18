<?php
require_once dirname(__DIR__) . '/db/database.php';

/**
 * Get all categories
 */
function getAllCategories($conn) {
    // Validate inputs
    if (!$conn || !is_object($conn)) {
        error_log("Invalid database connection in getAllCategories");
        return [];
    }

    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM items WHERE category_id = c.category_id) as item_count 
              FROM categories c 
              ORDER BY c.name";
    $result = $conn->query($query);
    
    if ($result === false) {
        error_log("Failed to execute query: " . $conn->error);
        return [];
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Add a new category
 */
function addCategory($conn, $name, $description) {
    // Validate inputs
    if (!$conn || !is_object($conn)) {
        error_log("Invalid database connection in addCategory");
        return false;
    }

    $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ss", $name, $description);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to execute insert: " . $stmt->error);
    }
    
    return $result;
}

/**
 * Update a category
 */
function updateCategory($conn, $categoryId, $name, $description, $icon = 'category') {
    // Validate inputs
    if (!$conn || !is_object($conn)) {
        error_log("Invalid database connection in updateCategory");
        return false;
    }

    // Additional validation
    if (!is_numeric($categoryId) || $categoryId <= 0) {
        error_log("Invalid category ID: $categoryId");
        return false;
    }

    // Check if category exists before updating
    $checkQuery = "SELECT * FROM categories WHERE category_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    
    if (!$checkStmt) {
        error_log("Failed to prepare check statement: " . $conn->error);
        return false;
    }
    
    $checkStmt->bind_param("i", $categoryId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        error_log("Category not found: ID $categoryId");
        return false;
    }

    $query = "UPDATE categories SET name = ?, description = ?, icon = ? WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("sssi", $name, $description, $icon, $categoryId);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to execute update: " . $stmt->error);
        error_log("Update details - ID: $categoryId, Name: $name, Description: $description, Icon: $icon");
    }
    
    return $result;
}

/**
 * Delete a category
 */
function deleteCategory($conn, $categoryId) {
    // Validate inputs
    if (!$conn || !is_object($conn)) {
        error_log("Invalid database connection in deleteCategory");
        return false;
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, delete all items in this category
        $deleteItemsQuery = "DELETE FROM items WHERE category_id = ?";
        $deleteItemsStmt = $conn->prepare($deleteItemsQuery);
        
        if (!$deleteItemsStmt) {
            error_log("Failed to prepare delete items statement: " . $conn->error);
            throw new Exception("Prepare statement failed");
        }
        
        $deleteItemsStmt->bind_param('i', $categoryId);
        $deleteItemsResult = $deleteItemsStmt->execute();
        
        if (!$deleteItemsResult) {
            error_log("Failed to delete items in category: " . $deleteItemsStmt->error);
            throw new Exception("Failed to delete items");
        }
        
        // Then delete the category
        $deleteCategoryQuery = "DELETE FROM categories WHERE category_id = ?";
        $deleteCategoryStmt = $conn->prepare($deleteCategoryQuery);
        
        if (!$deleteCategoryStmt) {
            error_log("Failed to prepare delete category statement: " . $conn->error);
            throw new Exception("Prepare statement failed");
        }
        
        $deleteCategoryStmt->bind_param('i', $categoryId);
        $deleteCategoryResult = $deleteCategoryStmt->execute();
        
        if (!$deleteCategoryResult) {
            error_log("Failed to delete category: " . $deleteCategoryStmt->error);
            throw new Exception("Failed to delete category");
        }
        
        // Commit transaction
        $conn->commit();
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get category by ID
 */
function getCategoryById($conn, $categoryId) {
    // Validate inputs
    if (!$conn || !is_object($conn)) {
        error_log("Invalid database connection in getCategoryById");
        return null;
    }

    $query = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        error_log("Failed to retrieve category: " . $stmt->error);
    }
    
    return $result;
}

/**
 * Get items in category
 */
function getItemsInCategory($conn, $categoryId) {
    // Validate inputs
    if (!$conn || !is_object($conn)) {
        error_log("Invalid database connection in getItemsInCategory");
        return [];
    }

    $query = "SELECT * FROM items WHERE category_id = ? ORDER BY name";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (!$result) {
        error_log("Failed to retrieve items: " . $stmt->error);
    }
    
    return $result;
}
