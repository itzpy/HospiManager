<?php
// Log user activities
function logActivity($conn, $adminId, $userId, $action, $recordId = 0, $description = '') {
    try {
        $stmt = $conn->prepare("INSERT INTO activity_log (admin_id, user_id, action, record_id, description) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        
        $stmt->bind_param("iisis", $adminId, $userId, $action, $recordId, $description);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute statement failed: ' . $stmt->error);
        }
        
        $stmt->close();
        return true;
    } catch (Exception $e) {
        // Log error or handle as needed
        error_log('Activity logging failed: ' . $e->getMessage());
        return false;
    }
}
