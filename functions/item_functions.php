<?php
require_once dirname(__DIR__) . '/db/database.php';

/**
 * Get all items with their category information
 */
function getAllItems($conn) {
    $query = "SELECT i.*, c.name as category_name 
              FROM items i 
              JOIN categories c ON i.category_id = c.category_id 
              ORDER BY i.name";
    $result = $conn->query($query);
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get items with low stock (quantity < 10)
 */
function getLowStockItems($conn) {
    $query = "SELECT i.*, c.name as category_name 
              FROM items i 
              JOIN categories c ON i.category_id = c.category_id 
              WHERE i.quantity < 10 
              ORDER BY i.quantity ASC";
    $result = $conn->query($query);
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Add a new item (Superadmin only)
 */
function addItem($conn, $categoryId, $name, $description, $quantity, $unit) {
    try {
        // Validate inputs
        if (!$conn || !is_object($conn)) {
            error_log("Invalid database connection in addItem");
            return false;
        }

        $conn->begin_transaction();
        
        // Insert new item
        $query = "INSERT INTO items (category_id, name, description, quantity, unit, last_updated) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Failed to prepare addItem statement: " . $conn->error);
            $conn->rollback();
            return false;
        }
        
        $stmt->bind_param("issis", $categoryId, $name, $description, $quantity, $unit);
        
        if (!$stmt->execute()) {
            error_log("Failed to execute addItem: " . $stmt->error);
            throw new Exception("Failed to add item");
        }
        
        $itemId = $stmt->insert_id;
        
        // Log the activity
        $query = "INSERT INTO activity_log (user_id, action, item_id, quantity_changed, notes, timestamp) 
                  VALUES (?, 'add', ?, ?, 'New item added', NOW())";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Failed to prepare activity log statement: " . $conn->error);
            $conn->rollback();
            return false;
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $stmt->bind_param("iii", $userId, $itemId, $quantity);
        
        if (!$stmt->execute()) {
            error_log("Failed to log activity: " . $stmt->error);
            throw new Exception("Failed to log activity");
        }
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        error_log("Add Item Exception: " . $e->getMessage());
        $conn->rollback();
        return false;
    }
}

/**
 * Update an item (Superadmin only)
 */
