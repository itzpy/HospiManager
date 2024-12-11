<?php
require_once '../functions/transaction_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['itemId'];
    $userId = $_POST['userId'];
    $type = $_POST['type'];
    $quantity = $_POST['quantity'];
    $notes = $_POST['notes'];

    if (addTransaction($itemId, $userId, $type, $quantity, $notes)) {
        header('Location: ../view/transactions.php');
    } else {
        echo "Error adding transaction.";
    }
}
?>