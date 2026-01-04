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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!Security::validateCSRFToken($csrfToken)) {
        $error = 'Invalid security token';
    } else {
        $settings = [
            'site_name' => Security::sanitizeInput($_POST['site_name'] ?? ''),
            'site_email' => Security::sanitizeInput($_POST['site_email'] ?? ''),
            'reseller_commission' => intval($_POST['reseller_commission'] ?? 0),
            'allow_registration' => isset($_POST['allow_registration']) ? '1' : '0',
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
            'currency' => Security::sanitizeInput($_POST['currency'] ?? 'USD'),
            'currency_symbol' => Security::sanitizeInput($_POST['currency_symbol'] ?? '$')
        ];
        
        try {
            foreach ($settings as $key => $value) {
                $db->query(
                    "UPDATE settings SET setting_value = ? WHERE setting_key = ?",
                    [$value, $key]
                );
            }
            
            $success = 'Settings updated successfully';
        } catch (Exception $e) {
            $error = 'Error updating settings';
        }
    }
}

$settingsData = [];
$settingsResult = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsResult as $setting) {
    $settingsData[$setting['setting_key']] = $setting['setting_value'];
}

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
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
                <h1 class="page-title">System Settings</h1>
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
                    <h2 class="card-title">General Settings</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settingsData['site_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Site Email</label>
                            <input type="email" name="site_email" class="form-control" value="<?php echo htmlspecialchars($settingsData['site_email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Reseller Commission (%)</label>
                            <input type="number" name="reseller_commission" class="form-control" value="<?php echo htmlspecialchars($settingsData['reseller_commission'] ?? '10'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                <option value="USD" <?php echo ($settingsData['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                <option value="EUR" <?php echo ($settingsData['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                <option value="GBP" <?php echo ($settingsData['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                <option value="JPY" <?php echo ($settingsData['currency'] ?? '') === 'JPY' ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                                <option value="CAD" <?php echo ($settingsData['currency'] ?? '') === 'CAD' ? 'selected' : ''; ?>>CAD - Canadian Dollar</option>
                                <option value="AUD" <?php echo ($settingsData['currency'] ?? '') === 'AUD' ? 'selected' : ''; ?>>AUD - Australian Dollar</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" value="<?php echo htmlspecialchars($settingsData['currency_symbol'] ?? '$'); ?>" maxlength="5">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="allow_registration" value="1" <?php echo ($settingsData['allow_registration'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                Allow User Registration
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($settingsData['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                Maintenance Mode
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">System Information</h2>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>MySQL Version:</strong></td>
                            <td><?php echo $db->fetchOne("SELECT VERSION() as version")['version']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server Software:</strong></td>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Application Version:</strong></td>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <td><strong>Max Upload Size:</strong></td>
                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max POST Size:</strong></td>
                            <td><?php echo ini_get('post_max_size'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Memory Limit:</strong></td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
