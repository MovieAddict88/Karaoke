<?php
// Simple Installer Script

$install_lock = 'install.lock';
if (file_exists($install_lock)) {
    die("<h2>Installation Complete</h2><p>The application is already installed. For security, please <strong>delete the install.php file</strong> from your server.</p>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $db_host = trim($_POST['db_host']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_pass']);
    $db_name = trim($_POST['db_name']);
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];
    $url_root = rtrim(trim($_POST['url_root']), '/');

    // --- Step 1: Validate Input ---
    if (empty($db_host) || empty($db_user) || empty($db_name) || empty($admin_name) || empty($admin_email) || empty($admin_password) || empty($url_root)) {
        $error = "Please fill in all required fields.";
    }

    // --- Step 2: Update config.php ---
    if(empty($error)){
        $config_file = 'config/config.php';
        if (is_writable($config_file)) {
            $config_content = file_get_contents($config_file);
            $config_content = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '$db_host');", $config_content);
            $config_content = preg_replace("/define\('DB_USER', '.*?'\);/", "define('DB_USER', '$db_user');", $config_content);
            $config_content = preg_replace("/define\('DB_PASS', '.*?'\);/", "define('DB_PASS', '$db_pass');", $config_content);
            $config_content = preg_replace("/define\('DB_NAME', '.*?'\);/", "define('DB_NAME', '$db_name');", $config_content);
            $config_content = preg_replace("/define\('URLROOT', '.*?'\);/", "define('URLROOT', '$url_root');", $config_content);
            file_put_contents($config_file, $config_content);
        } else {
            $error = "Config file (config/config.php) is not writable. Please check file permissions and try again.";
        }
    }

    if (empty($error)) {
        // --- Step 3: Connect to MySQL and Create Database ---
        try {
            $dbh = new PDO("mysql:host=$db_host", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $dbh->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $dbh->exec("USE `$db_name`;");

            // --- Step 4: Import SQL File ---
            $sql_file = 'database/database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $dbh->exec($sql);
            } else {
                throw new Exception("Database schema file (database/database.sql) not found.");
            }

            // --- Step 5: Create Admin User ---
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $dbh->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'admin')");
            $stmt->bindParam(':name', $admin_name);
            $stmt->bindParam(':email', $admin_email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            // --- Step 6: Finalize Installation ---
            file_put_contents($install_lock, 'installed on ' . date('Y-m-d H:i:s'));
            $success = "Installation successful! The application is ready to use.";

        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage() . "<br><br>Please check your database credentials and ensure the user has privileges to create a database.";
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
} else {
    // Pre-fill URL Root
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_path = str_replace('/install.php', '', $_SERVER['SCRIPT_NAME']);
    $prefilled_url_root = rtrim("$protocol://$host$script_path", '/');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Manager Installer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .installer-container { max-width: 700px; margin: 50px auto; }
    </style>
</head>
<body>
<div class="container installer-container">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h2>Student Attendance Manager Installation</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <a href="install.php" class="btn btn-primary">Try Again</a>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <p class="text-danger"><strong>For security reasons, please delete the <code>install.php</code> file from your server immediately.</strong></p>
                <a href="<?php echo htmlspecialchars($url_root); ?>" class="btn btn-primary">Go to Homepage</a>
            <?php else: ?>
                <form action="install.php" method="post">
                    <h4>1. Database Configuration</h4>
                    <p class="text-muted">Enter your database connection details. The database user must have permission to create a new database.</p>
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="student_attendance" required>
                    </div>
                    <div class="form-group">
                        <label for="db_user">Database User</label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" name="db_pass" class="form-control">
                    </div>
                    <hr>
                    <h4>2. Site Configuration</h4>
                     <div class="form-group">
                        <label for="url_root">URL Root</label>
                        <input type="text" name="url_root" class="form-control" value="<?php echo htmlspecialchars($prefilled_url_root); ?>" required>
                        <small class="form-text text-muted">This is the main URL of your application. No trailing slash.</small>
                    </div>
                    <hr>
                    <h4>3. Admin Account Setup</h4>
                    <p class="text-muted">Create the first administrator account.</p>
                     <div class="form-group">
                        <label for="admin_name">Admin Name</label>
                        <input type="text" name="admin_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" name="admin_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" name="admin_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg btn-block">Install Now</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>