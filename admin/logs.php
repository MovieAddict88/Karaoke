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

$search = Security::sanitizeInput($_GET['search'] ?? '');
$actionFilter = Security::sanitizeInput($_GET['action'] ?? '');

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(u.username LIKE :search OR a.action LIKE :search OR a.description LIKE :search)";
    $params['search'] = "%$search%";
}

if (!empty($actionFilter)) {
    $whereConditions[] = "a.action = :action";
    $params['action'] = $actionFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$totalLogs = $db->fetchOne("SELECT COUNT(*) as count FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id $whereClause", $params)['count'];
$totalPages = ceil($totalLogs / $perPage);

$logs = $db->fetchAll("
    SELECT a.*, u.username 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    $whereClause
    ORDER BY a.created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Admin Panel</title>
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
                <h1 class="page-title">Activity Logs</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">System Activity</h2>
                </div>
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 mb-4" style="flex-wrap: wrap;">
                        <input type="text" name="search" class="form-control" placeholder="Search logs..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                        <select name="action" class="form-select" style="width: auto;">
                            <option value="">All Actions</option>
                            <option value="login" <?php echo $actionFilter === 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="logout" <?php echo $actionFilter === 'logout' ? 'selected' : ''; ?>>Logout</option>
                            <option value="register" <?php echo $actionFilter === 'register' ? 'selected' : ''; ?>>Register</option>
                            <option value="create" <?php echo $actionFilter === 'create' ? 'selected' : ''; ?>>Create</option>
                            <option value="update" <?php echo $actionFilter === 'update' ? 'selected' : ''; ?>>Update</option>
                            <option value="delete" <?php echo $actionFilter === 'delete' ? 'selected' : ''; ?>>Delete</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="logs.php" class="btn btn-secondary">Reset</a>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo $log['username'] ? htmlspecialchars($log['username']) : 'System'; ?></td>
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
                                        $actionClass = $actionColors[$log['action']] ?? 'secondary';
                                        echo "<span class='badge badge-$actionClass'>" . ucfirst($log['action']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&action=<?php echo urlencode($actionFilter); ?>" class="<?php echo $page === $i ? 'active' : ''; ?>">
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
