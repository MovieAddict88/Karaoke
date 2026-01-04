<?php
session_start();
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Security.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();

$stats = [
    'total_users' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'client'")['count'] ?? 0,
    'total_resellers' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'reseller'")['count'] ?? 0,
    'total_servers' => $db->fetchOne("SELECT COUNT(*) as count FROM servers")['count'] ?? 0,
    'active_connections' => $db->fetchOne("SELECT COUNT(*) as count FROM connections WHERE status = 'connected'")['count'] ?? 0,
    'total_revenue' => $db->fetchOne("SELECT COALESCE(SUM(total), 0) as total FROM invoices WHERE status = 'paid'")['total'] ?? 0,
    'pending_invoices' => $db->fetchOne("SELECT COUNT(*) as count FROM invoices WHERE status IN ('draft', 'sent')")['count'] ?? 0
];

$recentUsers = $db->fetchAll("SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recentConnections = $db->fetchAll("
    SELECT c.*, u.username, s.name as server_name 
    FROM connections c 
    JOIN users u ON c.user_id = u.id 
    JOIN servers s ON c.server_id = s.id 
    ORDER BY c.connected_at DESC 
    LIMIT 10
");

$user = $auth->getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VPN Panel</title>
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
                <h1 class="page-title">Dashboard</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="stat-card-label">Total Users</div>
                        </div>
                        <div class="stat-card-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['total_resellers']); ?></div>
                            <div class="stat-card-label">Total Resellers</div>
                        </div>
                        <div class="stat-card-icon success">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['total_servers']); ?></div>
                            <div class="stat-card-label">Total Servers</div>
                        </div>
                        <div class="stat-card-icon info">
                            <i class="fas fa-server"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['active_connections']); ?></div>
                            <div class="stat-card-label">Active Connections</div>
                        </div>
                        <div class="stat-card-icon warning">
                            <i class="fas fa-plug"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo CURRENCY_SYMBOL . number_format($stats['total_revenue'], 2); ?></div>
                            <div class="stat-card-label">Total Revenue</div>
                        </div>
                        <div class="stat-card-icon success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['pending_invoices']); ?></div>
                            <div class="stat-card-label">Pending Invoices</div>
                        </div>
                        <div class="stat-card-icon danger">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Users</h2>
                    <a href="users.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span></td>
                                    <td>
                                        <?php
                                        $statusClass = $u['status'] === 'active' ? 'success' : ($u['status'] === 'suspended' ? 'danger' : 'warning');
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($u['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Connections</h2>
                    <a href="connections.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Server</th>
                                    <th>IP Address</th>
                                    <th>Connected At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentConnections as $conn): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($conn['username']); ?></td>
                                    <td><?php echo htmlspecialchars($conn['server_name']); ?></td>
                                    <td><?php echo htmlspecialchars($conn['ip_address']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($conn['connected_at'])); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $conn['status'] === 'connected' ? 'success' : 'secondary';
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($conn['status']) . "</span>";
                                        ?>
                                    </td>
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
