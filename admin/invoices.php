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
        
        if ($action === 'create') {
            $userId = intval($_POST['user_id'] ?? 0);
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $tax = floatval($_POST['tax'] ?? 0);
            $dueDate = Security::sanitizeInput($_POST['due_date'] ?? '');
            
            if ($amount <= 0) {
                $error = 'Amount must be greater than 0';
            } else {
                $total = $amount + $tax;
                $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                
                try {
                    $invoiceId = $db->insert('invoices', [
                        'invoice_number' => $invoiceNumber,
                        'user_id' => $userId,
                        'amount' => $amount,
                        'tax' => $tax,
                        'total' => $total,
                        'description' => $description,
                        'due_date' => $dueDate,
                        'status' => 'sent'
                    ]);
                    
                    $success = "Invoice {$invoiceNumber} created successfully";
                } catch (Exception $e) {
                    $error = 'Error creating invoice';
                }
            }
        } elseif ($action === 'update_status') {
            $invoiceId = intval($_POST['invoice_id'] ?? 0);
            $status = Security::sanitizeInput($_POST['status'] ?? '');
            
            try {
                $db->update('invoices', ['status' => $status], 'id = :id', ['id' => $invoiceId]);
                $success = 'Invoice status updated successfully';
            } catch (Exception $e) {
                $error = 'Error updating invoice';
            }
        } elseif ($action === 'delete') {
            $invoiceId = intval($_POST['invoice_id'] ?? 0);
            
            try {
                $db->delete('invoices', 'id = :id', ['id' => $invoiceId]);
                $success = 'Invoice deleted successfully';
            } catch (Exception $e) {
                $error = 'Error deleting invoice';
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
    $whereClause = 'WHERE i.status = :status';
    $params['status'] = $statusFilter;
}

$totalInvoices = $db->fetchOne("SELECT COUNT(*) as count FROM invoices i $whereClause", $params)['count'];
$totalPages = ceil($totalInvoices / $perPage);

$invoices = $db->fetchAll("
    SELECT i.*, u.username, u.email 
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    $whereClause
    ORDER BY i.created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

$users = $db->fetchAll("SELECT id, username, email FROM users WHERE role IN ('client', 'reseller') AND status = 'active' ORDER BY username");

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - Admin Panel</title>
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
                <h1 class="page-title">Invoice Management</h1>
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
                    <h2 class="card-title">Invoices</h2>
                    <button class="btn btn-primary" data-modal="createInvoiceModal">
                        <i class="fas fa-plus"></i> Create Invoice
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 mb-4">
                        <select name="status" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Sent</option>
                            <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="overdue" <?php echo $statusFilter === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="invoices.php" class="btn btn-secondary">Reset</a>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                    <td><?php echo htmlspecialchars($invoice['username']); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($invoice['tax'], 2); ?></td>
                                    <td><strong><?php echo CURRENCY_SYMBOL . number_format($invoice['total'], 2); ?></strong></td>
                                    <td><?php echo $invoice['due_date'] ? date('M d, Y', strtotime($invoice['due_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'warning'
                                        ];
                                        $statusClass = $statusColors[$invoice['status']] ?? 'secondary';
                                        echo "<span class='badge badge-$statusClass'>" . ucfirst($invoice['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="invoice-view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $invoice['id']; ?>, 'paid')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" data-confirm-delete="Delete this invoice?">
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
    
    <!-- Create Invoice Modal -->
    <div id="createInvoiceModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Create Invoice</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="create">
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
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" step="0.01" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tax</label>
                        <input type="number" name="tax" step="0.01" class="form-control" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
    
    <form id="statusUpdateForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="invoice_id" id="status_invoice_id">
        <input type="hidden" name="status" id="status_value">
    </form>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function updateStatus(invoiceId, status) {
            if (confirm('Update invoice status to ' + status + '?')) {
                document.getElementById('status_invoice_id').value = invoiceId;
                document.getElementById('status_value').value = status;
                document.getElementById('statusUpdateForm').submit();
            }
        }
    </script>
</body>
</html>
