<?php
require_once dirname(__DIR__) . '/db/database.php';

function getUserById($conn, $user_id) {
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserAnalytics($conn, $user_id) {
    $analytics = [
        'total_orders' => 0,
        'total_appointments' => 0
    ];
    
    // Get total orders
    $query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $analytics['total_orders'] = $result['total'] ?? 0;
    
    // Get total appointments
    $query = "SELECT COUNT(*) as total FROM appointments WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $analytics['total_appointments'] = $result['total'] ?? 0;
    
    return $analytics;
}

function getAllUsers($conn) {
    $query = "SELECT user_id, first_name, last_name, email, role FROM users";
    $result = $conn->query($query);
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addUser($conn, $firstName, $lastName, $email, $password, $role = 'admin') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);
    return $stmt->execute();
}

function deleteUser($conn, $user_id) {
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function updateUser($conn, $user_id, $firstName, $lastName, $email, $role) {
    $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $role, $user_id);
    return $stmt->execute();
}

function changePassword($conn, $user_id, $newPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $query = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $hashedPassword, $user_id);
    return $stmt->execute();
}

function updateLastLogin($conn, $user_id) {
    $query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function getRecipeList($conn, $userId) {
    // Fetch recipe list data here
}