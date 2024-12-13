<?php
session_start();
define('BASE_DIR', dirname(__DIR__, 2)); // This sets the base directory to RECIPE_SHARING
require_once BASE_DIR . '/db/database.php';
require_once BASE_DIR . '/functions/auth_functions.php';
require_once BASE_DIR . '/functions/user_functions.php';
require_once BASE_DIR . '/functions/item_functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Check if role is set in session
if (!isset($_SESSION['user_role'])) {
    die('Role is not set in the session.');
}

$userRole = $_SESSION['user_role'];

// Fetch analytics data
$totalUsers = 0;
$recentUsers = [];

if ($userRole == 'superadmin') { // Super Admin
    // Fetch total users
    $totalUsers = count(getAllUsers());
    $recentUsers = getAllUsers(); // Fetch all users
}

// Fetch categories and items
$categories = getAllCategories();
$items = getAllItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>        
         <a href="../../index.php" class="btn">Home</a>
         <a href="../view/change_password.php" class="btn">Change Password</a>
        </nav>
    </header>

    <main>
        <!-- Analytics Section -->
        <section class="analytics">
            <h2>Analytics</h2>
            <?php if ($userRole == 'superadmin'): // Super Admin ?>
                <p>Total Users: <span id="total-users"><?= $totalUsers ?></span></p>
            <?php endif; ?>
        </section>

        <?php if ($userRole == 'superadmin'): // Super Admin ?>
            <!-- Category Management Section -->
            <section class="category-management">
                <h2>Category Management</h2>
                <form id="addCategoryForm" method="POST" action="../../actions/add_category.php">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" name="categoryName" required>
                    <button type="submit" class="btn">Add Category</button>
                </form>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['id']) ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td>
                                    <button class="btn edit" data-category-id="<?= $category['id'] ?>">Edit</button>
                                    <button class="btn delete" data-category-id="<?= $category['id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- Item Management Section -->
            <section class="item-management">
                <h2>Item Management</h2>
                <form id="addItemForm" method="POST" action="../../actions/add_item.php">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" name="itemName" required>
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>
                    <button type="submit" class="btn">Add Item</button>
                </form>
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
                                    <button class="btn edit" data-item-id="<?= $item['id'] ?>">Edit</button>
                                    <button class="btn delete" data-item-id="<?= $item['id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if ($userRole == 'admin'): // Regular Admin ?>
            <!-- Stock Management Section -->
            <section class="stock-management">
                <h2>Stock Management</h2>
                <form id="deductItemForm" method="POST" action="../../actions/deduct_item.php">
                    <label for="item">Item</label>
                    <select id="item" name="item" required>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= $item['quantity'] ?> available)</option>
                        <?php endforeach; ?>
                    </select>
                    <label for="quantity">Quantity to Deduct</label>
                    <input type="number" id="quantity" name="quantity" required>
                    <button type="submit" class="btn">Deduct Item</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Hospital Management. All Rights Reserved.</p>
    </footer>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>