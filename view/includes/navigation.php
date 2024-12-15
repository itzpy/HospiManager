<?php
// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar">
    <div class="sidebar-header">
        <h3>Hospital Management</h3>
    </div>
    <ul class="nav-links">
        <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <a href="../admin/dashboard.php">
                <span class="material-icons">dashboard</span>
                <span>Dashboard</span>
            </a>
        </li>
        <?php if ($_SESSION['role'] === 'superadmin'): ?>
        <li class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
            <a href="../admin/users.php">
                <span class="material-icons">people</span>
                <span>Users</span>
            </a>
        </li>
        <?php endif; ?>
        <li class="<?php echo $currentPage === 'inventory.php' ? 'active' : ''; ?>">
            <a href="../admin/inventory.php">
                <span class="material-icons">inventory_2</span>
                <span>Inventory</span>
            </a>
        </li>
        <li class="<?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>">
            <a href="../admin/categories.php">
                <span class="material-icons">category</span>
                <span>Categories</span>
            </a>
        </li>
        <li class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
            <a href="../admin/reports.php">
                <span class="material-icons">assessment</span>
                <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="../../actions/logout.php">
                <span class="material-icons">logout</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>

<style>
.sidebar {
    width: 250px;
    height: 100%;
    background: var(--primary-color);
    padding: 20px 0;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
}

.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 500;
}

.nav-links {
    list-style: none;
    padding: 0;
    margin: 20px 0;
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

.nav-links li:last-child {
    margin-top: auto;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-links li:last-child a {
    color: rgba(255, 255, 255, 0.7);
}

.nav-links li:last-child a:hover {
    color: white;
}
</style>
