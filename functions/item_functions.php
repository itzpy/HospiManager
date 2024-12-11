<?php
require_once '../db/database.php';

function getAllItems() {
    global $db;
    $query = "SELECT * FROM items";
    $result = $db->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function addItem($name, $category, $quantity) {
    global $db;
    if (isSuperAdmin()) {
        $query = "INSERT INTO items (name, category, quantity) VALUES (:name, :category, :quantity)";
        $stmt = $db->prepare($query);
        return $stmt->execute([':name' => $name, ':category' => $category, ':quantity' => $quantity]);
    }
    return false;
}

function updateItem($id, $name, $category, $quantity) {
    global $db;
    if (isSuperAdmin()) {
        $query = "UPDATE items SET name = :name, category = :category, quantity = :quantity WHERE id = :id";
        $stmt = $db->prepare($query);
        return $stmt->execute([':name' => $name, ':category' => $category, ':quantity' => $quantity, ':id' => $id]);
    }
    return false;
}

function deleteItem($id) {
    global $db;
    if (isSuperAdmin()) {
        $query = "DELETE FROM items WHERE id = :id";
        $stmt = $db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    return false;
}

function deductItem($id, $quantity) {
    global $db;
    if (isAdmin() || isSuperAdmin()) {
        $query = "UPDATE items SET quantity = quantity - :quantity WHERE id = :id";
        $stmt = $db->prepare($query);
        return $stmt->execute([':quantity' => $quantity, ':id' => $id]);
    }
    return false;
}
?>