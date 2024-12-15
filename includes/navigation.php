<?php
// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>Hospital</h3>
        <h3>Management</h3>
    </div>
    <ul class="nav-links">
        <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <a href="/Hospital_Management/view/admin/dashboard.php">
                <span class="material-icons">dashboard</span>
                <span>Dashboard</span>
            </a>
        </li>
        <?php if ($_SESSION['role'] === 'superadmin'): ?>
        <li class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
            <a href="/Hospital_Management/view/admin/users.php">
                <span class="material-icons">people</span>
                <span>Users</span>
            </a>
        </li>
        <?php endif; ?>
        <li class="<?php echo $currentPage === 'inventory.php' ? 'active' : ''; ?>">
            <a href="/Hospital_Management/view/admin/inventory.php">
                <span class="material-icons">inventory_2</span>
                <span>Inventory</span>
            </a>
        </li>
        <li class="<?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>">
            <a href="/Hospital_Management/view/admin/categories.php">
                <span class="material-icons">category</span>
                <span>Categories</span>
            </a>
        </li>
        <li class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
            <a href="/Hospital_Management/view/admin/reports.php">
                <span class="material-icons">assessment</span>
                <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="/Hospital_Management/actions/logout.php">
                <span class="material-icons">logout</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    background: #9575CD;
    padding: 20px 0;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 500;
    color: white;
}

.nav-links {
    list-style: none;
    padding: 20px 0;
    margin: 0;
}

.nav-links li {
    padding: 0;
    margin: 0;
}

.nav-links li a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-links li a:hover,
.nav-links li.active a {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-links li a span.material-icons {
    margin-right: 10px;
    font-size: 20px;
}

/* Add margin to main content to account for sidebar */
.main-content {
    margin-left: 250px;
    padding: 2rem;
    background-color: #f5f5f5;
    min-height: 100vh;
}

body {
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
}
</style>
