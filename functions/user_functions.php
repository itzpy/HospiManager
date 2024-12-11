<?php
require_once '../db/database.php';

function getUserById($user_id) {
    global $db; // Assuming you have a database connection variable
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserAnalytics($user_Id) {
    global $db; // Assuming you have a database connection variable
    $query = "SELECT COUNT(*) as total_recipes FROM recipes WHERE recipe_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getAllUsers() {
    global $db;
    $query = "SELECT id, first_name, last_name, email, role, created_at FROM users";
    $result = $db->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function addUser($firstName, $lastName, $email, $password, $role = 'admin') {
    global $db;
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $query = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (:first_name, :last_name, :email, :password, :role)";
    $stmt = $db->prepare($query);
    return $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':role' => $role
    ]);
}

function deleteUser($id) {
    global $db;
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    return $stmt->execute([':id' => $id]);
}

function getPendingUserApprovals() {
    global $db;
    // Implement the logic to fetch pending user approvals
    $query = "SELECT COUNT(*) as pending_count FROM users WHERE approval_status = 'pending'";
    $result = $db->query($query);
    $data = $result->fetch(PDO::FETCH_ASSOC);
    return $data['pending_count'];
}

// function getUserList($page = 1) {
//     global $conn;
//     // Fetch user list data here
// }

function getRecipeList($userId) {
    global $db;
    // Fetch recipe list data here
}