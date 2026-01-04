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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!Security::validateCSRFToken($csrfToken)) {
        $error = 'Invalid security token';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $username = Security::sanitizeInput($_POST['username'] ?? '');
            $email = Security::sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullName = Security::sanitizeInput($_POST['full_name'] ?? '');
            $role = Security::sanitizeInput($_POST['role'] ?? 'client');
            $status = Security::sanitizeInput($_POST['status'] ?? 'active');
            
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Please fill in all required fields';
            } elseif (!Security::validateEmail($email)) {
                $error = 'Invalid email address';
            } else {
                $result = $auth->register([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'full_name' => $fullName,
                    'role' => $role,
                    'status' => $status
                ]);
                
                if ($result['success']) {
                    $success = 'User added successfully';
                } else {
                    $error = $result['message'];
                }
            }
        } elseif ($action === 'edit') {
            $userId = intval($_POST['user_id'] ?? 0);
            $data = [
                'username' => Security::sanitizeInput($_POST['username'] ?? ''),
                'email' => Security::sanitizeInput($_POST['email'] ?? ''),
                'full_name' => Security::sanitizeInput($_POST['full_name'] ?? ''),
                'role' => Security::sanitizeInput($_POST['role'] ?? 'client'),
                'status' => Security::sanitizeInput($_POST['status'] ?? 'active')
            ];
            
            if (!empty($_POST['password'])) {
                $data['password'] = Security::hashPassword($_POST['password']);
            }
            
            try {
                $db->update('users', $data, 'id = :id', ['id' => $userId]);
                $success = 'User updated successfully';
            } catch (Exception $e) {
                $error = 'Error updating user';
            }
        } elseif ($action === 'delete') {
            $userId = intval($_POST['user_id'] ?? 0);
            
            if ($userId === $user['id']) {
                $error = 'You cannot delete your own account';
            } else {
                try {
                    $db->delete('users', 'id = :id', ['id' => $userId]);
                    $success = 'User deleted successfully';
                } catch (Exception $e) {
                    $error = 'Error deleting user';
                }
            }
        }
    }
}

$search = Security::sanitizeInput($_GET['search'] ?? '');
$roleFilter = Security::sanitizeInput($_GET['role'] ?? '');
$statusFilter = Security::sanitizeInput($_GET['status'] ?? '');

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
    $params['search'] = "%$search%";
}

if (!empty($roleFilter)) {
    $whereConditions[] = "role = :role";
    $params['role'] = $roleFilter;
}

if (!empty($statusFilter)) {
    $whereConditions[] = "status = :status";
    $params['status'] = $statusFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users $whereClause", $params)['count'];
$totalPages = ceil($totalUsers / $perPage);

$users = $db->fetchAll("
    SELECT * FROM users 
    $whereClause
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Panel</title>
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
                <h1 class="page-title">User Management</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Users</h2>
                    <button class="btn btn-primary" data-modal="addUserModal">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 mb-4" style="flex-wrap: wrap;">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                        <select name="role" class="form-select" style="width: auto;">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="reseller" <?php echo $roleFilter === 'reseller' ? 'selected' : ''; ?>>Reseller</option>
                            <option value="client" <?php echo $roleFilter === 'client' ? 'selected' : ''; ?>>Client</option>
                        </select>
                        <select name="status" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="suspended" <?php echo $statusFilter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="users.php" class="btn btn-secondary">Reset</a>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span></td>
                                    <td>
                                        <?php
                                        $statusClass = $u['status'] === 'active' ? 'success' : ($u['status'] === 'suspended' ? 'danger' : 'warning');
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($u['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($u['id'] !== $user['id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete="Are you sure you want to delete this user?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New User</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="client">Client</option>
                            <option value="reseller">Reseller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password (leave empty to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" id="edit_role" class="form-select">
                            <option value="client">Client</option>
                            <option value="reseller">Reseller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_full_name').value = user.full_name || '';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            
            openModal('editUserModal');
        }
    </script>
</body>
</html>
