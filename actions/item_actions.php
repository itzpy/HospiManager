<?php
require_once '../functions/item_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['itemName'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];

    if (addItem($name, $category, $quantity)) {
        header('Location: ../view/items.php');
    } else {
        echo "Error adding item.";
    }
}
?>