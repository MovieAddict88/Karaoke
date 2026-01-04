<?php
session_start();
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Security.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();
$user = $auth->getUser();

$userStats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN role = 'reseller' THEN 1 ELSE 0 END) as resellers,
        SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as clients,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users
    FROM users
");

$serverStats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_servers,
        SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_servers,
        SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_servers,
        SUM(current_connections) as total_connections,
        SUM(max_connections) as max_connections
    FROM servers
");

$financialStats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_invoices,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_invoices,
        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_invoices,
        SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_invoices,
        COALESCE(SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END), 0) as total_revenue,
        COALESCE(SUM(CASE WHEN status != 'paid' AND status != 'cancelled' THEN total ELSE 0 END), 0) as pending_revenue
    FROM invoices
");

$connectionStats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_connections,
        SUM(CASE WHEN status = 'connected' THEN 1 ELSE 0 END) as active_connections,
        SUM(duration) as total_duration,
        SUM(bytes_sent + bytes_received) as total_bandwidth
    FROM connections
");

$topServers = $db->fetchAll("
    SELECT s.name, s.ip_address, COUNT(c.id) as connection_count
    FROM servers s
    LEFT JOIN connections c ON s.id = c.server_id
    GROUP BY s.id
    ORDER BY connection_count DESC
    LIMIT 10
");

$topUsers = $db->fetchAll("
    SELECT u.username, u.email, COUNT(c.id) as connection_count
    FROM users u
    LEFT JOIN connections c ON u.id = c.user_id
    WHERE u.role = 'client'
    GROUP BY u.id
    ORDER BY connection_count DESC
    LIMIT 10
");

$recentActivity = $db->fetchAll("
    SELECT a.*, u.username 
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 15
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="topbar">
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">System Reports</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">User Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($userStats['total_users']); ?></div>
                            <div class="stat-card-label">Total Users</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($userStats['admins']); ?></div>
                            <div class="stat-card-label">Administrators</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($userStats['resellers']); ?></div>
                            <div class="stat-card-label">Resellers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($userStats['clients']); ?></div>
                            <div class="stat-card-label">Clients</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($userStats['active_users']); ?></div>
                            <div class="stat-card-label">Active Users</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($userStats['pending_users']); ?></div>
                            <div class="stat-card-label">Pending Approval</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Server Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($serverStats['total_servers']); ?></div>
                            <div class="stat-card-label">Total Servers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($serverStats['online_servers']); ?></div>
                            <div class="stat-card-label">Online Servers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($serverStats['total_connections']); ?></div>
                            <div class="stat-card-label">Current Connections</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($serverStats['max_connections']); ?></div>
                            <div class="stat-card-label">Max Capacity</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Financial Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo CURRENCY_SYMBOL . number_format($financialStats['total_revenue'], 2); ?></div>
                            <div class="stat-card-label">Total Revenue</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo CURRENCY_SYMBOL . number_format($financialStats['pending_revenue'], 2); ?></div>
                            <div class="stat-card-label">Pending Revenue</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($financialStats['paid_invoices']); ?></div>
                            <div class="stat-card-label">Paid Invoices</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo number_format($financialStats['overdue_invoices']); ?></div>
                            <div class="stat-card-label">Overdue Invoices</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(500px, 100%), 1fr)); gap: clamp(1rem, 3vw, 1.5rem);">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Top Servers by Connections</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Server Name</th>
                                        <th>IP Address</th>
                                        <th>Connections</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topServers as $server): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($server['name']); ?></td>
                                        <td><?php echo htmlspecialchars($server['ip_address']); ?></td>
                                        <td><?php echo number_format($server['connection_count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Top Users by Activity</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Connections</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topUsers as $topUser): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($topUser['username']); ?></td>
                                        <td><?php echo htmlspecialchars($topUser['email']); ?></td>
                                        <td><?php echo number_format($topUser['connection_count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Activity</h2>
                    <a href="logs.php" class="btn btn-primary btn-sm">View All Logs</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $activity): ?>
                                <tr>
                                    <td><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></td>
                                    <td><?php echo $activity['username'] ? htmlspecialchars($activity['username']) : 'System'; ?></td>
                                    <td>
                                        <?php
                                        $actionColors = [
                                            'login' => 'success',
                                            'logout' => 'info',
                                            'register' => 'primary',
                                            'create' => 'success',
                                            'update' => 'warning',
                                            'delete' => 'danger'
                                        ];
                                        $actionClass = $actionColors[$activity['action']] ?? 'secondary';
                                        echo "<span class='badge badge-$actionClass'>" . ucfirst($activity['action']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
