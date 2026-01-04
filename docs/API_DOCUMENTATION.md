# VPN Management Panel - API Documentation

## Database Schema Reference

### Tables Overview

The VPN Management Panel uses the following database tables:

1. **users** - User accounts (admin, reseller, client)
2. **protocols** - VPN protocol definitions
3. **servers** - VPN server configurations
4. **connections** - Connection logs
5. **invoices** - Invoice records
6. **invoice_items** - Invoice line items
7. **payments** - Payment transactions
8. **reseller_credits** - Credit transactions
9. **settings** - System settings
10. **activity_logs** - System activity logs

---

## Table Schemas

### users

Stores all user accounts with role-based access control.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| username | VARCHAR(50) | Unique username |
| email | VARCHAR(100) | Unique email address |
| password | VARCHAR(255) | Hashed password (bcrypt) |
| full_name | VARCHAR(100) | User's full name |
| role | ENUM | 'admin', 'reseller', or 'client' |
| status | ENUM | 'active', 'suspended', or 'pending' |
| reseller_id | INT UNSIGNED | Parent reseller (for clients) |
| balance | DECIMAL(10,2) | Credit balance (for resellers) |
| last_login | DATETIME | Last login timestamp |
| login_attempts | INT | Failed login counter |
| lockout_until | DATETIME | Account lockout time |
| created_at | TIMESTAMP | Account creation time |
| updated_at | TIMESTAMP | Last update time |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (username, email)
- INDEX (role, status, reseller_id)

---

### protocols

VPN protocol definitions (OpenVPN, SSH, WireGuard, etc.).

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| name | VARCHAR(50) | Protocol name |
| code | VARCHAR(20) | Unique protocol code |
| description | TEXT | Protocol description |
| icon | VARCHAR(50) | Icon class name |
| status | ENUM | 'active' or 'inactive' |
| created_at | TIMESTAMP | Creation time |

**Default Protocols:**
- OpenVPN (ovpn)
- SSH Tunnel (ssh)
- WireGuard (wireguard)
- IKEv2/IPSec (ikev2)
- SSTP (sstp)

---

### servers

VPN server configurations and status.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| name | VARCHAR(100) | Server name |
| hostname | VARCHAR(255) | Server hostname |
| ip_address | VARCHAR(45) | Server IP (IPv4/IPv6) |
| port | INT UNSIGNED | Service port |
| protocol_id | INT UNSIGNED | Protocol reference |
| country | VARCHAR(50) | Server country |
| city | VARCHAR(50) | Server city |
| flag_icon | VARCHAR(100) | Country flag icon |
| max_connections | INT | Maximum simultaneous connections |
| current_connections | INT | Current active connections |
| bandwidth_limit | BIGINT UNSIGNED | Monthly bandwidth limit (bytes) |
| bandwidth_used | BIGINT UNSIGNED | Bandwidth used this month (bytes) |
| config_file | TEXT | Configuration file content |
| config_file_path | VARCHAR(255) | Uploaded file path |
| username | VARCHAR(100) | Server username |
| password | VARCHAR(255) | Server password (encrypted) |
| status | ENUM | 'online', 'offline', or 'maintenance' |
| created_by | INT UNSIGNED | Creator user ID |
| created_at | TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | Last update time |

**Foreign Keys:**
- protocol_id → protocols(id)
- created_by → users(id)

---

### connections

VPN connection logs and statistics.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| user_id | INT UNSIGNED | Connected user |
| server_id | INT UNSIGNED | Connected server |
| ip_address | VARCHAR(45) | Client IP address |
| connected_at | TIMESTAMP | Connection start time |
| disconnected_at | TIMESTAMP | Connection end time |
| duration | INT UNSIGNED | Connection duration (seconds) |
| bytes_sent | BIGINT UNSIGNED | Data sent (bytes) |
| bytes_received | BIGINT UNSIGNED | Data received (bytes) |
| status | ENUM | 'connected' or 'disconnected' |

**Foreign Keys:**
- user_id → users(id)
- server_id → servers(id)

---

### invoices

Invoice records for billing.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| invoice_number | VARCHAR(50) | Unique invoice number |
| user_id | INT UNSIGNED | Invoice recipient |
| amount | DECIMAL(10,2) | Subtotal amount |
| tax | DECIMAL(10,2) | Tax amount |
| total | DECIMAL(10,2) | Total amount |
| currency | VARCHAR(3) | Currency code (USD, EUR, etc.) |
| description | TEXT | Invoice description |
| due_date | DATE | Payment due date |
| status | ENUM | 'draft', 'sent', 'paid', 'overdue', 'cancelled' |
| created_at | TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | Last update time |

