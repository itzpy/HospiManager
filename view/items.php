<?php
session_start();
require_once '../functions/item_functions.php';

$items = getAllItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Item Management</h1>
        <nav>
            <a href="./admin/dashboard.php" class="btn">Back to Dashboard</a>
        </nav>
    </header>
    <main>
        <section class="item-management">
            <h2>Item List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id']) ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>
                                <?php if (isSuperAdmin()): ?>
                                    <button class="btn edit">Edit</button>
                                    <button class="btn delete">Delete</button>
                                <?php endif; ?>
                                <button class="btn deduct">Deduct</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (isSuperAdmin()): ?>
                <h2>Add New Item</h2>
                <form id="addItemForm" method="POST" action="../actions/item_actions.php">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" name="itemName" required>
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>
                    <button type="submit" class="btn">Add Item</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="editCloseButton">&times;</span>
            <h2>Edit Item</h2>
            <form id="editItemForm">
                <label for="editItemName">Item Name</label>
                <input type="text" id="editItemName" name="editItemName" required>
                <label for="editCategory">Category</label>
                <input type="text" id="editCategory" name="editCategory">
                <label for="editQuantity">Quantity</label>
                <input type="number" id="editQuantity" name="editQuantity" required>
                <button type="submit" class="btn">Update Item</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/items.js"></script>
</body>
</html>