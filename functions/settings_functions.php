<?php
require_once dirname(__DIR__) . '/db/database.php';

/**
 * Get user settings
 */
function getUserSettings($conn, $userId) {
    $sql = "SELECT * FROM user_settings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    
    // Return default settings if none exist
    return [
        'notifications_enabled' => 1,
        'low_stock_threshold' => 10,
        'theme' => 'light',
        'items_per_page' => 10
    ];
}

/**
 * Update user settings
 */
function updateUserSettings($conn, $userId, $settings) {
    // Validate settings
    $validSettings = array_intersect_key($settings, [
        'notifications_enabled' => true,
        'low_stock_threshold' => true,
        'theme' => true,
        'items_per_page' => true
    ]);
    
    if (empty($validSettings)) {
        return false;
    }
    
    // Check if settings exist for user
    $sql = "SELECT user_id FROM user_settings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    
    if ($exists) {
        // Update existing settings
        $setParts = [];
        $types = "";
        $values = [];
        
        foreach ($validSettings as $key => $value) {
            $setParts[] = "$key = ?";
            $types .= is_int($value) ? "i" : "s";
            $values[] = $value;
        }
        
        $values[] = $userId;
        $types .= "i";
        
        $sql = "UPDATE user_settings SET " . implode(", ", $setParts) . " WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    } else {
        // Insert new settings
        $columns = array_keys($validSettings);
        $placeholders = array_fill(0, count($validSettings), "?");
        
        $sql = "INSERT INTO user_settings (user_id, " . implode(", ", $columns) . ") 
                VALUES (?, " . implode(", ", $placeholders) . ")";
        
        $types = "i" . str_repeat("i", count($validSettings));
        $values = array_merge([$userId], array_values($validSettings));
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }
}

/**
 * Get system settings
 */
function getSystemSettings($conn) {
    $sql = "SELECT * FROM system_settings";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    
    // Return default system settings if none exist
    return [
        'site_name' => 'Hospital Management System',
        'items_per_page' => 10,
        'low_stock_threshold' => 10,
        'enable_notifications' => 1,
        'maintenance_mode' => 0
    ];
}

/**
 * Update system settings
 */
function updateSystemSettings($conn, $settings) {
    // Validate settings
    $validSettings = array_intersect_key($settings, [
        'site_name' => true,
        'items_per_page' => true,
        'low_stock_threshold' => true,
        'enable_notifications' => true,
        'maintenance_mode' => true
    ]);
    
    if (empty($validSettings)) {
        return false;
    }
    
    // Check if any settings exist
    $sql = "SELECT COUNT(*) as count FROM system_settings";
    $result = $conn->query($sql);
    $exists = $result->fetch_assoc()['count'] > 0;
    
    if ($exists) {
        // Update existing settings
        $setParts = [];
        $types = "";
        $values = [];
        
        foreach ($validSettings as $key => $value) {
            $setParts[] = "$key = ?";
            $types .= is_int($value) ? "i" : "s";
            $values[] = $value;
        }
        
        $sql = "UPDATE system_settings SET " . implode(", ", $setParts);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    } else {
        // Insert new settings
        $columns = array_keys($validSettings);
        $placeholders = array_fill(0, count($validSettings), "?");
        
        $sql = "INSERT INTO system_settings (" . implode(", ", $columns) . ") 
                VALUES (" . implode(", ", $placeholders) . ")";
        
        $types = str_repeat(is_int(reset($validSettings)) ? "i" : "s", count($validSettings));
        $values = array_values($validSettings);
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }
}