**Foreign Keys:**
- user_id → users(id)

---

### invoice_items

Line items for invoices.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| invoice_id | INT UNSIGNED | Parent invoice |
| description | VARCHAR(255) | Item description |
| quantity | INT | Item quantity |
| unit_price | DECIMAL(10,2) | Price per unit |
| total | DECIMAL(10,2) | Line item total |

**Foreign Keys:**
- invoice_id → invoices(id)

---

### payments

Payment transaction records.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| transaction_id | VARCHAR(100) | Unique transaction ID |
| user_id | INT UNSIGNED | Payer user |
| invoice_id | INT UNSIGNED | Related invoice (optional) |
| amount | DECIMAL(10,2) | Payment amount |
| currency | VARCHAR(3) | Currency code |
| payment_method | VARCHAR(50) | Payment method |
| status | ENUM | 'pending', 'completed', 'failed', 'refunded' |
| notes | TEXT | Payment notes |
| created_at | TIMESTAMP | Payment time |

**Foreign Keys:**
- user_id → users(id)
- invoice_id → invoices(id)

---

### reseller_credits

Reseller credit transaction history.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| reseller_id | INT UNSIGNED | Reseller user |
| amount | DECIMAL(10,2) | Transaction amount |
| type | ENUM | 'credit' or 'debit' |
| description | TEXT | Transaction description |
| balance_after | DECIMAL(10,2) | Balance after transaction |
| created_by | INT UNSIGNED | Transaction creator |
| created_at | TIMESTAMP | Transaction time |

**Foreign Keys:**
- reseller_id → users(id)
- created_by → users(id)

---

### settings

System-wide configuration settings.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| setting_key | VARCHAR(100) | Unique setting key |
| setting_value | TEXT | Setting value |
| setting_type | VARCHAR(20) | Value type (string, number, boolean) |
| updated_at | TIMESTAMP | Last update time |

**Default Settings:**
- site_name
- site_email
- reseller_commission
- allow_registration
- maintenance_mode
- currency
- currency_symbol

---

### activity_logs

System activity and audit logs.

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| user_id | INT UNSIGNED | Actor user (NULL for system) |
| action | VARCHAR(100) | Action type |
| description | TEXT | Action description |
| ip_address | VARCHAR(45) | Client IP address |
| user_agent | TEXT | Client user agent |
| created_at | TIMESTAMP | Action time |

**Foreign Keys:**
- user_id → users(id)

**Common Actions:**
- login, logout
- register
- create, update, delete
- connect, disconnect

---

## PHP Class Reference

### Database Class

**Location:** `/includes/Database.php`

Singleton database connection wrapper using PDO.

**Methods:**

```php
getInstance() // Get database instance
getConnection() // Get PDO connection
query($sql, $params) // Execute query with parameters
fetchOne($sql, $params) // Fetch single row
fetchAll($sql, $params) // Fetch all rows
insert($table, $data) // Insert record
update($table, $data, $where, $whereParams) // Update record
delete($table, $where, $params) // Delete record
beginTransaction() // Start transaction
commit() // Commit transaction
rollback() // Rollback transaction
```

**Example Usage:**

```php
$db = Database::getInstance();

// Fetch one
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [1]);

// Fetch all
$servers = $db->fetchAll("SELECT * FROM servers WHERE status = ?", ['online']);

// Insert
$userId = $db->insert('users', [
    'username' => 'john',
    'email' => 'john@example.com',
    'password' => Security::hashPassword('password')
]);

// Update
$db->update('users', ['status' => 'active'], 'id = :id', ['id' => 1]);

// Delete
$db->delete('servers', 'id = :id', ['id' => 5]);
```

---

### Security Class

**Location:** `/includes/Security.php`

Security utilities for input validation, CSRF protection, and encryption.

**Methods:**

```php
generateCSRFToken() // Generate CSRF token
validateCSRFToken($token) // Validate CSRF token
sanitizeInput($data) // Sanitize input data
validateEmail($email) // Validate email address
validateIP($ip) // Validate IP address
hashPassword($password) // Hash password (bcrypt)
verifyPassword($password, $hash) // Verify password
getClientIP() // Get client IP address
getUserAgent() // Get user agent
generateRandomString($length) // Generate random string
encryptData($data, $key) // Encrypt data
decryptData($data, $key) // Decrypt data
sanitizeFilename($filename) // Sanitize filename
validateFileUpload($file, $allowedExtensions) // Validate file upload
```

