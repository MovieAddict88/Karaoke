<?php
session_start();
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Security.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->requireRole('reseller');

$db = Database::getInstance();
$user = $auth->getUser();

$stats = [
    'balance' => $user['balance'],
    'total_clients' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE reseller_id = ?", [$user['id']])['count'] ?? 0,
    'active_clients' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE reseller_id = ? AND status = 'active'", [$user['id']])['count'] ?? 0,
    'total_revenue' => $db->fetchOne("SELECT COALESCE(SUM(i.total), 0) as total FROM invoices i JOIN users u ON i.user_id = u.id WHERE u.reseller_id = ? AND i.status = 'paid'", [$user['id']])['total'] ?? 0
];

$recentClients = $db->fetchAll("SELECT * FROM users WHERE reseller_id = ? ORDER BY created_at DESC LIMIT 5", [$user['id']]);

$recentTransactions = $db->fetchAll("
    SELECT * FROM reseller_credits 
    WHERE reseller_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
", [$user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Dashboard - VPN Panel</title>
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
                            <div class="stat-card-value"><?php echo CURRENCY_SYMBOL . number_format($stats['balance'], 2); ?></div>
                            <div class="stat-card-label">Current Balance</div>
                        </div>
                        <div class="stat-card-icon success">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['total_clients']); ?></div>
                            <div class="stat-card-label">Total Clients</div>
                        </div>
                        <div class="stat-card-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo number_format($stats['active_clients']); ?></div>
                            <div class="stat-card-label">Active Clients</div>
                        </div>
                        <div class="stat-card-icon success">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value"><?php echo CURRENCY_SYMBOL . number_format($stats['total_revenue'], 2); ?></div>
                            <div class="stat-card-label">Total Revenue</div>
                        </div>
                        <div class="stat-card-icon info">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Clients</h2>
                    <a href="clients.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentClients as $client): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($client['username']); ?></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $client['status'] === 'active' ? 'success' : ($client['status'] === 'suspended' ? 'danger' : 'warning');
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($client['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Transactions</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $typeClass = $transaction['type'] === 'credit' ? 'success' : 'danger';
                                        echo "<span class='badge badge-$typeClass'>" . ucfirst($transaction['type']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo ($transaction['type'] === 'credit' ? '+' : '-') . CURRENCY_SYMBOL . number_format($transaction['amount'], 2); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($transaction['balance_after'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
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
