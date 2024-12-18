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
$fullName = $_SESSION['first_name'] ?? 'User';

// Debug logging function
// function debugLog($message) {
//     error_log("[Inventory Filter Debug] " . $message, 3, "C:/xampp/htdocs/Hospital_Management/logs/inventory_filter_debug.log");
// }

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$stockFilter = $_GET['stock'] ?? '';
$sortColumn = $_GET['sort'] ?? 'name';
$sortOrder = $_GET['order'] ?? 'asc';

// Debug logging of filter values
// debugLog("Search Query: " . $searchQuery);
// debugLog("Category Filter: " . $categoryFilter);
// debugLog("Stock Filter: " . $stockFilter);
// debugLog("Sort Column: " . $sortColumn);
// debugLog("Sort Order: " . $sortOrder);

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
    
    // debugLog("Category Check Count: " . $categoryCheckResult['count']);
    
    if ($categoryCheckResult['count'] > 0) {
        $query .= " AND i.category_id = ?";
        $params[] = &$categoryFilter;
        $types .= 'i';
    } else {
        // debugLog("Invalid category filter: " . $categoryFilter);
    }
}

// Apply stock filter
if (!empty($stockFilter)) {
    // debugLog("Applying Stock Filter: " . $stockFilter);
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
            // debugLog("Invalid stock filter: " . $stockFilter);
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
// debugLog("Final Query: " . $query);
// debugLog("Param Types: " . $types);
// debugLog("Param Count: " . count($params));

// Check for AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    
    try {
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

        // Prepare JSON response
        $response = [
            'success' => true,
            'items' => array_map(function($item) {
                return [
                    'item_id' => $item['item_id'],
                    'name' => htmlspecialchars($item['name']),
                    'category_name' => htmlspecialchars($item['category_name']),
                    'quantity' => $item['quantity'],
                    'unit' => htmlspecialchars($item['unit']),
                    'last_updated' => $item['last_updated'],
                    'status' => $item['status']
                ];
            }, $items)
        ];
        
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        // Log the error
        error_log('AJAX Search Error: ' . $e->getMessage());
        
        // Return error response
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
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
$items = $result->fetch_all(MYSQLI_ASSOC);

// Debug number of items retrieved
// debugLog("Items Retrieved: " . count($items));

// Ensure items is an array
$items = $items ?: [];

// Filter items based on stock level
$filteredItems = $items;
if ($stockFilter === 'low') {
    $filteredItems = array_filter($items, function($item) {
        return $item['quantity'] <= 10; // Low stock threshold
    });
} elseif ($stockFilter === 'out') {
    $filteredItems = array_filter($items, function($item) {
        return $item['quantity'] == 0; // Out of stock items
    });
}

// Update the main items array for rendering
$items = $filteredItems;

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
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/inventory.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">
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
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            visibility: visible;
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
        

        .modal-header .close-modal:hover {
            color: #000;
            text-decoration: none;
            opacity: .75;
        }

        .modal-body {
            position: relative;
            flex: 1 1 auto;
            padding: 1rem;
        }

        .modal-body .form-group {
            margin-bottom: 1rem;
        }

        .modal-body label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .modal-body .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .modal-footer button {
            margin-left: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
        }

        .modal-footer .btn-submit {
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        .modal-footer .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
        }

        /* Low Stock Filter Info CSS */
        .filter-info {
            background-color: #f0f4f8;
            border-left: 4px solid #2196F3;
            padding: 10px 15px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-info p {
            margin: 0;
            color: #333;
        }

        .clear-filter-btn {
            color: #2196F3;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .clear-filter-btn:hover {
            color: #1976D2;
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
                    <a href="admin_dashboard.php">
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
                    <h1><?= $stockFilter === 'low' ? 'Low Stock Items' : ($stockFilter === 'out' ? 'Out of Stock Items' : 'Inventory Management') ?></h1>
                    <?php if ($stockFilter === 'low'): ?>
                    <div class="filter-info">
                        <p>Showing items with stock quantity of 10 or less</p>
                        <a href="admin_inventory.php" class="clear-filter-btn">Clear Filter</a>
                    </div>
                    <?php elseif ($stockFilter === 'out'): ?>
                    <div class="filter-info">
                        <p>Showing items that are out of stock</p>
                        <a href="admin_inventory.php" class="clear-filter-btn">Clear Filter</a>
                    </div>
                    <?php endif; ?>
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
                            <option value="low" <?= $stockFilter == 'low' ? 'selected' : '' ?>>Low Stock (<10)</option>
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
                            <td>
                                <?php if ($userRole === 'superadmin'): ?>
                                <div class="action-buttons">
                                    <button class="action-btn edit-btn" onclick="editItem(<?= $item['item_id'] ?>)">
                                        <span class="material-icons">edit</span>
                                    </button>
                                    <button class="action-btn delete-btn" data-delete-id="<?= $item['item_id'] ?>" onclick="deleteItem(<?= $item['item_id'] ?>)">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </div>
                                <?php endif; ?>
                                <button class="action-btn stock-btn" onclick="adjustStock(<?= $item['item_id'] ?>, '<?= htmlspecialchars($item['name']) ?>', <?= $item['quantity'] ?>)">
                                    <span class="material-icons">inventory</span>
                                </button>
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

    <!-- Adjust Stock Modal -->
    <div id="adjustStockModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="close-modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="adjustStockForm" method="POST" action="../../actions/adjust_stock.php">
                        <input type="hidden" id="adjust_item_id" name="item_id">
                        <div class="form-group">
                            <label>Item Name</label>
                            <p id="adjust_item_name" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label id="current_quantity">Current Stock: </label>
                        </div>
                        <div class="form-group">
                            <label for="adjust_type">Adjustment Type</label>
                            <select id="adjust_type" name="adjust_type" class="form-control" required>
                                <option value="remove">Remove</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Fallback inline onclick method
        window.openModal = function(modalId) {
            console.log('Fallback openModal called for modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                // Ensure modal is displayed and visible
                modal.style.display = 'flex';
                modal.style.opacity = '1';
                modal.style.visibility = 'visible';
                modal.classList.add('show');
                
                // Focus on the modal to ensure it's on top
                modal.focus();
            } else {
                console.error('Modal not found:', modalId);
            }
        }

        // Close modal when clicking outside and set up event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Remove any existing event listeners
            const adjustStockModal = document.getElementById('adjustStockModal');
            
            if (adjustStockModal) {
                // Click outside to close
                adjustStockModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                        this.classList.remove('show');
                        this.style.opacity = '0';
                        this.style.visibility = 'hidden';
                    }
                });

                // Close button functionality
                const closeButtons = adjustStockModal.querySelectorAll('.close-modal');
                closeButtons.forEach(closeButton => {
                    closeButton.addEventListener('click', function() {
                        adjustStockModal.style.display = 'none';
                        adjustStockModal.classList.remove('show');
                        adjustStockModal.style.opacity = '0';
                        adjustStockModal.style.visibility = 'hidden';
                    });
                });
            } else {
                console.error('Adjust Stock Modal not found');
            }

            // Stock adjustment function
            window.adjustStock = function(itemId, itemName, currentStock) {
                console.log('adjustStock called with:', { itemId, itemName, currentStock });

                // Ensure itemId is passed correctly
                if (!itemId) {
                    console.error('Item ID is required to adjust stock');
                    return;
                }

                // Get the stock adjustment modal
                const modal = document.getElementById('adjustStockModal');
                const itemNameEl = document.getElementById('adjust_item_name');
                const currentStockEl = document.getElementById('current_quantity');
                const stockActionSelect = document.getElementById('adjust_type');
                const stockQuantityInput = document.getElementById('quantity');
                const itemIdInput = document.getElementById('adjust_item_id');
                
                // Validate all required elements exist
                const requiredElements = [
                    { el: modal, name: 'Modal' },
                    { el: itemNameEl, name: 'Item Name Element' },
                    { el: currentStockEl, name: 'Current Stock Element' },
                    { el: stockActionSelect, name: 'Stock Action Select' },
                    { el: stockQuantityInput, name: 'Stock Quantity Input' },
                    { el: itemIdInput, name: 'Item ID Input' }
                ];

                const missingElements = requiredElements.filter(item => !item.el);
                
                if (missingElements.length > 0) {
                    console.error('Missing elements:', missingElements.map(item => item.name).join(', '));
                    alert('Error: Unable to open stock adjustment modal. Please contact support.');
                    return;
                }
                
                // Set item details
                itemNameEl.textContent = itemName || 'Unknown Item';
                currentStockEl.textContent = `Current Stock: ${currentStock}`;
                
                // Populate stock action options based on user role
                const userRole = '<?= $userRole ?>';
                stockActionSelect.innerHTML = `
                    <option value="add">Add Stock</option>
                    <option value="remove">Remove Stock</option>
                `;
                stockActionSelect.disabled = false;
                
                // Dynamic min and max handling based on action
                stockActionSelect.addEventListener('change', function() {
                    const action = this.value;
                    if (action === 'add') {
                        // For adding stock, set a high max value
                        stockQuantityInput.min = 1;
                        stockQuantityInput.max = userRole === 'superadmin' ? 1000 : 500;
                    } else {
                        // For removing stock, limit to current stock
                        stockQuantityInput.min = 1;
                        stockQuantityInput.max = currentStock;
                    }
                    // Reset the input value
                    stockQuantityInput.value = '';
                });
                
                // Trigger initial setup
                stockActionSelect.dispatchEvent(new Event('change'));
                
                // Store the item ID for form submission
                itemIdInput.value = itemId;
                
                // Open the modal with improved visibility
                window.openModal('adjustStockModal');
            }

            // Attach adjustStock to window to make it globally accessible
            window.adjustStock = window.adjustStock;
        });
    </script>
    <script>
        // Prevent edit and delete actions for non-superadmin users
        const userRole = '<?= $userRole ?>';

        function editItem(itemId) {
            if (userRole !== 'superadmin') {
                showToast('You do not have permission to edit items', 'error');
                return;
            }
            // Existing edit logic
            openEditModal(itemId);
        }

        function deleteItem(itemId) {
            if (userRole !== 'superadmin') {
                showToast('You do not have permission to delete items', 'error');
                return;
            }
            
            // Confirm deletion
            const confirmDelete = confirm('Are you sure you want to delete this item? This action cannot be undone.');
            
            if (confirmDelete) {
                console.log('Attempting to delete item with ID:', itemId);

                // Disable delete button to prevent multiple clicks
                const deleteButton = document.querySelector(`button[data-delete-id="${itemId}"]`);
                const itemRow = document.querySelector(`tr[data-item-id="${itemId}"]`);
                
                if (deleteButton) {
                    deleteButton.disabled = true;
                    deleteButton.innerHTML = '<span class="material-icons">hourglass_empty</span> Deleting...';
                }

                // Send AJAX request
                fetch('../../actions/delete_item.php', {
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
                    console.log('Delete Response:', data);

                    // Validate response
                    if (data.success) {
                        // Remove the item row from the table
                        if (itemRow) {
                            // Add a fade-out animation
                            itemRow.classList.add('fade-out');
                            setTimeout(() => {
                                itemRow.remove();
                                
                                // Update item count
                                updateItemCount();
                                
                                // Show success toast
                                showToast('Item deleted successfully', 'success');
                            }, 300);
                        }
                    } else {
                        // Show error message with debug info if available
                        let errorMsg = data.message || 'Failed to delete item';
                        
                        // If debug info is present, log it to console
                        if (data.debug) {
                            console.error('Delete Item Debug Info:', data.debug);
                        }
                        
                        // Show error toast
                        showToast(errorMsg, 'error');
                    }
                })
                .catch(error => {
                    console.error('Delete Item Error:', error);
                    
                    // Detailed error handling
                    let errorMessage = 'An unexpected error occurred';
                    if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    // Show error toast
                    showToast(errorMessage, 'error');
                })
                .finally(() => {
                    // Re-enable delete button
                    if (deleteButton) {
                        deleteButton.disabled = false;
                        deleteButton.innerHTML = '<span class="material-icons">delete</span>';
                    }
                });
            }
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
        // Enhanced filter update and initialization JavaScript with logging
        function updateFilter(filterType, value) {
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);

            // Update specific parameter based on filter type
            switch(filterType) {
                case 'search':
                    urlParams.set('search', value);
                    break;
                case 'category':
                    urlParams.set('category', value);
                    break;
                case 'stock':
                    urlParams.set('stock', value);
                    break;
                case 'sort':
                    urlParams.set('sort', value);
                    break;
                case 'order':
                    urlParams.set('order', value);
                    break;
            }

            // Remove page parameter to reset pagination
            urlParams.delete('page');

            // Construct new URL
            const newUrl = window.location.pathname + '?' + urlParams.toString();

            // Fetch new data via AJAX
            fetch(newUrl + '&ajax=true', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Received Data:', data);

                if (data.success) {
                    // Update table rows
                    const inventoryTable = document.querySelector('.inventory-table tbody');
                    inventoryTable.innerHTML = '';

                    data.items.forEach(item => {
                        const row = `
                            <tr data-item-id="${item.item_id}" data-category="${item.category_id}" 
                                data-stock="${item.quantity <= 10 ? 'low' : (item.quantity == 0 ? 'out' : 'available')}">
                                <td>${item.name}</td>
                                <td>${item.category_name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.unit}</td>
                                <td>${item.last_updated}</td>
                                <td>${item.status}</td>
                                <td>
                                    <button onclick="editItem(${item.item_id})" class="action-btn">
                                        <span class="material-icons">edit</span>
                                    </button>
                                    <button onclick="deleteItem(${item.item_id})" class="action-btn delete">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </td>
                            </tr>
                        `;
                        inventoryTable.innerHTML += row;
                    });

                    // Update URL without page reload
                    window.history.pushState({path: newUrl}, '', newUrl);
                } else {
                    console.error('Search failed:', data.error);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
            });
        }
    </script>
    <script>
        // Modify the existing adjustStock function to work for both admin and superadmin
        window.adjustStock = function(itemId, itemName, currentStock) {
            console.log('adjustStock called with:', { itemId, itemName, currentStock });

            // Ensure itemId is passed correctly
            if (!itemId) {
                console.error('Item ID is required to adjust stock');
                return;
            }

            // Get the stock adjustment modal
            const modal = document.getElementById('adjustStockModal');
            const itemNameEl = document.getElementById('adjust_item_name');
            const currentStockEl = document.getElementById('current_quantity');
            const stockActionSelect = document.getElementById('adjust_type');
            const stockQuantityInput = document.getElementById('quantity');
            const itemIdInput = document.getElementById('adjust_item_id');
            
            // Validate all required elements exist
            const requiredElements = [
                { el: modal, name: 'Modal' },
                { el: itemNameEl, name: 'Item Name Element' },
                { el: currentStockEl, name: 'Current Stock Element' },
                { el: stockActionSelect, name: 'Stock Action Select' },
                { el: stockQuantityInput, name: 'Stock Quantity Input' },
                { el: itemIdInput, name: 'Item ID Input' }
            ];

            const missingElements = requiredElements.filter(item => !item.el);
            
            if (missingElements.length > 0) {
                console.error('Missing elements:', missingElements.map(item => item.name).join(', '));
                alert('Error: Unable to open stock adjustment modal. Please contact support.');
                return;
            }
            
            // Set item details
            itemNameEl.textContent = itemName || 'Unknown Item';
            currentStockEl.textContent = `Current Stock: ${currentStock}`;
            
            // Populate stock action options based on user role
            const userRole = '<?= $userRole ?>';
            stockActionSelect.innerHTML = `
                <option value="add">Add Stock</option>
                <option value="remove">Remove Stock</option>
            `;
            stockActionSelect.disabled = false;
            
            // Dynamic min and max handling based on action
            stockActionSelect.addEventListener('change', function() {
                const action = this.value;
                if (action === 'add') {
                    // For adding stock, set a high max value
                    stockQuantityInput.min = 1;
                    stockQuantityInput.max = userRole === 'superadmin' ? 1000 : 500;
                } else {
                    // For removing stock, limit to current stock
                    stockQuantityInput.min = 1;
                    stockQuantityInput.max = currentStock;
                }
                // Reset the input value
                stockQuantityInput.value = '';
            });
            
            // Trigger initial setup
            stockActionSelect.dispatchEvent(new Event('change'));
            
            // Store the item ID for form submission
            itemIdInput.value = itemId;
            
            // Open the modal with improved visibility
            window.openModal('adjustStockModal');
        }

        // Attach adjustStock to window to make it globally accessible
        window.adjustStock = window.adjustStock;
    </script>
    <script>
        // Function to show notification
        function showNotification(message, isSuccess = true) {
            // Create notification element if it doesn't exist
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                notificationContainer.style.position = 'fixed';
                notificationContainer.style.top = '20px';
                notificationContainer.style.right = '20px';
                notificationContainer.style.zIndex = '1000';
                document.body.appendChild(notificationContainer);
            }

            // Create notification
            const notification = document.createElement('div');
            notification.style.backgroundColor = isSuccess ? '#4CAF50' : '#f44336';
            notification.style.color = 'white';
            notification.style.padding = '15px';
            notification.style.borderRadius = '5px';
            notification.style.marginBottom = '10px';
            notification.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s ease';
            
            notification.textContent = message;

            // Add to container
            notificationContainer.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
            }, 10);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notificationContainer.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Attach event listener to form
        const adjustStockForm = document.getElementById('adjustStockForm');
        if (adjustStockForm) {
            adjustStockForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submission prevented');
                
                const itemIdInput = document.getElementById('adjust_item_id');
                const quantityInput = document.getElementById('quantity');
                const stockActionSelect = document.getElementById('adjust_type');
                const notesInput = document.getElementById('notes');
                
                console.log('Form Elements:', {
                    itemIdInput: !!itemIdInput,
                    quantityInput: !!quantityInput,
                    stockActionSelect: !!stockActionSelect,
                    notesInput: !!notesInput
                });
                
                if (!itemIdInput || !quantityInput || !stockActionSelect || !notesInput) {
                    console.error('One or more form elements are missing');
                    return;
                }
                
                const itemId = itemIdInput.value;
                const quantity = quantityInput.value;
                const action = stockActionSelect.value;
                const notes = notesInput.value;
                
                console.log('Submission Data:', { itemId, quantity, action, notes });
                
                // Validate quantity
                if (quantity <= 0) {
                    showNotification('Quantity must be greater than 0', false);
                    return;
                }
                
                // Send AJAX request to adjust stock
                fetch('../../actions/adjust_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json'
                    },
                    body: `item_id=${itemId}&quantity=${quantity}&adjust_type=${action}&notes=${encodeURIComponent(notes)}`
                })
                .then(response => {
                    // First check if response is ok
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    // Check content type
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new TypeError("Expected JSON response from server");
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Server Response:', data);
                    
                    if (data.success) {
                        // Close modal
                        const modal = document.getElementById('adjustStockModal');
                        if (modal) {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            modal.style.opacity = '0';
                            modal.style.visibility = 'hidden';
                        }
                        
                        // Show success notification
                        showNotification(data.message || 'Stock adjusted successfully');
                        
                        // Reload page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        // Show error notification
                        showNotification(data.message || 'Failed to adjust stock', false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // More detailed error handling
                    let errorMessage = 'An unexpected error occurred';
                    if (error instanceof TypeError) {
                        errorMessage = 'Invalid response from server';
                    } else if (error instanceof Error) {
                        errorMessage = error.message;
                    }
                    showNotification(errorMessage, false);
                    
                    // Log detailed error information
                    console.error('Detailed error:', {
                        name: error.name,
                        message: error.message,
                        stack: error.stack
                    });
                });
            });
        } else {
            console.error('Adjust stock form not found');
        }
    </script>
</body>
</html>