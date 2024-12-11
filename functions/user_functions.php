<?php
require_once dirname(__DIR__) . '/db/database.php';

function getUserById($user_id) {
    global $conn; // Assuming you have a database connection variable
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserAnalytics($user_Id) {
    global $conn; // Assuming you have a database connection variable
    $query = "SELECT COUNT(*) as total_recipes FROM recipes WHERE recipe_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getAllUsers() {
    global $conn;
    $query = "SELECT id, first_name, last_name, email, role, created_at FROM users";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
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