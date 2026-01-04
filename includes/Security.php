<?php

class Security
{
    public static function generateCSRFToken()
    {
        if (empty($_SESSION['csrf_token']) || time() > $_SESSION['csrf_token_time'] + CSRF_TOKEN_EXPIRE) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRFToken($token)
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        if (time() > $_SESSION['csrf_token_time'] + CSRF_TOKEN_EXPIRE) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
            return $data;
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    public static function getClientIP()
    {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    
    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    public static function generateRandomString($length = 16)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    public static function encryptData($data, $key)
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public static function decryptData($data, $key)
    {
        list($encrypted, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    public static function sanitizeFilename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        return $filename;
    }
    
    public static function validateFileUpload($file, $allowedExtensions = [])
    {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error';
            return ['valid' => false, 'errors' => $errors];
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds maximum allowed';
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedExtensions) && !in_array($ext, $allowedExtensions)) {
            $errors[] = 'File type not allowed';
        }
        
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'extension' => $ext
        ];
    }
}
