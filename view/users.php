<?php
session_start();
define('BASE_DIR', dirname(__DIR__, 2)); // This sets the base directory to RECIPE_SHARING
require_once '../db/database.php';
require_once '../functions/user_functions.php';

// Check if role is set in session
if (!isset($_SESSION['user_role'])) {
    die('Role is not set in the session.');
}

$userRole = $_SESSION['user_role'];

// Fetch all users for display
$users = getAllUsers(); // Fetch all users
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header>
        <h1>User Management</h1>
        <nav>
            <a href="./admin/dashboard.php" class="btn">Back to Dashboard</a>
        </nav>
    </header>
    <main>
        <section class="user-management">
            <h2>User List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td>
                                <button class="btn delete" data-user-id="<?= $user['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
    <script src="../assets/js/users.js"></script>
</body>
</html>