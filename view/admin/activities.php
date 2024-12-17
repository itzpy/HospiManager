<?php
session_start();

// Define the base path
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Include required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/functions/auth_functions.php';
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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$activitiesPerPage = 20;
$offset = ($page - 1) * $activitiesPerPage;

// Get all activities with user details
$query = "SELECT al.*, u.first_name, u.last_name 
          FROM activity_log al
          JOIN users u ON al.user_id = u.user_id
          ORDER BY al.timestamp DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $activitiesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$activities = $result->fetch_all(MYSQLI_ASSOC);

// Get total number of activities for pagination
$countQuery = "SELECT COUNT(*) as total FROM activity_log";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute();
$totalActivities = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalActivities / $activitiesPerPage);

// Function to determine icon and color based on action
function getActivityIcon($action) {
    switch ($action) {
        case 'add':
            return ['icon' => 'add_circle', 'color' => '#4CAF50'];
        case 'remove':
            return ['icon' => 'remove_circle', 'color' => '#F44336'];
        case 'update':
            return ['icon' => 'update', 'color' => '#FF9800'];
        case 'stock_in':
            return ['icon' => 'add_shopping_cart', 'color' => '#2196F3'];
        default:
            return ['icon' => 'info', 'color' => '#9E9E9E'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Activities - Hospital Management</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .activities-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .activities-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activities-header-content {
            display: flex;
            align-items: center;
        }

        .activities-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .activity-item:hover {
            background-color: #f0f0f0;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
        }

        .activity-details {
            flex-grow: 1;
        }

        .activity-details h3 {
            margin: 0 0 5px 0;
            font-size: 0.9rem;
        }

        .activity-details p {
            margin: 0;
            color: #666;
            font-size: 0.8rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
        }

        .back-button {
            margin-right: 10px;
            text-decoration: none;
            color: #333;
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
            <div class="activities-container">
                <div class="activities-header">
                    <div class="activities-header-content">
                        <a href="dashboard.php" class="back-button">
                            <span class="material-icons">arrow_back</span>
                        </a>
                        <h1>All Activities</h1>
                    </div>
                </div>
                <div class="activities-list">
                    <?php foreach ($activities as $activity): 
                        $activityDetails = getActivityIcon($activity['action']);
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background-color: <?= $activityDetails['color'] ?>;">
                            <span class="material-icons"><?= $activityDetails['icon'] ?></span>
                        </div>
                        <div class="activity-details">
                            <h3>
                                <?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?> 
                                <?= htmlspecialchars($activity['action']) ?> 
                                Item #<?= htmlspecialchars($activity['item_id']) ?>
                            </h3>
                            <p>
                                Quantity Changed: <?= htmlspecialchars($activity['quantity_changed']) ?> 
                                | <?= date('M j, Y H:i', strtotime($activity['timestamp'])) ?>
                            </p>
                            <?php if (!empty($activity['notes'])): ?>
                            <p><em>Notes: <?= htmlspecialchars($activity['notes']) ?></em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
