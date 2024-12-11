<?php
session_start();
define('BASE_DIR', dirname(__DIR__, 2)); // This sets the base directory to RECIPE_SHARING
require_once '../db/database.php';
require_once '../functions/user_functions.php';
// require_once '../actions/user_actions.php';

// Check if user is logged in
// if (!isLoggedIn()) {
//     header('Location: login.php');
//     exit();
// }

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
    <link rel="stylesheet" href="../assets/css/d_r.css">
</head>
<body>
    <div class="container">
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
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_id']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= $user['role'] == 1 ? 'Super Admin' : 'Regular Admin' ?></td>
                                <td><?= htmlspecialchars($user['created_at']) ?></td>
                                <td>
                                    <button class="btn view">View More</button>
                                    <button class="btn update" data-user-id="<?= $user['user_id'] ?>">Update</button>
                                    <button class="btn delete" data-user-id="<?= $user['user_id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Badu's Recipes Admin</p>
        </footer>
    </div>

    <!-- <div id="editUser Modal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="editCloseButton">&times;</span>
            <h2>Edit User</h2>
            <form id="editUser Form">
                <label for="editUser Name">Name</label>
                <input type="text" id="editUser Name" name="editUser Name" required>

                <label for="editUser Email">Email</label>
                <input type="email" id="editUser Email" name="editUser Email" required>

                <button type="submit">Update User</button>
            </form>
        </div>
    </div> -->

    <!-- <script src="../assets/js/users.js"></script> Link to external JS file -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
 
        // Get all delete buttons
            const deleteButtons = document.querySelectorAll(".delete");
            deleteButtons.forEach((button) => {
                button.addEventListener("click", function () {
                console.log("Delete button clicked");
                const row = this.closest("tr"); // Get the parent row of the clicked button
                const userId = this.getAttribute("data-user-id");
                // Get the user ID from the button

                // Confirm deletion
                const confirmDeletion = confirm(
                    `Are you sure you want to delete this user?`
                );
                if (confirmDeletion) {
                    // Send DELETE request to the server
                    console.log("Sending DELETE request for user ID:", userId);
                    fetch("../actions/user_actions.php", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `id=${userId}`,
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                        alert(data.message);
                        row.remove(); // Remove the row from the table
                        } else {
                        alert(data.message);
                        }
                    })
                    .catch((error) => {
                        console.log("Error:", error);
                        alert("An error occurred while trying to delete the user.");
                    });
                }
                });
            });

        });
    </script>

</body>
</html>