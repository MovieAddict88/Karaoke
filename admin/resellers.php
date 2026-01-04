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
        
        if ($action === 'add_credit') {
            $resellerId = intval($_POST['reseller_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            
            if ($amount <= 0) {
                $error = 'Amount must be greater than 0';
            } else {
                try {
                    $db->beginTransaction();
                    
                    $reseller = $db->fetchOne("SELECT balance FROM users WHERE id = ? AND role = 'reseller'", [$resellerId]);
                    
                    if ($reseller) {
                        $newBalance = $reseller['balance'] + $amount;
                        
                        $db->update('users', ['balance' => $newBalance], 'id = :id', ['id' => $resellerId]);
                        
                        $db->insert('reseller_credits', [
                            'reseller_id' => $resellerId,
                            'amount' => $amount,
                            'type' => 'credit',
                            'description' => $description,
                            'balance_after' => $newBalance,
                            'created_by' => $user['id']
                        ]);
                        
                        $db->commit();
                        $success = 'Credit added successfully';
                    } else {
                        $error = 'Reseller not found';
                    }
                } catch (Exception $e) {
                    $db->rollback();
                    $error = 'Error adding credit';
                }
            }
        } elseif ($action === 'deduct_credit') {
            $resellerId = intval($_POST['reseller_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            
            if ($amount <= 0) {
                $error = 'Amount must be greater than 0';
            } else {
                try {
                    $db->beginTransaction();
                    
                    $reseller = $db->fetchOne("SELECT balance FROM users WHERE id = ? AND role = 'reseller'", [$resellerId]);
                    
                    if ($reseller) {
                        $newBalance = $reseller['balance'] - $amount;
                        
                        if ($newBalance < 0) {
                            $error = 'Insufficient balance';
                        } else {
                            $db->update('users', ['balance' => $newBalance], 'id = :id', ['id' => $resellerId]);
                            
                            $db->insert('reseller_credits', [
                                'reseller_id' => $resellerId,
                                'amount' => $amount,
                                'type' => 'debit',
                                'description' => $description,
                                'balance_after' => $newBalance,
                                'created_by' => $user['id']
                            ]);
                            
                            $db->commit();
                            $success = 'Credit deducted successfully';
                        }
                    } else {
                        $error = 'Reseller not found';
                    }
                } catch (Exception $e) {
                    $db->rollback();
                    $error = 'Error deducting credit';
                }
            }
        }
    }
}

$resellers = $db->fetchAll("
    SELECT u.*, 
    (SELECT COUNT(*) FROM users WHERE reseller_id = u.id) as clients_count,
    (SELECT COUNT(*) FROM reseller_credits WHERE reseller_id = u.id) as transactions_count
    FROM users u
    WHERE u.role = 'reseller'
    ORDER BY u.created_at DESC
");

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resellers - Admin Panel</title>
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
                <h1 class="page-title">Reseller Management</h1>
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
                    <h2 class="card-title">Resellers</h2>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Reseller
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Balance</th>
                                    <th>Clients</th>
                                    <th>Transactions</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resellers as $reseller): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reseller['username']); ?></td>
                                    <td><?php echo htmlspecialchars($reseller['email']); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($reseller['balance'], 2); ?></td>
                                    <td><?php echo $reseller['clients_count']; ?></td>
                                    <td><?php echo $reseller['transactions_count']; ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $reseller['status'] === 'active' ? 'success' : ($reseller['status'] === 'suspended' ? 'danger' : 'warning');
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($reseller['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($reseller['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-success" onclick="addCredit(<?php echo $reseller['id']; ?>, '<?php echo htmlspecialchars($reseller['username']); ?>')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deductCredit(<?php echo $reseller['id']; ?>, '<?php echo htmlspecialchars($reseller['username']); ?>')">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <a href="reseller-details.php?id=<?php echo $reseller['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
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
    
    <!-- Add Credit Modal -->
    <div id="addCreditModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add Credit</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="add_credit">
                <input type="hidden" name="reseller_id" id="add_reseller_id">
                <div class="modal-body">
                    <p><strong>Reseller:</strong> <span id="add_reseller_name"></span></p>
                    
                    <div class="form-group">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" step="0.01" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-success">Add Credit</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Deduct Credit Modal -->
    <div id="deductCreditModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Deduct Credit</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="deduct_credit">
                <input type="hidden" name="reseller_id" id="deduct_reseller_id">
                <div class="modal-body">
                    <p><strong>Reseller:</strong> <span id="deduct_reseller_name"></span></p>
                    
                    <div class="form-group">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" step="0.01" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-danger">Deduct Credit</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function addCredit(resellerId, resellerName) {
            document.getElementById('add_reseller_id').value = resellerId;
            document.getElementById('add_reseller_name').textContent = resellerName;
            openModal('addCreditModal');
        }
        
        function deductCredit(resellerId, resellerName) {
            document.getElementById('deduct_reseller_id').value = resellerId;
            document.getElementById('deduct_reseller_name').textContent = resellerName;
            openModal('deductCreditModal');
        }
    </script>
</body>
</html>
