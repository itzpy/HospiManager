<?php
session_start();

// Define the base path
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Include required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/functions/auth_functions.php';
require_once BASE_PATH . '/functions/dashboard_functions.php';
require_once BASE_PATH . '/functions/category_functions.php';
require_once BASE_PATH . '/functions/inventory_functions.php';
require_once BASE_PATH . '/functions/user_functions.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$fullName = $_SESSION['full_name'] ?? 'User';

// Get dashboard statistics
$stats = getDashboardStats($conn);

// Get recent activities
$recentActivities = getRecentActivities($conn);
$recentActivities = array_map(function($activity) {
    return [
        'title' => $activity['user_name'] . ' ' . $activity['action'] . ' ' . $activity['item_name'],
        'description' => date('M j, g:i a', strtotime($activity['timestamp'])),
        'icon' => match($activity['action']) {
            'add' => 'add_circle',
            'remove' => 'remove_circle',
            'update' => 'update',
            default => 'info'
        },
        'color' => match($activity['action']) {
            'add' => '#4CAF50',
            'remove' => '#F44336',
            'update' => '#FF9800',
            default => '#2196F3'
        }
    ];
}, $recentActivities ?? []);

// Ensure arrays have default values if empty
$recentActivities = $recentActivities ?: [
    [
        'title' => 'No Recent Activities',
        'description' => 'No activities to display',
        'icon' => 'info',
        'color' => '#9E9E9E'
    ]
];

// Get categories
$categories = getAllCategories($conn);

// Get quick actions
$quickActions = [
    [
        'title' => 'Add Item',
        'description' => 'Add a new item to the inventory',
        'icon' => 'add_box',
        'color' => '#4CAF50',
        'onclick' => 'openModal("addItemModal")'
    ],
    [
        'title' => 'Add Category',
        'description' => 'Add a new category to the inventory',
        'icon' => 'category',
        'color' => '#03A9F4',
        'onclick' => 'openModal("addCategoryModal")'
    ],
    [
        'title' => 'View Inventory',
        'description' => 'View all items in the inventory',
        'icon' => 'inventory',
        'color' => '#FF9800',
        'onclick' => 'window.location.href="inventory.php"'
    ]
];

$quickActions = $quickActions ?: [
    [
        'title' => 'No Actions',
        'description' => 'No quick actions available',
        'icon' => 'block',
        'color' => '#9E9E9E',
        'onclick' => ''
    ]
];

if ($userRole === 'superadmin') {
    $quickActions[] = [
        'title' => 'Add User',
        'description' => 'Add a new user to the system',
        'icon' => 'person_add',
        'color' => '#8BC34A',
        'onclick' => 'openModal("addUserModal")'
    ];
}

// Ensure analytics data is properly populated
$analyticsData = [
    [
        'title' => 'Total Items',
        'value' => isset($stats['total_items']) ? $stats['total_items'] : 0,
        'icon' => 'inventory_2',
        'color' => '#4CAF50',
        'trend' => 'up',
        'trendPercentage' => 12
    ],
    [
        'title' => 'Low Stock Items',
        'value' => isset($stats['low_stock_items']) ? $stats['low_stock_items'] : 0,
        'icon' => 'warning_amber',
        'color' => '#FF9800',
        'trend' => 'down',
        'trendPercentage' => 5
    ],
    [
        'title' => 'Total Categories',
        'value' => is_array($categories) ? count($categories) : 0,
        'icon' => 'category',
        'color' => '#2196F3',
        'trend' => 'up',
        'trendPercentage' => 8
    ],
    [
        'title' => 'Total Users',
        'value' => isset($stats['total_users']) ? $stats['total_users'] : 0,
        'icon' => 'group',
        'color' => '#9C27B0',
        'trend' => 'up',
        'trendPercentage' => 15
    ]
];

