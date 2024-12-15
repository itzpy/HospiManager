<?php
session_start();

// Define the base path
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Include required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/functions/auth_functions.php';

// Check if user is logged in and is a superadmin
if (!isLoggedIn() || !isSuperAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$fullName = $_SESSION['full_name'] ?? 'User';

// Handle settings update
$settingsUpdateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and process settings
        $hospitalName = trim($_POST['hospital_name'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $timezone = trim($_POST['timezone'] ?? 'UTC');

        // Validate inputs
        if (empty($hospitalName)) {
            throw new Exception('Hospital name cannot be empty');
        }

        if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Prepare update query
        $stmt = $conn->prepare("UPDATE system_settings SET 
            hospital_name = ?, 
            contact_email = ?, 
            contact_phone = ?, 
            timezone = ?
            WHERE id = 1");
        
        $stmt->bind_param("ssss", $hospitalName, $contactEmail, $contactPhone, $timezone);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update settings');
        }

        $settingsUpdateMessage = 'Settings updated successfully!';
    } catch (Exception $e) {
        $settingsUpdateMessage = $e->getMessage();
    }
}

// Fetch current settings
$settingsStmt = $conn->prepare("SELECT * FROM system_settings WHERE id = 1");
$settingsStmt->execute();
$settings = $settingsStmt->get_result()->fetch_assoc();

// List of timezones
$timezones = DateTimeZone::listIdentifiers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Hospital Management</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/users.css">
    <link rel="stylesheet" href="../../assets/css/inventory.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
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
            <div class="users-container">
                <div class="content-header">
                    <h1>System Settings</h1>
                </div>

                <?php if (!empty($settingsUpdateMessage)): ?>
                    <div class="alert <?= strpos($settingsUpdateMessage, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>">
                        <?= htmlspecialchars($settingsUpdateMessage) ?>
                    </div>
                <?php endif; ?>

                <div class="main-content-inner">
                    <form action="" method="POST" class="form">
                        <div class="form-section">
                            <h2>Hospital Information</h2>
                            <div class="form-group">
                                <label for="hospital_name">Hospital Name</label>
                                <input type="text" id="hospital_name" name="hospital_name" 
                                       value="<?= htmlspecialchars($settings['hospital_name'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_email">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email" 
                                       value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_phone">Contact Phone</label>
                                <input type="tel" id="contact_phone" name="contact_phone" 
                                       value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>System Preferences</h2>
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone" name="timezone" required>
                                    <?php foreach ($timezones as $tz): ?>
                                        <option value="<?= htmlspecialchars($tz) ?>" 
                                            <?= ($settings['timezone'] ?? '') === $tz ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tz) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/settings.js"></script>
</body>
</html>
