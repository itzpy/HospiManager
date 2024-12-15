<?php
require_once dirname(__DIR__) . '/db/database.php';

/**
 * Get all inventory logs with user and item details
 */
function getAllInventoryLogs($limit = 100) {
    global $conn;
    $query = "SELECT l.*, u.first_name, u.last_name, i.name as item_name, i.unit 
              FROM inventory_logs l
              JOIN users u ON l.user_id = u.user_id
              JOIN items i ON l.item_id = i.item_id
              ORDER BY l.timestamp DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Add items to inventory (Superadmin only)
 */
function addInventoryItem($categoryId, $name, $description, $quantity, $unit) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Insert new item
        $query = "INSERT INTO items (category_id, name, description, quantity, unit) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issis", $categoryId, $name, $description, $quantity, $unit);
        $stmt->execute();
        
        $itemId = $conn->insert_id;
        
        // Log the addition
        logInventoryChange($itemId, $_SESSION['user_id'], 'add', $quantity, 0, $quantity, 'Initial stock addition');
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error adding inventory item: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove items from inventory (Both admin and superadmin)
 */
function removeFromInventory($itemId, $quantity, $notes) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Get current quantity
        $query = "SELECT quantity FROM items WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            throw new Exception("Item not found");
        }
        
        $currentQuantity = $result['quantity'];
        if ($currentQuantity < $quantity) {
            throw new Exception("Insufficient quantity in stock");
        }
        
        // Update quantity
        $newQuantity = $currentQuantity - $quantity;
        $query = "UPDATE items SET quantity = ? WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $newQuantity, $itemId);
        $stmt->execute();
        
        // Log the removal
        logInventoryChange($itemId, $_SESSION['user_id'], 'remove', -$quantity, $currentQuantity, $newQuantity, $notes);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error removing from inventory: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Update item quantity (Superadmin only)
 */
function updateItemQuantity($itemId, $newQuantity, $notes) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Get current quantity
        $query = "SELECT quantity FROM items WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            throw new Exception("Item not found");
        }
        
        $currentQuantity = $result['quantity'];
        $quantityChanged = $newQuantity - $currentQuantity;
        
        // Update quantity
        $query = "UPDATE items SET quantity = ? WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $newQuantity, $itemId);
        $stmt->execute();
        
        // Log the update
        logInventoryChange($itemId, $_SESSION['user_id'], 'update', $quantityChanged, $currentQuantity, $newQuantity, $notes);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating item quantity: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Log inventory changes
 */
function logInventoryChange($itemId, $userId, $actionType, $quantityChanged, $previousQuantity, $newQuantity, $notes) {
    global $conn;
    
    $query = "INSERT INTO inventory_logs (user_id, item_id, action_type, quantity_changed, previous_quantity, new_quantity, notes) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisiiss", $userId, $itemId, $actionType, $quantityChanged, $previousQuantity, $newQuantity, $notes);
    return $stmt->execute();
}

/**
 * Get inventory summary
 */
function getInventorySummary() {
    global $conn;
    
    $summary = [
        'total_items' => 0,
        'low_stock_items' => 0,
        'categories' => 0,
        'recent_activities' => []
    ];
    
    // Get total items and categories
    $query = "SELECT 
                (SELECT COUNT(*) FROM items) as total_items,
                (SELECT COUNT(*) FROM items WHERE quantity < 10) as low_stock_items,
                (SELECT COUNT(*) FROM categories) as categories";
    $result = $conn->query($query);
    $counts = $result->fetch_assoc();
    
    $summary['total_items'] = $counts['total_items'];
    $summary['low_stock_items'] = $counts['low_stock_items'];
    $summary['categories'] = $counts['categories'];
    
    // Get recent activities
    $summary['recent_activities'] = getAllInventoryLogs(5);
    
    return $summary;
}