// Get items for category counting
$items = getAllItems($conn);
$items = $items ?: [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hospital Management</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.5s ease-out;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Form styles */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
        }
        
        /* Notification styles */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dashboard-grid > * {
            animation: fadeIn 0.6s ease-out;
        }
        
        .dashboard-card {
            animation-delay: 0.2s;
        }
        
        .recent-activity {
            animation-delay: 0.4s;
        }
        
        .quick-actions-section {
            animation-delay: 0.6s;
        }
        
        /* Recent Activities and Quick Actions styles */
        .recent-activities-container {
            background-color: #fff;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .recent-activities-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .recent-activities-header h2 {
            font-weight: 500;
            margin: 0;
        }
        
        .view-all-link {
            color: #337ab7;
            text-decoration: none;
        }
        
        .recent-activities-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid #ddd;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 1rem;
        }
        
        .activity-details {
            flex-grow: 1;
        }
        
        .activity-details h3 {
            font-weight: 500;
            margin: 0;
        }
        
        .activity-details p {
            margin: 0;
        }
        
        .quick-actions-container {
            background-color: #fff;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .quick-actions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .quick-actions-header h2 {
            font-weight: 500;
            margin: 0;
        }
        
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 1rem;
        }
        
        .quick-action-card {
            background-color: #fff;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        
        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .quick-action-details {
            flex-grow: 1;
        }
        
        .quick-action-details h3 {
            font-weight: 500;
            margin: 0;
        }
        
        .quick-action-details p {
            margin: 0;
        }
        
        /* Analytics Section */
        .analytics-section {
            margin-top: 2rem;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 1rem;
        }
        
        .dashboard-card {
            background-color: #fff;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 1rem;
        }
        
        .card-trend {
            font-weight: 500;
            font-size: 1.25rem;
        }
        
        .card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .card-title {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .card-value {
            font-size: 1.5rem;
            font-weight: 500;
        }
        
        .analytics-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 1rem;
        }
        
        .analytics-cards .card {
            background-color: #fff;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        
        .analytics-cards .card-content {
            display: flex;
            align-items: center;
        }
        
        .analytics-cards .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 1rem;
        }
        
        .analytics-cards .card-details {
            flex-grow: 1;
        }
        
        .analytics-cards .card-details h3 {
            font-weight: 500;
            margin: 0;
        }
        
        .analytics-cards .card-details p {
            margin: 0;
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
                <li class="active">
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
                <li>
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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Welcome back, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h1>
                <p class="date"><?= date('l, F j, Y') ?></p>
            </div>

            <!-- Analytics Section -->
            <div class="analytics-section">
                <div class="analytics-cards">
                    <div class="card total-items" onclick="window.location.href='inventory.php'">
                        <div class="card-content">
                            <span class="material-icons">inventory</span>
                            <div class="card-details">
                                <h3><?= $stats['total_items'] ?></h3>
                                <p>Total Items</p>
                            </div>
                        </div>
                    </div>
                    <div class="card low-stock" onclick="window.location.href='inventory.php?stock=low'">
                        <div class="card-content">
                            <span class="material-icons">warning</span>
                            <div class="card-details">
                                <h3><?= $stats['low_stock_items'] ?></h3>
                                <p>Low Stock Items</p>
                            </div>
                        </div>
                    </div>
                    <div class="card total-categories" onclick="document.querySelector('.categories-overview').scrollIntoView({behavior: 'smooth'})">
                        <div class="card-content">
                            <span class="material-icons">category</span>
                            <div class="card-details">
                                <h3><?= $stats['total_categories'] ?></h3>
                                <p>Total Categories</p>
                            </div>
                        </div>
                    </div>
                    <div class="card total-users" onclick="window.location.href='users.php'">
                        <div class="card-content">
                            <span class="material-icons">people</span>
                            <div class="card-details">
                                <h3><?= $stats['total_users'] ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Recent Activities Section -->
                <div class="recent-activities-container">
                    <div class="recent-activities-header">
                        <h2>Recent Activities</h2>
                        <a href="activities.php" class="view-all-link">View All</a>
                    </div>
                    <div class="recent-activities-list">
                        <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background-color: <?= $activity['color'] ?>;">
                                <span class="material-icons"><?= $activity['icon'] ?></span>
                            </div>
                            <div class="activity-details">
                                <h3><?= htmlspecialchars($activity['title']) ?></h3>
                                <p><?= htmlspecialchars($activity['description']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="quick-actions-section">
                    <div class="section-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="quick-actions-grid">
                        <?php foreach ($quickActions as $action): ?>
                        <div class="quick-action-card" onclick="<?= htmlspecialchars($action['onclick']) ?>">
                            <div class="quick-action-icon" style="background-color: <?= $action['color'] ?>;">
                                <span class="material-icons"><?= $action['icon'] ?></span>
                            </div>
                            <div class="quick-action-details">
                                <h3><?= htmlspecialchars($action['title']) ?></h3>
                                <p><?= htmlspecialchars($action['description']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Categories Overview -->
            <div class="categories-overview">
                <div class="section-header">
                    <h2>Categories Overview</h2>
                    <button class="add-category-btn" onclick="openModal('addCategoryModal')">
                        <span class="material-icons">add</span>
                    </button>
                </div>
                <div class="categories-grid">
                    <?php 
                    // Ensure categories is an array
                    $categories = $categories ?: [];
                    
                    foreach ($categories as $category): 
                        // Count items in this category
                        $categoryItemCount = 0;
                        foreach ($items as $item) {
                            if ($item['category_id'] == $category['category_id']) {
                                $categoryItemCount++;
                            }
                        }
                    ?>
                        <a href="inventory.php?category=<?= $category['category_id'] ?>" class="category-card">
                            <div class="category-icon">
                                <span class="material-icons">
                                    <?= !empty($category['icon']) ? htmlspecialchars($category['icon']) : 'category' ?>
                                </span>
                            </div>
                            <div class="category-details">
                                <h3><?= htmlspecialchars($category['name']) ?></h3>
                                <p>
                                    <?= $categoryItemCount . ' item' . ($categoryItemCount != 1 ? 's' : '') ?>
                                </p>
                            </div>
                            <div class="category-actions">
                                <button class="edit-btn" onclick="event.preventDefault(); editCategory(<?= $category['category_id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="delete-btn" onclick="event.preventDefault(); deleteCategory(<?= $category['category_id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    
                    <?php if (empty($categories)): ?>
                        <div class="no-categories">
                            <p>No categories found. Click "+" to add a new category.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <span class="modal-close" onclick="closeModal('addItemModal')">&times;</span>
            </div>
            <form action="../../actions/add_item.php" method="POST" id="addItemForm">
                <div class="form-group">
                    <label for="itemName">Item Name*</label>
                    <input type="text" id="itemName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="itemDescription">Description</label>
                    <textarea id="itemDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="itemCategory">Category*</label>
                    <select id="itemCategory" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="itemQuantity">Initial Quantity*</label>
                    <input type="number" id="itemQuantity" name="quantity" required min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="itemUnit">Unit of Measurement*</label>
                    <input type="text" id="itemUnit" name="unit" required placeholder="e.g., pieces, boxes, units">
                </div>
                <button type="submit" class="btn-submit">Add Item</button>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Category</h2>
                <span class="modal-close" onclick="closeModal('addCategoryModal')">&times;</span>
            </div>
            <form action="../../actions/add_category.php" method="POST" id="addCategoryForm">
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="categoryDescription">Description</label>
                    <textarea id="categoryDescription" name="description" rows="3"></textarea>
                </div>
                <button type="submit" class="btn-submit">Add Category</button>
            </form>
        </div>
    </div>

    <!-- Add User Modal -->
    <?php if ($userRole === 'superadmin'): ?>
    <div id="addUserModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <span class="modal-close" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <form action="../../actions/add_user.php" method="POST" id="addUserForm">
                <div class="form-group">
                    <label for="firstName">First Name*</label>
                    <input type="text" id="firstName" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name*</label>
                    <input type="text" id="lastName" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="userEmail">Email*</label>
                    <input type="email" id="userEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="userPassword">Password*</label>
                    <input type="password" id="userPassword" name="password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                <div class="form-group">
                    <label for="userRole">Role*</label>
                    <select id="userRole" name="role" required>
                        <option value="">Select a role</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Add User</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Modal Functions
        function openModal(modalId) {
            const modalOverlay = document.getElementById(modalId);
            if (modalOverlay) {
                modalOverlay.style.display = 'flex';
                setTimeout(() => {
                    modalOverlay.classList.add('show');
                }, 10);
            }
        }

        function closeModal(modalId) {
            const modalOverlay = document.getElementById(modalId);
            if (modalOverlay) {
                modalOverlay.classList.remove('show');
                setTimeout(() => {
                    modalOverlay.style.display = 'none';
                }, 300);
            }
        }

        // Add close button functionality to all modals
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission handler
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success notification
                            const notification = document.createElement('div');
                            notification.className = 'notification success';
                            notification.textContent = data.message;
                            document.body.appendChild(notification);
                            
                            // Close the modal
                            closeModal(this.closest('.modal-overlay').id);
                            
                            // Reload the page after a short delay
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            // Show error notification
                            const notification = document.createElement('div');
                            notification.className = 'notification error';
                            notification.textContent = data.message;
                            document.body.appendChild(notification);
                            
                            // Remove notification after 3 seconds
                            setTimeout(() => {
                                notification.remove();
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        // Show error notification
                        const notification = document.createElement('div');
                        notification.className = 'notification error';
                        notification.textContent = 'An error occurred. Please try again.';
                        document.body.appendChild(notification);
                        
                        // Remove notification after 3 seconds
                        setTimeout(() => {
                            notification.remove();
                        }, 3000);
                    });
                });
            });

            // Close modal when clicking outside
            const closeButtons = document.querySelectorAll('.modal-close');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modalOverlay = this.closest('.modal-overlay');
                    if (modalOverlay) {
                        closeModal(modalOverlay.id);
                    }
                });
            });

            // Close modal when clicking outside
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        closeModal(this.id);
                    }
                });
            });
        });
    </script>

    <script>
        // Notification dismissal
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            
            notifications.forEach(notification => {
                const closeBtn = notification.querySelector('.close-notification');
                
                // Auto-dismiss after 5 seconds
                const autoCloseTimer = setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
                
                // Manual close
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        clearTimeout(autoCloseTimer);
                        notification.style.display = 'none';
                    });
                }
            });
        });
    </script>

    <script>
        // Edit category function
        function editCategory(categoryId, categoryName) {
            // Open edit category modal
            const editModal = document.getElementById('editCategoryModal');
            if (!editModal) {
                // Create modal dynamically if it doesn't exist
                const modalHtml = `
                    <div id="editCategoryModal" class="modal-overlay">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Edit Category</h2>
                                <button class="close-modal" onclick="closeModal('editCategoryModal')">
                                    <span class="material-icons">close</span>
                                </button>
                            </div>
                            <form id="editCategoryForm" method="POST" action="../../actions/edit_category.php">
                                <input type="hidden" name="category_id" value="${categoryId}">
                                <div class="form-group">
                                    <label for="categoryName">Category Name</label>
                                    <input type="text" id="categoryName" name="name" value="${categoryName}" required>
                                </div>
                                <div class="form-group">
                                    <label for="categoryIcon">Category Icon</label>
                                    <select id="categoryIcon" name="icon">
                                        <option value="category">Default Category</option>
                                        <option value="inventory">Inventory</option>
                                        <option value="folder">Folder</option>
                                        <option value="list">List</option>
                                    </select>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('editCategoryModal')">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }
            
            // Open the modal
            openModal('editCategoryModal');
        }

        // Delete category function
        function deleteCategory(categoryId, categoryName) {
            // Create confirmation modal
            const confirmModal = document.createElement('div');
            confirmModal.id = 'deleteCategoryModal';
            confirmModal.className = 'modal-overlay';
            confirmModal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Delete Category</h2>
                        <button class="close-modal" onclick="closeModal('deleteCategoryModal')">
                            <span class="material-icons">close</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the category "${categoryName}"?</p>
                        <p class="warning">Warning: This will remove all items in this category!</p>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('deleteCategoryModal')">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteCategory(${categoryId})">Delete</button>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmModal);
            
            // Open the modal
            openModal('deleteCategoryModal');
        }

        // Confirm delete category function
        function confirmDeleteCategory(categoryId) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../actions/delete_category.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_id';
            input.value = categoryId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>