<?php
session_start();

// Define the base path
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Include required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/functions/auth_functions.php';
require_once BASE_PATH . '/functions/inventory_functions.php';
require_once BASE_PATH . '/functions/category_functions.php';
require_once BASE_PATH . '/functions/dashboard_functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$fullName = $_SESSION['full_name'] ?? 'User';

// Restrict access to superadmin only
if ($userRole !== 'superadmin') {
    // Redirect to admin inventory for regular admin
    header('Location: admin_inventory.php');
    exit();
}

// Debug logging function
function debugLog($message) {
    error_log("[Inventory Filter Debug] " . $message, 3, "C:/xampp/htdocs/Hospital_Management/logs/inventory_filter_debug.log");
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$stockFilter = $_GET['stock'] ?? '';
$sortColumn = $_GET['sort'] ?? 'name';
$sortOrder = $_GET['order'] ?? 'asc';

// Debug logging of filter values
debugLog("Search Query: " . $searchQuery);
debugLog("Category Filter: " . $categoryFilter);
debugLog("Stock Filter: " . $stockFilter);
debugLog("Sort Column: " . $sortColumn);
debugLog("Sort Order: " . $sortOrder);

// Validate sort column and order
$validColumns = ['name', 'category_name', 'quantity', 'unit', 'last_updated', 'status'];
$sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'name';
$sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

// Prepare base query with status calculation
$query = "SELECT i.*, c.name as category_name, 
          CASE 
              WHEN i.quantity = 0 THEN 'Out of Stock'
              WHEN i.quantity <= 10 THEN 'Low Stock'
              ELSE 'In Stock'
          END as status
          FROM items i 
          LEFT JOIN categories c ON i.category_id = c.category_id 
          WHERE 1=1";
$params = [];
$types = '';

// Apply search filter
if (!empty($searchQuery)) {
    $query .= " AND (i.name LIKE ? OR c.name LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= 'ss';
}

// Apply category filter
if (!empty($categoryFilter)) {
    // Validate that the category exists
    $categoryCheckStmt = $conn->prepare("SELECT COUNT(*) as count FROM categories WHERE category_id = ?");
    $categoryCheckStmt->bind_param('i', $categoryFilter);
    $categoryCheckStmt->execute();
    $categoryCheckResult = $categoryCheckStmt->get_result()->fetch_assoc();
    
    debugLog("Category Check Count: " . $categoryCheckResult['count']);
    
    if ($categoryCheckResult['count'] > 0) {
        $query .= " AND i.category_id = ?";
        $params[] = &$categoryFilter;
        $types .= 'i';
    } else {
        debugLog("Invalid category filter: " . $categoryFilter);
    }
}

// Apply stock filter
if (!empty($stockFilter)) {
    debugLog("Applying Stock Filter: " . $stockFilter);
    switch ($stockFilter) {
        case 'low':
            $query .= " AND i.quantity > 0 AND i.quantity <= 10";
            break;
        case 'out':
            $query .= " AND i.quantity = 0";
            break;
        case 'available':
            $query .= " AND i.quantity > 10";
            break;
        default:
            debugLog("Invalid stock filter: " . $stockFilter);
    }
}

// Add sorting
if ($sortColumn === 'category_name') {
    $query .= " ORDER BY c.name {$sortOrder}";
} elseif ($sortColumn === 'last_updated') {
    $query .= " ORDER BY i.last_updated {$sortOrder}";
} elseif ($sortColumn === 'status') {
    $query .= " ORDER BY status {$sortOrder}";
} else {
    $query .= " ORDER BY i.{$sortColumn} {$sortOrder}";
}

// Debug the final query
debugLog("Final Query: " . $query);
debugLog("Param Types: " . $types);
debugLog("Param Count: " . count($params));

// Prepare and execute the query
$stmt = $conn->prepare($query);
    
// Bind parameters if any
if (!empty($params)) {
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
}
    
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

// Debug number of items retrieved
debugLog("Items Retrieved: " . count($items));

// Ensure items is an array
$items = $items ?: [];

// Get categories
$categories = getAllCategories($conn);

// Get low stock items
$lowStockItems = getLowStockItems($conn);

// Initialize empty arrays if no data
$categories = $categories ?: [];
$lowStockItems = $lowStockItems ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Hospital Management</title>
    
    <!-- jQuery (must be loaded before other scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/inventory.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Existing styles... */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: background-color 0.2s ease;
        }

        .sortable:hover {
            background-color: #f0f0f0;
        }

        .sortable .sort-icon {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 20px;
            opacity: 0.7;
        }

        .sortable.active-sort {
            background-color: #e6e6e6;
        }

        .sortable.active-sort .sort-icon {
            opacity: 1;
            color: #333;
        }

        .sort-filter {
            display: flex;
            gap: 10px;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            
            /* Flexbox for centering */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: #fefefe;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transform: scale(1);
            opacity: 1;
            transition: all 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.2em;
            color: #333;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-cancel, .btn-submit {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .btn-cancel {
            background-color: #f0f0f0;
            color: #333;
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #e0e0e0;
        }

        .btn-submit:hover {
            background-color: #45a049;
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
                <li class="active">
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
            <!-- Inventory Header -->
            <div class="inventory-container">
                <div class="content-header">
                    <h1>Inventory Management</h1>
                    <button class="add-btn" onclick="openModal('addItemModal')">
                        <span class="material-icons">add</span>
                        Add Item
                    </button>
                </div>

                <!-- Inventory Filters -->
                <div class="filters-section">
                    <div class="search-filter">
                        <input type="text" id="searchInput" placeholder="Search items..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <span class="material-icons search-icon">search</span>
                    </div>
                    <div class="filter-group">
                        <label for="categoryFilter">Category:</label>
                        <select id="categoryFilter" onchange="updateFilter('category', this.value)">
                            <option value="">All Categories</option>
                            <?php 
                            // Fetch categories for dropdown
                            $categoryQuery = "SELECT category_id, name FROM categories ORDER BY name";
                            $categoryResult = $conn->query($categoryQuery);
                            while ($category = $categoryResult->fetch_assoc()) {
                                $selected = ($categoryFilter == $category['category_id']) ? 'selected' : '';
                                echo "<option value='{$category['category_id']}' $selected>" . 
                                     htmlspecialchars($category['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="stockFilter">Stock Level:</label>
                        <select id="stockFilter" onchange="updateFilter('stock', this.value)">
                            <option value="">All Stock Levels</option>
                            <option value="available" <?= $stockFilter == 'available' ? 'selected' : '' ?>>Available (>10)</option>
                            <option value="low" <?= $stockFilter == 'low' ? 'selected' : '' ?>>Low Stock (1-10)</option>
                            <option value="out" <?= $stockFilter == 'out' ? 'selected' : '' ?>>Out of Stock (0)</option>
                        </select>
                    </div>
                    <div class="sort-filter">
                        <select id="sortColumn" onchange="updateFilter('sort', this.value)">
                            <option value="name" <?= $sortColumn == 'name' ? 'selected' : '' ?>>Name</option>
                            <option value="category_name" <?= $sortColumn == 'category_name' ? 'selected' : '' ?>>Category</option>
                            <option value="quantity" <?= $sortColumn == 'quantity' ? 'selected' : '' ?>>Quantity</option>
                            <option value="unit" <?= $sortColumn == 'unit' ? 'selected' : '' ?>>Unit</option>
                            <option value="last_updated" <?= $sortColumn == 'last_updated' ? 'selected' : '' ?>>Last Updated</option>
                            <option value="status" <?= $sortColumn == 'status' ? 'selected' : '' ?>>Status</option>
                        </select>
                        <select id="sortOrder" onchange="updateFilter('order', this.value)">
                            <option value="asc" <?= $sortOrder == 'asc' ? 'selected' : '' ?>>Ascending</option>
                            <option value="desc" <?= $sortOrder == 'desc' ? 'selected' : '' ?>>Descending</option>
                        </select>
                    </div>
                </div>

                <!-- Inventory Table -->
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="name">
                                Item Name 
                                <?php if ($sortColumn === 'name'): ?>
                                    <span class="material-icons sort-icon">
                                        <?= $sortOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down' ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="category_name">
                                Category 
                                <?php if ($sortColumn === 'category_name'): ?>
                                    <span class="material-icons sort-icon">
                                        <?= $sortOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down' ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="quantity">
                                Quantity 
                                <?php if ($sortColumn === 'quantity'): ?>
                                    <span class="material-icons sort-icon">
                                        <?= $sortOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down' ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="unit">
                                Unit 
                                <?php if ($sortColumn === 'unit'): ?>
                                    <span class="material-icons sort-icon">
                                        <?= $sortOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down' ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="last_updated">
                                Last Updated 
                                <?php if ($sortColumn === 'last_updated'): ?>
                                    <span class="material-icons sort-icon">
                                        <?= $sortOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down' ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="status">
                                Status 
                                <?php if ($sortColumn === 'status'): ?>
                                    <span class="material-icons sort-icon">
                                        <?= $sortOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down' ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr data-item-id="<?= $item['item_id'] ?>" data-category="<?= $item['category_id'] ?>" 
                            data-stock="<?= $item['quantity'] <= 10 ? 'low' : ($item['quantity'] == 0 ? 'out' : 'available') ?>">
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['category_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($item['last_updated'])) ?></td>
                            <td>
                                <span class="status-badge <?= $item['quantity'] <= 10 ? 'warning' : ($item['quantity'] == 0 ? 'danger' : 'success') ?>">
                                    <?= $item['status'] ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn" onclick="editItem(<?= $item['item_id'] ?>)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="action-btn stock-btn" onclick="adjustStock(<?= $item['item_id'] ?>)">
                                    <span class="material-icons">inventory</span>
                                </button>
                                <?php if ($userRole === 'superadmin'): ?>
                                <button class="action-btn delete-btn" data-delete-id="<?= $item['item_id'] ?>" onclick="deleteItem(<?= $item['item_id'] ?>)">
                                    <span class="material-icons">delete</span>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Low Stock Alerts -->
            <?php if (!empty($lowStockItems)): ?>
            <div class="alerts-section">
                <h2>Low Stock Alerts</h2>
                <div class="alerts-grid">
                    <?php foreach ($lowStockItems as $item): ?>
                    <div class="alert-card <?= $item['quantity'] == 0 ? 'danger' : 'warning' ?>">
                        <div class="alert-icon">
                            <span class="material-icons"><?= $item['quantity'] == 0 ? 'error' : 'warning' ?></span>
                        </div>
                        <div class="alert-content">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>Current Stock: <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?></p>
                            <button class="btn" onclick="adjustStock(<?= $item['item_id'] ?>)">
                                Update Stock
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="form-group">
                        <label for="item_name">Item Name <span class="required">*</span></label>
                        <input type="text" id="item_name" name="name" required placeholder="Enter item name" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Initial Quantity <span class="required">*</span></label>
                        <input type="number" id="quantity" name="quantity" min="0" required placeholder="Enter initial quantity">
                    </div>
                    <div class="form-group">
                        <label for="unit">Unit <span class="required">*</span></label>
                        <input type="text" id="unit" name="unit" required placeholder="e.g., pcs, kg, ml" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Optional item description" maxlength="500"></textarea>
                    </div>
                </form>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('addItemModal')">Cancel</button>
                <button type="submit" form="addItemForm" class="btn-submit">Add Item</button>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Item</h2>
                <span class="close" onclick="closeModal('editItemModal')">&times;</span>
            </div>
            <form id="editItemForm">
                <input type="hidden" id="edit_item_id" name="item_id">
                <div class="form-group">
                    <label for="edit_item_name">Item Name</label>
                    <input type="text" id="edit_item_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_category_id">Category</label>
                    <select id="edit_category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_unit">Unit</label>
                    <input type="text" id="edit_unit" name="unit" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_quantity">Quantity</label>
                    <input type="number" id="edit_quantity" name="quantity" min="0" required>
                </div>
                <button type="submit" class="btn-submit">Update Item</button>
            </form>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div id="adjustStockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Adjust Stock</h2>
                <span class="close" onclick="closeModal('adjustStockModal')">&times;</span>
            </div>
            <form id="adjustStockForm">
                <input type="hidden" id="adjust_item_id" name="item_id">
                <div class="form-group">
                    <label>Item Name</label>
                    <p id="adjust_item_name" class="readonly-field"></p>
                </div>
                <div class="form-group">
                    <label>Current Quantity</label>
                    <p id="current_quantity" class="readonly-field"></p>
                </div>
                <div class="form-group">
                    <label for="adjust_type">Action</label>
                    <select id="adjust_type" name="adjust_type" required>
                        <option value="remove">Remove</option>
                        <option value="add">Add</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" required placeholder="Reason for stock adjustment"></textarea>
                </div>
                <button type="submit" class="btn-submit">Update Stock</button>
            </form>
        </div>
    </div>

    <script src="../../assets/js/inventory.js"></script>
    <script>
        // Comprehensive modal and button debugging
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded');

            // Detailed button selection and logging
            const addButton = document.querySelector('.add-btn');
            const modalElement = document.getElementById('addItemModal');

            console.log('Add Button:', addButton);
            console.log('Add Item Modal:', modalElement);

            // Ensure button exists and has click event
            if (addButton) {
                addButton.addEventListener('click', function(e) {
                    console.log('Add button clicked!');
                    e.preventDefault(); // Prevent any default action
                    
                    // Force modal to show
                    if (modalElement) {
                        modalElement.style.display = 'flex';
                        modalElement.classList.add('show');
                        
                        // Ensure modal stays visible
                        setTimeout(() => {
                            modalElement.style.opacity = '1';
                            modalElement.style.visibility = 'visible';
                        }, 10);
                    } else {
                        console.error('Modal element not found!');
                    }
                });
            } else {
                console.error('Add button not found on the page!');
            }

            // Fallback inline onclick method
            window.openModal = function(modalId) {
                console.log('Fallback openModal called');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                    
                    // Ensure modal stays visible
                    setTimeout(() => {
                        modal.style.opacity = '1';
                        modal.style.visibility = 'visible';
                    }, 10);
                }
            }

            // Close modal when clicking outside
            if (modalElement) {
                modalElement.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                        this.classList.remove('show');
                    }
                });
            }

            // Close button functionality
            const closeButton = document.querySelector('#addItemModal .close');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    const modal = document.getElementById('addItemModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                    }
                });
            }

            // Add Item Form submission
            const addItemForm = document.getElementById('addItemForm');
            if (addItemForm) {
                addItemForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Collect form data
                    const formData = new FormData(this);
                    
                    // Send AJAX request
                    fetch('../../actions/add_item.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            alert(data.message);
                            
                            // Close the modal
                            closeModal('addItemModal');
                            
                            // Reload the page to show updated inventory
                            location.reload();
                        } else {
                            // Show error message
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred');
                    });
                });
            }
        });

        // Enhanced filter update and initialization JavaScript with logging
        function updateFilter(filterType, value) {
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Log the filter update
            console.log(`Updating filter - Type: ${filterType}, Value: ${value}`);
            
            // Set or remove the filter parameter
            if (value) {
                urlParams.set(filterType, value);
            } else {
                urlParams.delete(filterType);
            }
            
            // Remove page parameter to reset pagination
            urlParams.delete('page');
            
            // Construct new URL
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            
            // Log the new URL
            console.log(`New URL: ${newUrl}`);
            
            // Redirect to the new URL
            window.location.href = newUrl;
        }

        // Enhanced filter initialization
        function initializeFilters() {
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Set initial values for dropdowns
            const categoryFilter = urlParams.get('category') || '';
            const stockFilter = urlParams.get('stock') || '';
            
            // Log initial filter states
            console.log(`Initial Category Filter: ${categoryFilter}`);
            console.log(`Initial Stock Filter: ${stockFilter}`);
            
            // Update dropdowns to match URL
            const categorySelect = document.getElementById('categoryFilter');
            const stockSelect = document.getElementById('stockFilter');
            
            if (categorySelect) {
                categorySelect.value = categoryFilter;
            }
            
            if (stockSelect) {
                stockSelect.value = stockFilter;
            }
        }

        // Call initialization on page load
        document.addEventListener('DOMContentLoaded', initializeFilters);

        // Add click event to sortable headers
        const sortableHeaders = document.querySelectorAll('.sortable');
        
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                // Get the current column and order from URL or set defaults
                const urlParams = new URLSearchParams(window.location.search);
                const currentSort = urlParams.get('sort') || 'name';
                const currentOrder = urlParams.get('order') || 'asc';
                
                // Get the column to sort from the clicked header
                const column = this.getAttribute('data-column');
                
                // Determine new sort order
                let newOrder = 'asc';
                if (column === currentSort) {
                    // If clicking the same column, toggle the order
                    newOrder = (currentOrder === 'asc') ? 'desc' : 'asc';
                }
                
                // Update URL parameters
                urlParams.set('sort', column);
                urlParams.set('order', newOrder);
                
                // Redirect or reload with new sorting
                window.location.search = urlParams.toString();
            });
        });

        // Ensure current sort column and order are visually indicated
        function highlightCurrentSort() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort') || 'name';
            const currentOrder = urlParams.get('order') || 'asc';

            sortableHeaders.forEach(header => {
                const column = header.getAttribute('data-column');
                const sortIcon = header.querySelector('.sort-icon');

                if (column === currentSort) {
                    // Add active sort class
                    header.classList.add('active-sort');
                    
                    // Update sort icon
                    if (sortIcon) {
                        sortIcon.textContent = currentOrder === 'asc' ? 'arrow_drop_up' : 'arrow_drop_down';
                        sortIcon.style.display = 'inline-block';
                    }
                } else {
                    header.classList.remove('active-sort');
                    if (sortIcon) {
                        sortIcon.style.display = 'none';
                    }
                }
            });
        }

        // Call on page load
        highlightCurrentSort();
    </script>
    <script>
        // Delete Item Function
        function deleteItem(itemId) {
            // Confirm deletion
            if (!confirm('Are you sure you want to delete this item?')) {
                return;
            }

            // Disable delete button to prevent multiple clicks
            const deleteButton = document.querySelector(`button[data-delete-id="${itemId}"]`);
            const itemRow = document.querySelector(`tr[data-item-id="${itemId}"]`);
            
            if (deleteButton) {
                deleteButton.disabled = true;
                deleteButton.innerHTML = '<span class="material-icons">hourglass_empty</span> Deleting...';
            }

            // Check if jQuery is available
            if (typeof $ !== 'undefined') {
                // jQuery AJAX method
                $.ajax({
                    url: '../../actions/delete_item.php',  // Updated path
                    method: 'POST',
                    dataType: 'json',
                    data: { 
                        item_id: itemId 
                    },
                    timeout: 10000,
                    success: function(response) {
                        if (response && response.success) {
                            // Remove the item row
                            if (itemRow) {
                                itemRow.classList.add('fade-out');
                                setTimeout(() => {
                                    itemRow.remove();
                                    updateItemCount();
                                    showToast('Item deleted successfully', 'success');
                                }, 300);
                            }
                        } else {
                            // Show error message
                            showToast(response.message || 'Failed to delete item', 'error');
                            console.error('Delete Item Error:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Fallback to fetch if jQuery AJAX fails
                        fetchDeleteItem(itemId, deleteButton, itemRow);
                    },
                    complete: function() {
                        // Re-enable delete button
                        if (deleteButton) {
                            deleteButton.disabled = false;
                            deleteButton.innerHTML = '<span class="material-icons">delete</span>';
                        }
                    }
                });
            } else {
                // Fallback to fetch if jQuery is not available
                fetchDeleteItem(itemId, deleteButton, itemRow);
            }
        }

        // Fetch-based delete method as fallback
        function fetchDeleteItem(itemId, deleteButton, itemRow) {
            fetch('../../actions/delete_item.php', {  // Updated path
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: `item_id=${itemId}`,
                credentials: 'same-origin'
            })
            .then(response => {
                // Check response status
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Check content type
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Expected JSON response');
                }

                // Parse JSON
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove the item row
                    if (itemRow) {
                        itemRow.classList.add('fade-out');
                        setTimeout(() => {
                            itemRow.remove();
                            updateItemCount();
                            showToast('Item deleted successfully', 'success');
                        }, 300);
                    }
                } else {
                    // Show error message
                    showToast(data.message || 'Failed to delete item', 'error');
                    console.error('Delete Item Error:', data);
                }
            })
            .catch(error => {
                console.error('Delete Item Error:', error);
                
                // Show error toast
                showToast(error.message || 'Failed to delete item', 'error');
            })
            .finally(() => {
                // Re-enable delete button
                if (deleteButton) {
                    deleteButton.disabled = false;
                    deleteButton.innerHTML = '<span class="material-icons">delete</span>';
                }
            });
        }

        // Function to update item count
        function updateItemCount() {
            const itemCountElement = document.getElementById('total-items-count');
            if (itemCountElement) {
                let currentCount = parseInt(itemCountElement.textContent);
                itemCountElement.textContent = currentCount - 1;
            }
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                document.body.appendChild(toastContainer);
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;

            // Add to container
            toastContainer.appendChild(toast);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Add some CSS for animations and toast
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; transform: translateX(-100%); }
            }

            .fade-out {
                animation: fadeOut 0.3s ease-out forwards;
            }

            #toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }

            .toast {
                padding: 15px;
                margin-bottom: 10px;
                border-radius: 4px;
                color: white;
                opacity: 0;
                animation: slideIn 0.3s ease-out forwards;
                max-width: 300px;
            }

            .toast-success {
                background-color: #4CAF50;
            }

            .toast-error {
                background-color: #F44336;
            }

            .toast-info {
                background-color: #2196F3;
            }

            @keyframes slideIn {
                from { 
                    opacity: 0; 
                    transform: translateX(100%); 
                }
                to { 
                    opacity: 1; 
                    transform: translateX(0); 
                }
            }

            .toast.fade-out {
                animation: fadeOut 0.3s ease-out forwards;
            }
        `;
        document.head.appendChild(styleElement);
    </script>
    <script>
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
