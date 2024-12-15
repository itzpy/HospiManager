<?php
session_start();

// Define the base path
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Include required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/functions/auth_functions.php';
require_once BASE_PATH . '/functions/user_functions.php';
require_once BASE_PATH . '/functions/dashboard_functions.php';

// Check if user is logged in and is a superadmin
if (!isLoggedIn() || !isSuperAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$fullName = $_SESSION['full_name'] ?? 'User';

// Get search and filter parameters
$searchQuery = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

// Construct the base query
$query = "SELECT user_id, first_name, last_name, email, role, last_login FROM users WHERE 1=1";
$params = [];
$types = '';

// Apply search filter
if (!empty($searchQuery)) {
    $query .= " AND (
        first_name LIKE ? OR 
        last_name LIKE ? OR 
        email LIKE ? OR 
        CONCAT(first_name, ' ', last_name) LIKE ?
    )";
    $searchParam = "%{$searchQuery}%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= 'ssss';
}

// Apply role filter
if (!empty($roleFilter)) {
    $query .= " AND role = ?";
    $params[] = &$roleFilter;
    $types .= 's';
}

// Prepare and execute the query
$stmt = $conn->prepare($query);

// Bind parameters if any
if (!empty($params)) {
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
}

$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Hospital Management</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/users.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        .main-content {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px;
            backdrop-filter: blur(10px);
        }

        .users-container {
            padding: 20px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 20px;
        }

        .content-header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #333;
        }

        .add-btn {
            background-color: #9575CD;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .add-btn:hover {
            background-color: #7E57C2;
        }

        .filters-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 20px;
        }

        .search-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        .search-filter input {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-filter input:focus {
            outline: none;
        }

        .search-icon {
            color: #666;
        }

        .role-filter {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        .role-filter select {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #9575CD;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .users-table tr:hover {
            background-color: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
        <nav class="top-nav">
            <div class="nav-brand">
                <span class="material-icons">local_hospital</span>
                <span class="brand-name">Hospi Manager</span>
            </div>
            <ul class="nav-menu">
                <li>
                    <a href="dashboard.php">
                        <span class="material-icons">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php">
                        <span class="material-icons">inventory</span>
                        <span>Inventory</span>
                    </a>
                </li>
                <?php if ($userRole === 'superadmin'): ?>
                <li class="active">
                    <a href="users.php">
                        <span class="material-icons">people</span>
                        <span>Users</span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="settings.php">
                        <span class="material-icons">settings</span>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            <div class="nav-profile">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($fullName) ?></span>
                    <span class="user-role"><?= ucfirst(htmlspecialchars($userRole)) ?></span>
                </div>
                <a href="../../actions/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    <span class="material-icons">logout</span>
                    <span>Logout</span>
                </a>
            </div>
        </nav>

        <main class="main-content">
            <div class="users-container">
                <div class="content-header">
                    <h1>User Management</h1>
                    <button class="add-btn" onclick="openModal('addUserModal')">
                        <span class="material-icons">add</span>
                        Add User
                    </button>
                </div>

                <div class="filters-section">
                    <form id="userFilterForm" method="GET" action="">
                        <div class="search-filter">
                            <input 
                                type="text" 
                                id="searchInput" 
                                name="search" 
                                placeholder="Search users..." 
                                value="<?= htmlspecialchars($searchQuery) ?>"
                            >
                            <span class="material-icons search-icon">search</span>
                        </div>
                        <div class="role-filter">
                            <select 
                                id="roleFilter" 
                                name="role" 
                                onchange="this.form.submit()"
                            >
                                <option value="">All Roles</option>
                                <option value="superadmin" <?= $roleFilter === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="staff" <?= $roleFilter === 'staff' ? 'selected' : '' ?>>Staff</option>
                            </select>
                        </div>
                        <?php if (!empty($searchQuery) || !empty($roleFilter)): ?>
                        <div class="clear-filter">
                            <a href="users.php" class="clear-btn">Clear Filters</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="search-results">
                    <?php if (empty($users)): ?>
                        <p class="no-results">No users found matching your search criteria.</p>
                    <?php else: ?>
                        <p class="results-count"><?= count($users) ?> user(s) found</p>
                    <?php endif; ?>
                </div>

                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): 
                            // Combine first and last name, handle potential null values
                            $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            $fullName = $fullName ?: 'Unnamed User';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($fullName) ?></td>
                            <td><?= htmlspecialchars($user['email'] ?? 'No Email') ?></td>
                            <td>
                                <span class="role-badge <?= strtolower($user['role'] ?? 'guest') ?>">
                                    <?= htmlspecialchars($user['role'] ?? 'Guest') ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $lastLogin = $user['last_login'] ?? null;
                                if ($lastLogin) {
                                    $loginTime = new DateTime($lastLogin);
                                    echo htmlspecialchars($loginTime->format('Y-m-d H:i:s'));
                                } else {
                                    echo 'Never Logged In';
                                }
                                ?>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn" onclick="editUser(<?= $user['user_id'] ?? 0 ?>)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <?php if ($userRole === 'superadmin'): ?>
                                <button class="action-btn delete-btn" onclick="deleteUser(<?= $user['user_id'] ?? 0 ?>)">
                                    <span class="material-icons">delete</span>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <form id="addUserForm" action="../../actions/add_user.php" method="POST">
                <div class="form-group">
                    <label for="firstName">First Name*</label>
                    <input type="text" id="firstName" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name*</label>
                    <input type="text" id="lastName" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                <div class="form-group">
                    <label for="role">Role*</label>
                    <select id="role" name="role" required>
                        <option value="">Select a role</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Add User</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <form id="editUserForm" action="../../actions/edit_user.php" method="POST">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="form-group">
                    <label for="editFirstName">First Name*</label>
                    <input type="text" id="editFirstName" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="editLastName">Last Name*</label>
                    <input type="text" id="editLastName" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email*</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editPassword">New Password</label>
                    <input type="password" id="editPassword" name="password" minlength="6">
                    <small>Leave blank to keep current password</small>
                </div>
                <div class="form-group">
                    <label for="editRole">Role*</label>
                    <select id="editRole" name="role" required>
                        <option value="">Select a role</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update User</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchIcon = document.querySelector('.search-icon');
            const filterForm = document.getElementById('userFilterForm');

            // Handle search on icon click
            if (searchIcon) {
                searchIcon.addEventListener('click', function() {
                    filterForm.submit();
                });
            }

            // Handle search on Enter key press
            if (searchInput) {
                searchInput.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        filterForm.submit();
                    }
                });
            }

            // Optional: Debounce search to improve performance
            let searchTimeout;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (this.value.length > 2 || this.value.length === 0) {
                            filterForm.submit();
                        }
                    }, 500);
                });
            }
        });

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Edit user
        function editUser(userId) {
            fetch(`../../actions/get_user.php?id=${userId}`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('editUserId').value = user.user_id;
                    document.getElementById('editFirstName').value = user.first_name;
                    document.getElementById('editLastName').value = user.last_name;
                    document.getElementById('editEmail').value = user.email;
                    document.getElementById('editRole').value = user.role;
                    document.getElementById('editPassword').value = '';
                    openModal('editUserModal');
                })
                .catch(error => {
                    showNotification('Error loading user data', 'error');
                });
        }

        // Delete user
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch(`../../actions/delete_user.php?id=${userId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        location.reload();
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error deleting user', 'error');
                });
            }
        }

        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Add User Form
            const addUserForm = document.getElementById('addUserForm');
            if (addUserForm) {
                addUserForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message);
                            closeModal('addUserModal');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('An error occurred', 'error');
                    });
                });
            }

            // Edit User Form
            const editUserForm = document.getElementById('editUserForm');
            if (editUserForm) {
                editUserForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message);
                            closeModal('editUserModal');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('An error occurred', 'error');
                    });
                });
            }
        });
    </script>
</body>
</html>
