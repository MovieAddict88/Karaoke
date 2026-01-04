<aside class="sidebar">
    <div class="sidebar-header">
        <h2>VPN Panel</h2>
        <p>Reseller Dashboard</p>
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
                <a href="clients.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>My Clients</span>
                </a>
            </li>
            <li>
                <a href="servers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'servers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-server"></i>
                    <span>Servers</span>
                </a>
            </li>
            <li>
                <a href="invoices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>Invoices</span>
                </a>
            </li>
            <li>
                <a href="transactions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transactions</span>
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
