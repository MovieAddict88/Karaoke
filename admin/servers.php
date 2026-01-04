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
        
        if ($action === 'add' || $action === 'edit') {
            $data = [
                'name' => Security::sanitizeInput($_POST['name'] ?? ''),
                'hostname' => Security::sanitizeInput($_POST['hostname'] ?? ''),
                'ip_address' => Security::sanitizeInput($_POST['ip_address'] ?? ''),
                'port' => intval($_POST['port'] ?? 0),
                'protocol_id' => intval($_POST['protocol_id'] ?? 0),
                'country' => Security::sanitizeInput($_POST['country'] ?? ''),
                'city' => Security::sanitizeInput($_POST['city'] ?? ''),
                'max_connections' => intval($_POST['max_connections'] ?? 100),
                'bandwidth_limit' => intval($_POST['bandwidth_limit'] ?? 0),
                'username' => Security::sanitizeInput($_POST['server_username'] ?? ''),
                'password' => $_POST['server_password'] ?? '',
                'status' => Security::sanitizeInput($_POST['status'] ?? 'online')
            ];
            
            if (empty($data['name']) || empty($data['ip_address']) || empty($data['port'])) {
                $error = 'Please fill in all required fields';
            } elseif (!Security::validateIP($data['ip_address'])) {
                $error = 'Invalid IP address';
            } else {
                $configFile = '';
                $configFilePath = '';
                
                if (isset($_FILES['config_file']) && $_FILES['config_file']['error'] === UPLOAD_ERR_OK) {
                    $validation = Security::validateFileUpload($_FILES['config_file'], ALLOWED_EXTENSIONS);
                    
                    if ($validation['valid']) {
                        $fileName = Security::sanitizeFilename($_FILES['config_file']['name']);
                        $uniqueName = time() . '_' . $fileName;
                        $uploadPath = UPLOAD_DIR . $uniqueName;
                        
                        if (move_uploaded_file($_FILES['config_file']['tmp_name'], $uploadPath)) {
                            $configFile = file_get_contents($uploadPath);
                            $configFilePath = $uniqueName;
                        }
                    } else {
                        $error = implode(', ', $validation['errors']);
                    }
                }
                
                if (empty($error)) {
                    try {
                        if ($action === 'add') {
                            $data['config_file'] = $configFile;
                            $data['config_file_path'] = $configFilePath;
                            $data['created_by'] = $user['id'];
                            
                            $db->insert('servers', $data);
                            $success = 'Server added successfully';
                        } else {
                            $serverId = intval($_POST['server_id'] ?? 0);
                            
                            if (!empty($configFile)) {
                                $data['config_file'] = $configFile;
                                $data['config_file_path'] = $configFilePath;
                            }
                            
                            $db->update('servers', $data, 'id = :id', ['id' => $serverId]);
                            $success = 'Server updated successfully';
                        }
                    } catch (Exception $e) {
                        $error = 'Database error: ' . $e->getMessage();
                    }
                }
            }
        } elseif ($action === 'delete') {
            $serverId = intval($_POST['server_id'] ?? 0);
            
            try {
                $server = $db->fetchOne("SELECT config_file_path FROM servers WHERE id = ?", [$serverId]);
                
                if ($server && !empty($server['config_file_path'])) {
                    $filePath = UPLOAD_DIR . $server['config_file_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                $db->delete('servers', 'id = :id', ['id' => $serverId]);
                $success = 'Server deleted successfully';
            } catch (Exception $e) {
                $error = 'Error deleting server: ' . $e->getMessage();
            }
        }
    }
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalServers = $db->fetchOne("SELECT COUNT(*) as count FROM servers")['count'];
$totalPages = ceil($totalServers / $perPage);

$servers = $db->fetchAll("
    SELECT s.*, p.name as protocol_name 
    FROM servers s 
    LEFT JOIN protocols p ON s.protocol_id = p.id 
    ORDER BY s.created_at DESC 
    LIMIT ? OFFSET ?
", [$perPage, $offset]);

$protocols = $db->fetchAll("SELECT * FROM protocols WHERE status = 'active' ORDER BY name");

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servers - Admin Panel</title>
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
                <h1 class="page-title">Server Management</h1>
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
                    <h2 class="card-title">VPN Servers</h2>
                    <button class="btn btn-primary" data-modal="addServerModal">
                        <i class="fas fa-plus"></i> Add Server
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>IP Address</th>
                                    <th>Port</th>
                                    <th>Protocol</th>
                                    <th>Location</th>
                                    <th>Connections</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servers as $server): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($server['name']); ?></td>
                                    <td><?php echo htmlspecialchars($server['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($server['port']); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($server['protocol_name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($server['city'] . ', ' . $server['country']); ?></td>
                                    <td><?php echo $server['current_connections'] . '/' . $server['max_connections']; ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $server['status'] === 'online' ? 'success' : ($server['status'] === 'offline' ? 'danger' : 'warning');
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($server['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-primary" onclick="editServer(<?php echo htmlspecialchars(json_encode($server)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="server_id" value="<?php echo $server['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete="Are you sure you want to delete this server?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
                            <a href="?page=<?php echo $i; ?>" class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Server Modal -->
    <div id="addServerModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Server</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Server Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Hostname</label>
                        <input type="text" name="hostname" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">IP Address *</label>
                        <input type="text" name="ip_address" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Port *</label>
                        <input type="number" name="port" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Protocol *</label>
                        <select name="protocol_id" class="form-select" required>
                            <option value="">Select Protocol</option>
                            <?php foreach ($protocols as $protocol): ?>
                                <option value="<?php echo $protocol['id']; ?>"><?php echo htmlspecialchars($protocol['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Max Connections</label>
                        <input type="number" name="max_connections" class="form-control" value="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bandwidth Limit (GB)</label>
                        <input type="number" name="bandwidth_limit" class="form-control" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="server_username" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="server_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Configuration File (.ovpn, .conf, etc.)</label>
                        <input type="file" name="config_file" class="form-control" accept=".ovpn,.conf,.key,.crt,.pem">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Server</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Server Modal -->
    <div id="editServerModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Server</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editServerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="server_id" id="edit_server_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Server Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Hostname</label>
                        <input type="text" name="hostname" id="edit_hostname" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">IP Address *</label>
                        <input type="text" name="ip_address" id="edit_ip_address" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Port *</label>
                        <input type="number" name="port" id="edit_port" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Protocol *</label>
                        <select name="protocol_id" id="edit_protocol_id" class="form-select" required>
                            <?php foreach ($protocols as $protocol): ?>
                                <option value="<?php echo $protocol['id']; ?>"><?php echo htmlspecialchars($protocol['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" id="edit_country" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="edit_city" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Max Connections</label>
                        <input type="number" name="max_connections" id="edit_max_connections" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bandwidth Limit (GB)</label>
                        <input type="number" name="bandwidth_limit" id="edit_bandwidth_limit" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="server_username" id="edit_username" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password (leave empty to keep current)</label>
                        <input type="password" name="server_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Configuration File (upload new to replace)</label>
                        <input type="file" name="config_file" class="form-control" accept=".ovpn,.conf,.key,.crt,.pem">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Server</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function editServer(server) {
            document.getElementById('edit_server_id').value = server.id;
            document.getElementById('edit_name').value = server.name;
            document.getElementById('edit_hostname').value = server.hostname || '';
            document.getElementById('edit_ip_address').value = server.ip_address;
            document.getElementById('edit_port').value = server.port;
            document.getElementById('edit_protocol_id').value = server.protocol_id;
            document.getElementById('edit_country').value = server.country || '';
            document.getElementById('edit_city').value = server.city || '';
            document.getElementById('edit_max_connections').value = server.max_connections;
            document.getElementById('edit_bandwidth_limit').value = server.bandwidth_limit;
            document.getElementById('edit_username').value = server.username || '';
            document.getElementById('edit_status').value = server.status;
            
            openModal('editServerModal');
        }
    </script>
</body>
</html>