function updateItem($conn, $itemId, $categoryId, $name, $description, $quantity, $unit) {
    try {
        $conn->begin_transaction();
        
        // Get current item details
        $query = "SELECT quantity, name, category_id, description, unit FROM items WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            throw new Exception("Item not found");
        }
        
        $currentQuantity = $result['quantity'];
        $currentName = $result['name'];
        $currentCategoryId = $result['category_id'];
        $currentDescription = $result['description'];
        $currentUnit = $result['unit'];
        
        // Determine changes
        $quantityChanged = $quantity - $currentQuantity;
        $nameChanged = $name !== $currentName;
        $categoryChanged = $categoryId !== $currentCategoryId;
        $descriptionChanged = $description !== $currentDescription;
        $unitChanged = $unit !== $currentUnit;
        
        // Update item
        $updateQuery = "UPDATE items SET category_id = ?, name = ?, description = ?, quantity = ?, unit = ?, last_updated = NOW() WHERE item_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("isssii", $categoryId, $name, $description, $quantity, $unit, $itemId);
        $updateStmt->execute();
        
        // Log changes if significant
        if ($quantityChanged !== 0 || $nameChanged || $categoryChanged || $descriptionChanged || $unitChanged) {
            $logQuery = "INSERT INTO activity_log (user_id, action, item_id, quantity_changed, notes, timestamp) 
                         VALUES (?, 'update', ?, ?, ?, NOW())";
            $logStmt = $conn->prepare($logQuery);
            $userId = $_SESSION['user_id'] ?? 0;
            
            // Compile change notes
            $changeNotes = [];
            if ($quantityChanged !== 0) $changeNotes[] = "Quantity changed by $quantityChanged";
            if ($nameChanged) $changeNotes[] = "Name updated";
            if ($categoryChanged) $changeNotes[] = "Category changed";
            if ($descriptionChanged) $changeNotes[] = "Description modified";
            if ($unitChanged) $changeNotes[] = "Unit updated";
            
            $notes = implode(', ', $changeNotes);
            
            $logStmt->bind_param("iiis", $userId, $itemId, $quantityChanged, $notes);
            $logStmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Update Item Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete an item (Superadmin only)
 */
function deleteItem($conn, $itemId) {
    // Open log file for detailed logging
    $logFile = dirname(__DIR__) . '/logs/delete_item_debug.log';
    
    try {
        // Log start of deletion attempt
        $logMessage = date('[Y-m-d H:i:s] ') . "Deletion Attempt - Item ID: $itemId\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Validate inputs
        if (!$conn || !is_object($conn)) {
            $errorMsg = "Invalid database connection in deleteItem";
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            return false;
        }
        
        $conn->begin_transaction();
        
        // Get current item details
        $query = "SELECT quantity, name FROM items WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            $errorMsg = "Item not found - Item ID: $itemId";
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            throw new Exception($errorMsg);
        }
        
        $currentQuantity = $result['quantity'];
        $itemName = $result['name'];
        
        // Log item details
        $logMessage = date('[Y-m-d H:i:s] ') . "Item Details - Name: $itemName, Quantity: $currentQuantity\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // First, delete related activity log entries
        $deleteLogQuery = "DELETE FROM activity_log WHERE item_id = ?";
        $deleteLogStmt = $conn->prepare($deleteLogQuery);
        $deleteLogStmt->bind_param("i", $itemId);
        $deleteLogSuccess = $deleteLogStmt->execute();
        
        if (!$deleteLogSuccess) {
            $errorMsg = "Failed to delete activity logs for item $itemId: " . $conn->error;
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            throw new Exception($errorMsg);
        }
        
        // Log activity log deletion
        $logMessage = date('[Y-m-d H:i:s] ') . "Activity Logs Deleted for Item ID: $itemId\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Delete the item
        $deleteItemQuery = "DELETE FROM items WHERE item_id = ?";
        $deleteItemStmt = $conn->prepare($deleteItemQuery);
        $deleteItemStmt->bind_param("i", $itemId);
        $deleteItemSuccess = $deleteItemStmt->execute();
        
        if (!$deleteItemSuccess) {
            $errorMsg = "Failed to delete item $itemId: " . $conn->error;
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            throw new Exception($errorMsg);
        }
        
        // Log item deletion
        $logMessage = date('[Y-m-d H:i:s] ') . "Item Deleted - Item ID: $itemId, Name: $itemName\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Log the deletion activity
        $logQuery = "INSERT INTO activity_log (user_id, action, item_id, quantity_changed, notes, timestamp) 
                     VALUES (?, 'delete', ?, ?, 'Item deleted', NOW())";
        $logStmt = $conn->prepare($logQuery);
        $userId = $_SESSION['user_id'] ?? 0;
        $quantityChanged = -$currentQuantity;
        
        $logStmt->bind_param("iis", $userId, $itemId, $quantityChanged);
        $logSuccess = $logStmt->execute();
        
        if (!$logSuccess) {
            $errorMsg = "Failed to log deletion of item $itemId: " . $conn->error;
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            throw new Exception($errorMsg);
        }
        
        // Log activity log insertion
        $logMessage = date('[Y-m-d H:i:s] ') . "Deletion Activity Logged - Item ID: $itemId, User ID: $userId\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        $conn->commit();
        
        // Log successful deletion
        $logMessage = date('[Y-m-d H:i:s] ') . "Deletion Successful - Item ID: $itemId\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        
        // Log exception details
        $errorMsg = "Delete Item Error: " . $e->getMessage();
        error_log($errorMsg);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
        
        return false;
    }
}

/**
 * Get item by ID
 */
function getItemById($conn, $itemId) {
    $query = "SELECT i.*, c.name as category_name 
              FROM items i 
              JOIN categories c ON i.category_id = c.category_id 
              WHERE i.item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Remove or add items from inventory (Both admin and superadmin)
 */
function removeFromInventory($conn, $itemId, $quantity, $notes) {
    try {
        // Validate inputs
        if (!$conn || !is_object($conn)) {
            error_log("Invalid database connection in removeFromInventory");
            return false;
        }

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
        $newQuantity = $currentQuantity + $quantity;
        
        // Validate quantity change
        if ($newQuantity < 0) {
            throw new Exception("Insufficient quantity in stock");
        }
        
        // Update quantity
        $query = "UPDATE items SET quantity = ?, last_updated = NOW() WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $newQuantity, $itemId);
        $stmt->execute();
        
        // Log the activity
        $query = "INSERT INTO activity_log (user_id, action, item_id, quantity_changed, notes, timestamp) 
                  VALUES (?, 'adjust', ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $userId = $_SESSION['user_id'] ?? 0;
        $stmt->bind_param("iiis", $userId, $itemId, $quantity, $notes);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        error_log("Remove From Inventory Exception: " . $e->getMessage());
        $conn->rollback();
        return false;
    }
}