**Example Usage:**

```php
// CSRF Protection
$token = Security::generateCSRFToken();
if (Security::validateCSRFToken($_POST['csrf_token'])) {
    // Process form
}

// Input Sanitization
$username = Security::sanitizeInput($_POST['username']);

// Password Hashing
$hash = Security::hashPassword('mypassword');
if (Security::verifyPassword('mypassword', $hash)) {
    // Password correct
}

// File Upload Validation
$validation = Security::validateFileUpload($_FILES['config'], ['ovpn', 'conf']);
if ($validation['valid']) {
    // Process file
}
```

---

### Auth Class

**Location:** `/includes/Auth.php`

Authentication and authorization management.

**Methods:**

```php
login($username, $password) // Authenticate user
register($data) // Register new user
logout() // End session
isLoggedIn() // Check if user is logged in
getUser() // Get current user data
hasRole($role) // Check user role
requireLogin() // Require authentication
requireRole($role) // Require specific role
```

**Example Usage:**

```php
$auth = new Auth();

// Login
$result = $auth->login('username', 'password');
if ($result['success']) {
    // Login successful
}

// Register
$result = $auth->register([
    'username' => 'newuser',
    'email' => 'user@example.com',
    'password' => 'password',
    'role' => 'client'
]);

// Check authentication
if ($auth->isLoggedIn()) {
    $user = $auth->getUser();
}

// Require admin access
$auth->requireRole('admin');
```

---

## Security Implementation

### Password Security

- **Algorithm:** bcrypt (PASSWORD_BCRYPT)
- **Cost Factor:** 12
- **Minimum Length:** 8 characters

### Session Security

- **Lifetime:** 7200 seconds (2 hours)
- **Regeneration:** On login
- **HTTPS:** Recommended in production

### CSRF Protection

- **Token Length:** 64 characters (32 bytes hex)
- **Expiration:** 3600 seconds (1 hour)
- **Storage:** PHP session

### SQL Injection Prevention

- **Method:** PDO prepared statements
- **All queries:** Use parameter binding
- **No string concatenation:** In SQL queries

### XSS Prevention

- **Output Escaping:** htmlspecialchars()
- **Encoding:** ENT_QUOTES, UTF-8
- **Template Variables:** Always escaped

### File Upload Security

- **Size Limit:** 5MB (configurable)
- **Type Validation:** Extension whitelist
- **Name Sanitization:** Alphanumeric + underscore/dash/dot
- **Storage:** Outside web root (recommended)

---

## Configuration Reference

### Database Configuration

**File:** `config/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpn_panel');
define('DB_USER', 'database_user');
define('DB_PASS', 'database_password');
define('DB_CHARSET', 'utf8mb4');
```

### Application Settings

```php
define('APP_NAME', 'VPN Management Panel');
define('APP_URL', 'https://yourdomain.com');
define('APP_VERSION', '1.0.0');
```

### Security Settings

```php
define('SESSION_LIFETIME', 7200);
define('CSRF_TOKEN_EXPIRE', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);
```

### Upload Settings

```php
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['ovpn', 'conf', 'key', 'crt', 'pem']);
```

### Currency Settings

```php
define('DEFAULT_CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');
```

---

## Error Handling

### Database Errors

- Logged via error_log()
- User-friendly messages displayed
- Transactions rolled back on failure

### Authentication Errors

- Login attempts tracked
- Account lockout after max attempts
- Lockout duration: 15 minutes

### File Upload Errors

- Size validation
- Type validation
- Error messages returned to user

### CSRF Errors

- Token validation before processing
- Error message on failure
- Regeneration required

---

## Future API Endpoints

The following RESTful API endpoints are planned for future releases:

### Authentication
- POST /api/auth/login
- POST /api/auth/logout
- POST /api/auth/refresh

### Servers
- GET /api/servers
- GET /api/servers/:id
- POST /api/servers
- PUT /api/servers/:id
- DELETE /api/servers/:id

### Users
- GET /api/users
- GET /api/users/:id
- POST /api/users
- PUT /api/users/:id
- DELETE /api/users/:id

### Connections
- GET /api/connections
- GET /api/connections/:id
- POST /api/connections (connect)
- DELETE /api/connections/:id (disconnect)

---

## Version History

- **1.0.0** - Initial release
  - Core functionality
  - Admin and reseller panels
  - Server management
  - User management
  - Financial system

---

## Support

For technical support and documentation updates:
- Email: support@example.com
- Documentation: /docs/
- GitHub: (repository URL)
