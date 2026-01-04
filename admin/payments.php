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
            $userId = intval($_POST['user_id'] ?? 0);
            $invoiceId = intval($_POST['invoice_id'] ?? 0) ?: null;
            $amount = floatval($_POST['amount'] ?? 0);
            $paymentMethod = Security::sanitizeInput($_POST['payment_method'] ?? '');
            $notes = Security::sanitizeInput($_POST['notes'] ?? '');
            
            if ($amount <= 0) {
                $error = 'Amount must be greater than 0';
            } else {
                $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
                
                try {
                    $db->beginTransaction();
                    
                    $db->insert('payments', [
                        'transaction_id' => $transactionId,
                        'user_id' => $userId,
                        'invoice_id' => $invoiceId,
                        'amount' => $amount,
                        'payment_method' => $paymentMethod,
                        'status' => 'completed',
                        'notes' => $notes
                    ]);
                    
                    if ($invoiceId) {
                        $db->update('invoices', ['status' => 'paid'], 'id = :id', ['id' => $invoiceId]);
                    }
                    
                    $db->commit();
                    $success = 'Payment recorded successfully';
                } catch (Exception $e) {
                    $db->rollback();
                    $error = 'Error recording payment';
                }
            }
        }
    }
}

$statusFilter = Security::sanitizeInput($_GET['status'] ?? '');

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$whereClause = '';
$params = [];

if (!empty($statusFilter)) {
    $whereClause = 'WHERE p.status = :status';
    $params['status'] = $statusFilter;
}

$totalPayments = $db->fetchOne("SELECT COUNT(*) as count FROM payments p $whereClause", $params)['count'];
$totalPages = ceil($totalPayments / $perPage);

$payments = $db->fetchAll("
    SELECT p.*, u.username, i.invoice_number 
    FROM payments p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN invoices i ON p.invoice_id = i.id
    $whereClause
    ORDER BY p.created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

$users = $db->fetchAll("SELECT id, username, email FROM users WHERE status = 'active' ORDER BY username");
$pendingInvoices = $db->fetchAll("SELECT id, invoice_number, user_id FROM invoices WHERE status != 'paid' ORDER BY created_at DESC");

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Admin Panel</title>
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
                <h1 class="page-title">Payment Management</h1>
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
                    <h2 class="card-title">Payment Transactions</h2>
                    <button class="btn btn-primary" data-modal="addPaymentModal">
                        <i class="fas fa-plus"></i> Record Payment
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 mb-4">
                        <select name="status" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="payments.php" class="btn btn-secondary">Reset</a>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>User</th>
                                    <th>Invoice</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                    <td><?php echo $payment['invoice_number'] ? htmlspecialchars($payment['invoice_number']) : 'N/A'; ?></td>
                                    <td><strong><?php echo CURRENCY_SYMBOL . number_format($payment['amount'], 2); ?></strong></td>
                                    <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'refunded' => 'info'
                                        ];
                                        $statusClass = $statusColors[$payment['status']] ?? 'secondary';
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($payment['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
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
    
    <!-- Add Payment Modal -->
    <div id="addPaymentModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Record Payment</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">User *</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username'] . ' (' . $u['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Invoice (Optional)</label>
                        <select name="invoice_id" class="form-select">
                            <option value="">No Invoice</option>
                            <?php foreach ($pendingInvoices as $inv): ?>
                                <option value="<?php echo $inv['id']; ?>"><?php echo htmlspecialchars($inv['invoice_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" step="0.01" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Cryptocurrency">Cryptocurrency</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
