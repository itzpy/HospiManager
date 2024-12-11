<?php
session_start();
define('BASE_DIR', dirname(__DIR__, 2)); // This sets the base directory to RECIPE_SHARING
require_once BASE_DIR . '/db/database.php';
require_once BASE_DIR . '/functions/auth_functions.php';
require_once BASE_DIR . '/functions/user_functions.php';

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
            <!-- User Management Section -->
            <section class="user-management">
                <h2>User Management</h2>
                <p><a href="../users.php" class="btn">Manage Users</a></p>

                <h2>Add New Admin</h2>
                <form id="addAdminForm" method="POST" action="../../actions/add_admin.php">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" required>
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" required>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                    <button type="submit" class="btn">Add Admin</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Hospital Management. All Rights Reserved.</p>
    </footer>
</body>
</html>