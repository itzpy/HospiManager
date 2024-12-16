<?php
require_once dirname(__DIR__) . '/db/database.php';
require_once __DIR__ . '/user_functions.php';
require_once __DIR__ . '/category_functions.php';
require_once __DIR__ . '/inventory_functions.php';

function getDashboardStats($conn, $userRole = null) {
    $stats = [
        'total_users' => getTotalUsers($conn),
        'total_categories' => getTotalCategories($conn),
        'total_items' => getTotalItems($conn),
        'low_stock_items' => getLowStockItemsCount($conn)
    ];

    // Only include log-related stats for admin and superadmin
    if ($userRole === 'admin' || $userRole === 'superadmin') {
        $logQuery = "SELECT COUNT(*) as total_logs FROM inventory_logs";
        $logResult = $conn->query($logQuery);
        $stats['total_logs'] = $logResult ? $logResult->fetch_assoc()['total_logs'] : 0;
    } else {
        // Remove log-related stats for non-admin users
        unset($stats['total_logs']);
    }

    return $stats;
}

function getRecentActivities($conn, $userId = null, $limit = 5) {
    // Check if activity_log table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'activity_log'");
    if ($tableCheck->num_rows == 0) {
        return [];
    }

    // Prepare SQL query with optional user filtering
    $sql = "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, i.name as item_name 
            FROM activity_log al 
            LEFT JOIN users u ON al.user_id = u.user_id 
            LEFT JOIN items i ON al.item_id = i.item_id";
    
    // Add user filter if userId is provided
    if ($userId !== null) {
        $sql .= " WHERE al.user_id = ?";
    }
    
    $sql .= " ORDER BY al.timestamp DESC LIMIT ?";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    
    if ($userId !== null) {
        $stmt->bind_param("ii", $userId, $limit);
    } else {
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function getRecentActivity($conn, $limit = 10) {
    $activities = [];

    try {
        $query = "SELECT 
                    il.*, 
                    i.name as item_name,
                    u.first_name,
                    u.last_name,
                    i.unit
                FROM inventory_logs il
                JOIN items i ON il.item_id = i.item_id
                JOIN users u ON il.user_id = u.user_id
                ORDER BY il.timestamp DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    } catch (Exception $e) {
        error_log("Error in getRecentActivity: " . $e->getMessage());
    }

    return $activities;
}

function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function getTotalCategories($conn) {
    $sql = "SELECT COUNT(*) as total FROM categories";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function getTotalItems($conn) {
    $sql = "SELECT COUNT(*) as total FROM items";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function getLowStockItemsCount($conn) {
    $sql = "SELECT COUNT(*) as total FROM items WHERE quantity <= minimum_quantity";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

// Inventory-focused dashboard functions
function getAdminTotalItems($conn) {
    $sql = "SELECT COUNT(*) as total FROM items";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function getAdminLowStockItems($conn) {
    $sql = "SELECT COUNT(*) as total 
            FROM items 
            WHERE quantity <= minimum_quantity";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function getAdminTotalCategories($conn) {
    $sql = "SELECT COUNT(*) as total FROM categories";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function getAdminRecentInventoryActivities($conn) {
    $sql = "SELECT 
                il.*, 
                i.name as item_name,
                u.first_name,
                u.last_name,
                i.unit
            FROM inventory_logs il
            JOIN items i ON il.item_id = i.item_id
            JOIN users u ON il.user_id = u.user_id
            ORDER BY il.timestamp DESC
            LIMIT 5";
    
    $result = $conn->query($sql);
    
    $activities = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
    
    return $activities;
}
