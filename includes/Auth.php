<?php

class Auth
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password)
    {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$username, $username]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
            return ['success' => false, 'message' => 'Account temporarily locked. Try again later.'];
        }
        
        if (!Security::verifyPassword($password, $user['password'])) {
            $this->incrementLoginAttempts($user['id']);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is not active'];
        }
        
        $this->resetLoginAttempts($user['id']);
        $this->updateLastLogin($user['id']);
        $this->createSession($user);
        $this->logActivity($user['id'], 'login', 'User logged in successfully');
        
        return ['success' => true, 'role' => $user['role']];
    }
    
    public function register($data)
    {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (!Security::validateEmail($data['email'])) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$data['username'], $data['email']]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Security::hashPassword($data['password']),
            'full_name' => $data['full_name'] ?? '',
            'role' => $data['role'] ?? 'client',
            'status' => $data['status'] ?? 'pending'
        ];
        
        try {
            $userId = $this->db->insert('users', $userData);
            $this->logActivity($userId, 'register', 'New user registered');
            return ['success' => true, 'user_id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_unset();
        session_destroy();
        return true;
    }
    
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }
    
    public function getUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }
    
    public function hasRole($role)
    {
        return $this->isLoggedIn() && $_SESSION['role'] === $role;
    }
    
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public function requireRole($role)
    {
        $this->requireLogin();
        
        if (!$this->hasRole($role)) {
            header('Location: /unauthorized.php');
            exit;
        }
    }
    
    private function createSession($user)
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['login_time'] = time();
    }
    
    private function incrementLoginAttempts($userId)
    {
        $this->db->query(
            "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?",
            [$userId]
        );
        
        $user = $this->db->fetchOne("SELECT login_attempts FROM users WHERE id = ?", [$userId]);
        
        if ($user['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
            $this->db->query(
                "UPDATE users SET lockout_until = ? WHERE id = ?",
                [$lockoutTime, $userId]
            );
        }
    }
    
    private function resetLoginAttempts($userId)
    {
        $this->db->query(
            "UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?",
            [$userId]
        );
    }
    
    private function updateLastLogin($userId)
    {
        $this->db->query(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$userId]
        );
    }
    
    private function logActivity($userId, $action, $description)
    {
        $this->db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => Security::getClientIP(),
            'user_agent' => Security::getUserAgent()
        ]);
    }
}
