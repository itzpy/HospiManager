<?php
require_once dirname(__DIR__) . '/db/database.php';

/**
 * Get all inventory items with their category names
 */
function getAllItems($conn) {
    $query = "SELECT i.*, c.name as category_name 
              FROM items i 
              LEFT JOIN categories c ON i.category_id = c.category_id 
              ORDER BY i.name";
    $result = $conn->query($query);
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get item by ID
 */
function getItemById($conn, $itemId) {
    $query = "SELECT i.*, c.name as category_name 
              FROM items i 
              LEFT JOIN categories c ON i.category_id = c.category_id 
              WHERE i.item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Add new item
 */
function addItem($conn, $name, $categoryId, $quantity, $unit, $description) {
    $query = "INSERT INTO items (name, category_id, quantity, unit, description, last_updated) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisss", $name, $categoryId, $quantity, $unit, $description);
    
    if ($stmt->execute()) {
        $itemId = $stmt->insert_id;
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], 'add', $itemId, $quantity, "New item added");
        return true;
    }
    return false;
}

/**
 * Update item details
 */
function updateItem($conn, $itemId, $name, $categoryId, $unit, $description) {
    $query = "UPDATE items 
              SET name = ?, category_id = ?, unit = ?, description = ?, last_updated = NOW() 
              WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sissi", $name, $categoryId, $unit, $description, $itemId);
    
    if ($stmt->execute()) {
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], 'update', $itemId, 0, "Item details updated");
        return true;
    }
    return false;
}

/**
 * Adjust item stock
 */
function adjustStock($conn, $itemId, $quantity, $adjustType, $notes) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get current quantity
        $query = "SELECT quantity FROM items WHERE item_id = ? FOR UPDATE";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if (!$item) {
            throw new Exception("Item not found (ID: $itemId)");
        }
        
        // Calculate new quantity
        $newQuantity = $adjustType === 'add' 
            ? $item['quantity'] + $quantity 
            : $item['quantity'] - $quantity;
        
        // Prevent negative quantity
        if ($newQuantity < 0) {
            throw new Exception("Cannot remove more items than available (Current: {$item['quantity']}, Requested: $quantity)");
        }
        
        // Update quantity
        $query = "UPDATE items SET quantity = ?, last_updated = NOW() WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $newQuantity, $itemId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update quantity for item ID: $itemId");
        }
        
        // Log the activity
        $action = $adjustType === 'add' ? 'stock_in' : 'stock_out';
        $logResult = logActivity($conn, $_SESSION['user_id'], $action, $itemId, $quantity, $notes);
        
        if (!$logResult) {
            throw new Exception("Failed to log activity for item ID: $itemId");
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        // Log the error for debugging
        error_log("Stock Adjustment Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Delete item
 */
function deleteItem($conn, $itemId) {
    // Check if user is superadmin
    if ($_SESSION['role'] !== 'superadmin') {
        return false;
    }
    
    $query = "DELETE FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute()) {
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], 'delete', $itemId, 0, "Item deleted");
        return true;
    }
    return false;
}

/**
 * Log inventory activity
 */
function logActivity($conn, $userId, $action, $itemId, $quantity, $notes) {
    // Validate item exists before logging
    $query = "SELECT 1 FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If item doesn't exist, skip logging
    if ($result->num_rows === 0) {
        return false;
    }
    
    // Log the activity
    $query = "INSERT INTO activity_log (user_id, item_id, action, quantity_changed, notes, timestamp) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisis", $userId, $itemId, $action, $quantity, $notes);
    return $stmt->execute();
}

/**
 * Get low stock items
 * @param mysqli $conn Database connection
 * @param int $lowStockThreshold Threshold for low stock (default 10)
 * @return array List of low stock items
 */
function getLowStockItems($conn, $lowStockThreshold = 10) {
    $query = "SELECT i.*, c.name as category_name 
              FROM items i 
              LEFT JOIN categories c ON i.category_id = c.category_id 
              WHERE i.quantity <= ?
              ORDER BY i.quantity ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $lowStockThreshold);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    // Return empty array if no results
    if ($result->num_rows === 0) {
        return [];
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
