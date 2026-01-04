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

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$statusFilter = Security::sanitizeInput($_GET['status'] ?? '');

$whereClause = '';
$params = [];

if (!empty($statusFilter)) {
    $whereClause = 'WHERE c.status = :status';
    $params['status'] = $statusFilter;
}

$totalConnections = $db->fetchOne("SELECT COUNT(*) as count FROM connections c $whereClause", $params)['count'];
$totalPages = ceil($totalConnections / $perPage);

$connections = $db->fetchAll("
    SELECT c.*, u.username, s.name as server_name, s.ip_address 
    FROM connections c 
    JOIN users u ON c.user_id = u.id 
    JOIN servers s ON c.server_id = s.id 
    $whereClause
    ORDER BY c.connected_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connections - Admin Panel</title>
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
                <h1 class="page-title">Connection Logs</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">VPN Connections</h2>
                </div>
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 mb-4">
                        <select name="status" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="connected" <?php echo $statusFilter === 'connected' ? 'selected' : ''; ?>>Connected</option>
                            <option value="disconnected" <?php echo $statusFilter === 'disconnected' ? 'selected' : ''; ?>>Disconnected</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="connections.php" class="btn btn-secondary">Reset</a>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Server</th>
                                    <th>Server IP</th>
                                    <th>Client IP</th>
                                    <th>Connected At</th>
                                    <th>Disconnected At</th>
                                    <th>Duration</th>
                                    <th>Data Transfer</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($connections as $conn): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($conn['username']); ?></td>
                                    <td><?php echo htmlspecialchars($conn['server_name']); ?></td>
                                    <td><?php echo htmlspecialchars($conn['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($conn['ip_address']); ?></td>
                                    <td><?php echo date('M d, Y H:i:s', strtotime($conn['connected_at'])); ?></td>
                                    <td><?php echo $conn['disconnected_at'] ? date('M d, Y H:i:s', strtotime($conn['disconnected_at'])) : 'N/A'; ?></td>
                                    <td>
                                        <?php 
                                        if ($conn['duration'] > 0) {
                                            $hours = floor($conn['duration'] / 3600);
                                            $minutes = floor(($conn['duration'] % 3600) / 60);
                                            echo "{$hours}h {$minutes}m";
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $totalBytes = $conn['bytes_sent'] + $conn['bytes_received'];
                                        if ($totalBytes > 0) {
                                            if ($totalBytes > 1073741824) {
                                                echo number_format($totalBytes / 1073741824, 2) . ' GB';
                                            } elseif ($totalBytes > 1048576) {
                                                echo number_format($totalBytes / 1048576, 2) . ' MB';
                                            } else {
                                                echo number_format($totalBytes / 1024, 2) . ' KB';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
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
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>" class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
