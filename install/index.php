<?php
session_start();

if (file_exists('../config/config.php')) {
    die('Installation already completed. Delete config/config.php to reinstall.');
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        $dbHost = $_POST['db_host'] ?? '';
        $dbName = $_POST['db_name'] ?? '';
        $dbUser = $_POST['db_user'] ?? '';
        $dbPass = $_POST['db_pass'] ?? '';
        
        try {
            $conn = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn->exec("USE `$dbName`");
            
            $sql = file_get_contents('../config/database.sql');
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $conn->exec($statement);
                }
            }
            
            $_SESSION['install_db'] = [
                'host' => $dbHost,
                'name' => $dbName,
                'user' => $dbUser,
                'pass' => $dbPass
            ];
            
            $success[] = 'Database created successfully';
            header('Location: ?step=3');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
    
    if ($step == 3) {
        $adminUsername = $_POST['admin_username'] ?? '';
        $adminEmail = $_POST['admin_email'] ?? '';
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminConfirm = $_POST['admin_confirm'] ?? '';
        
        if (empty($adminUsername) || empty($adminEmail) || empty($adminPassword)) {
            $errors[] = 'All fields are required';
        } elseif ($adminPassword !== $adminConfirm) {
            $errors[] = 'Passwords do not match';
        } elseif (strlen($adminPassword) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } else {
            try {
                $dbConfig = $_SESSION['install_db'];
                $conn = new PDO(
                    "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}",
                    $dbConfig['user'],
                    $dbConfig['pass']
                );
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
                $stmt->execute([$adminUsername, $adminEmail, $hashedPassword, 'Administrator']);
                
                $configContent = "<?php\n";
                $configContent .= "// Database Configuration\n";
                $configContent .= "define('DB_HOST', '{$dbConfig['host']}');\n";
                $configContent .= "define('DB_NAME', '{$dbConfig['name']}');\n";
                $configContent .= "define('DB_USER', '{$dbConfig['user']}');\n";
                $configContent .= "define('DB_PASS', '{$dbConfig['pass']}');\n";
                $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
                $configContent .= file_get_contents('../config/config.sample.php');
                $configContent = str_replace('<?php', '', $configContent);
                $configContent = "<?php\n" . $configContent;
                
                file_put_contents('../config/config.php', $configContent);
                
                chmod('../config/config.php', 0644);
                
                unset($_SESSION['install_db']);
                
                header('Location: ?step=4');
                exit;
                
            } catch (PDOException $e) {
                $errors[] = 'Error creating admin user: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Panel Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 3vw, 2rem);
        }
        
        .install-container {
            background: white;
            border-radius: clamp(0.5rem, 2vw, 1rem);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            text-align: center;
        }
        
        .install-header h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            margin-bottom: 0.5rem;
        }
        
        .install-header p {
            font-size: clamp(0.875rem, 2vw, 1rem);
            opacity: 0.9;
        }
        
        .install-body {
            padding: clamp(1.5rem, 4vw, 2.5rem);
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .steps::before {
            content: '';
            position: absolute;
            top: 1.25rem;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        
        .step {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }
        
        .step.active {
            background: #667eea;
            color: white;
        }
        
        .step.completed {
            background: #10b981;
            color: white;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: clamp(0.875rem, 2vw, 1rem);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: clamp(0.625rem, 2vw, 0.75rem);
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            font-size: clamp(0.875rem, 2vw, 1rem);
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: clamp(0.75rem, 2vw, 1rem) clamp(1.5rem, 4vw, 2rem);
            border-radius: 0.5rem;
            font-size: clamp(0.875rem, 2vw, 1rem);
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: clamp(0.875rem, 2vw, 1rem);
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #10b981;
            text-align: center;
            margin: 2rem 0;
        }
        
        @media (max-width: 480px) {
            .steps {
                margin-bottom: 1.5rem;
            }
            
            .step {
                width: 2rem;
                height: 2rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>VPN Panel Installation</h1>
            <p>Complete these steps to set up your VPN management system</p>
        </div>
        
        <div class="install-body">
            <div class="steps">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">3</div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <?php foreach ($success as $msg): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <h2>Welcome</h2>
                <p style="margin: 1.5rem 0;">Welcome to the VPN Management Panel installation wizard. This process will:</p>
                <ul style="margin-left: 1.5rem; margin-bottom: 2rem; line-height: 1.8;">
                    <li>Set up your database</li>
                    <li>Create necessary tables</li>
                    <li>Create an administrator account</li>
                    <li>Configure system settings</li>
                </ul>
                <p style="margin-bottom: 2rem;">Click Next to begin the installation process.</p>
                <a href="?step=2" class="btn">Next →</a>
            <?php endif; ?>
            
            <?php if ($step == 2): ?>
                <h2>Database Configuration</h2>
                <p style="margin: 1rem 0 2rem;">Please provide your MySQL database credentials.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Database Host</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Database Name</label>
                        <input type="text" name="db_name" value="vpn_panel" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Database Username</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_pass">
                    </div>
                    
                    <button type="submit" class="btn">Create Database →</button>
                </form>
            <?php endif; ?>
            
            <?php if ($step == 3): ?>
                <h2>Administrator Account</h2>
                <p style="margin: 1rem 0 2rem;">Create your administrator account.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="admin_username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="admin_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="admin_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="admin_confirm" required>
                    </div>
                    
                    <button type="submit" class="btn">Create Account →</button>
                </form>
            <?php endif; ?>
            
            <?php if ($step == 4): ?>
                <div class="success-icon">✓</div>
                <h2 style="text-align: center; margin-bottom: 1rem;">Installation Complete!</h2>
                <p style="text-align: center; margin-bottom: 2rem;">Your VPN Management Panel has been successfully installed.</p>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem;">Next Steps:</h3>
                    <ol style="margin-left: 1.5rem; line-height: 1.8;">
                        <li>Delete the <code>/install</code> directory for security</li>
                        <li>Log in to the admin panel</li>
                        <li>Configure your system settings</li>
                        <li>Add VPN servers</li>
                    </ol>
                </div>
                <a href="../admin/login.php" class="btn">Go to Admin Login →</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
