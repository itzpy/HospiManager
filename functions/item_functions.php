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
    // Ensure logging directory exists
    $logDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/delete_item_debug.log';

    // Log the start of deletion attempt
    $logMessage = date('[Y-m-d H:i:s] ') . "Deletion Attempt - Item ID: $itemId\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Begin transaction for atomic operation
    $conn->begin_transaction();
    
    try {
        // First, check if the item exists and get its details
        $checkQuery = "SELECT * FROM items WHERE item_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $itemId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            $errorMsg = "Item not found - Item ID: $itemId";
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            return false;
        }

        // Fetch item details for logging
        $itemDetails = $checkResult->fetch_assoc();
        $logMessage = date('[Y-m-d H:i:s] ') . "Item Details - Name: " . $itemDetails['name'] . ", Quantity: " . $itemDetails['quantity'] . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Delete related records in activity_log
        $deleteActivityLogQuery = "DELETE FROM activity_log WHERE item_id = ?";
        $deleteActivityLogStmt = $conn->prepare($deleteActivityLogQuery);
        $deleteActivityLogStmt->bind_param("i", $itemId);
        $deleteActivityLogResult = $deleteActivityLogStmt->execute();
        
        if ($deleteActivityLogResult === false) {
            $errorMsg = "Failed to delete activity logs - Item ID: $itemId, Error: " . $conn->error;
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            
            // Log the specific activity log deletion failure
            $logMessage = date('[Y-m-d H:i:s] ') . "Activity Log Deletion Failed for Item ID: $itemId\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        } else {
            $logMessage = date('[Y-m-d H:i:s] ') . "Activity Logs Deleted for Item ID: $itemId\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }

        // Check if inventory_transactions table exists before attempting to delete
        $checkTableQuery = "SHOW TABLES LIKE 'inventory_transactions'";
        $tableCheckResult = $conn->query($checkTableQuery);
        
        if ($tableCheckResult->num_rows > 0) {
            // Delete related records in inventory_transactions
            $deleteTransactionsQuery = "DELETE FROM inventory_transactions WHERE item_id = ?";
            $deleteTransactionsStmt = $conn->prepare($deleteTransactionsQuery);
            $deleteTransactionsStmt->bind_param("i", $itemId);
            $deleteTransactionsResult = $deleteTransactionsStmt->execute();
            
            if ($deleteTransactionsResult === false) {
                $errorMsg = "Failed to delete inventory transactions - Item ID: $itemId, Error: " . $conn->error;
                error_log($errorMsg);
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            }
        } else {
            // Log that the table doesn't exist
            $logMessage = date('[Y-m-d H:i:s] ') . "Inventory Transactions Table Does Not Exist\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
        
        // Delete the item
        $deleteItemQuery = "DELETE FROM items WHERE item_id = ?";
        $deleteItemStmt = $conn->prepare($deleteItemQuery);
        $deleteItemStmt->bind_param("i", $itemId);
        $deleteResult = $deleteItemStmt->execute();
        
        if ($deleteResult === false) {
            $errorMsg = "Failed to delete item - Item ID: $itemId, Error: " . $conn->error;
            error_log($errorMsg);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
            throw new Exception("Failed to delete item");
        }

        // Log item deletion
        $logMessage = date('[Y-m-d H:i:s] ') . "Item Deleted - Item ID: $itemId, Name: " . $itemDetails['name'] . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Commit the transaction
        $conn->commit();
        
        return true;
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Log the error with more context
        $errorMsg = "Item Deletion Error for Item ID $itemId: " . $e->getMessage();
        error_log($errorMsg);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Delete Item Error: " . $e->getMessage() . "\n", FILE_APPEND);
        
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