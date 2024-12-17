<div class="sidebar-menu">
    <ul>
        <li class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
            <a href="../admin/dashboard.php">
                <span class="material-icons">dashboard</span>
                Dashboard
            </a>
        </li>
        <li class="<?= (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : '' ?>">
            <a href="../admin/users.php">
                <span class="material-icons">people</span>
                User Management
            </a>
        </li>
        <li class="<?= (basename($_SERVER['PHP_SELF']) == 'inventory.php') ? 'active' : '' ?>">
            <a href="../admin/inventory.php">
                <span class="material-icons">inventory</span>
                Inventory
            </a>
        </li>
        <li>
            <a href="../logout.php">
                <span class="material-icons">logout</span>
                Logout
            </a>
        </li>
    </ul>
</div>
