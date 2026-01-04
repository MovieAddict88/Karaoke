<aside class="sidebar">
    <div class="sidebar-header">
        <h2>VPN Panel</h2>
        <p>Admin Dashboard</p>
    </div>
    
    <nav>
        <ul class="sidebar-nav">
            <li>
                <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="servers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'servers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-server"></i>
                    <span>Servers</span>
                </a>
            </li>
            <li>
                <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="resellers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'resellers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Resellers</span>
                </a>
            </li>
            <li>
                <a href="connections.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'connections.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plug"></i>
                    <span>Connections</span>
                </a>
            </li>
            <li>
                <a href="invoices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>Invoices</span>
                </a>
            </li>
            <li>
                <a href="payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
