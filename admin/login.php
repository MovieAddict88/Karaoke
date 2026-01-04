<?php
session_start();
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Security.php';
require_once '../includes/Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn() && $auth->hasRole('admin')) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!Security::validateCSRFToken($csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            $result = $auth->login($username, $password);
            
            if ($result['success']) {
                if ($result['role'] === 'admin') {
                    header('Location: index.php');
                    exit;
                } else {
                    $auth->logout();
                    $error = 'Access denied. Admin privileges required.';
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VPN Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: clamp(1rem, 3vw, 2rem);
        }
        
        .login-box {
            background: white;
            border-radius: clamp(0.5rem, 2vw, 1rem);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: clamp(2rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2rem);
            text-align: center;
        }
        
        .login-header h1 {
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            font-size: clamp(0.875rem, 2vw, 1rem);
            opacity: 0.9;
        }
        
        .login-body {
            padding: clamp(1.5rem, 4vw, 2.5rem);
        }
        
        .login-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .login-btn {
            width: 100%;
            padding: clamp(0.75rem, 2vw, 1rem);
            margin-top: 1rem;
        }
        
        .login-footer {
            text-align: center;
            padding: clamp(1rem, 3vw, 1.5rem);
            border-top: 1px solid var(--border);
            font-size: clamp(0.875rem, 2vw, 0.9375rem);
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Admin Panel</h1>
                <p>VPN Management System</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary login-btn">Login</button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> VPN Management Panel. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
