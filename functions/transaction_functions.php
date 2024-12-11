<?php
require_once '../db/database.php';

function getAllTransactions() {
    global $db;
    $query = "SELECT * FROM transactions";
    $result = $db->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function addTransaction($itemId, $userId, $type, $quantity, $notes) {
    global $db;
    $query = "INSERT INTO transactions (item_id, user_id, type, quantity, notes) VALUES (:item_id, :user_id, :type, :quantity, :notes)";
    $stmt = $db->prepare($query);
    return $stmt->execute([':item_id' => $itemId, ':user_id' => $userId, ':type' => $type, ':quantity' => $quantity, ':notes' => $notes]);
}
?>