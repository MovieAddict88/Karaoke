# VPN Management Panel

A comprehensive, production-ready VPN management system built with PHP and MySQL. This application provides complete server management, user administration, reseller functionality, and financial tracking capabilities.

## Features

### Core Functionality
- ✅ **Auto-Installation Script** - Easy setup with database creation and admin account
- ✅ **Responsive Design** - Mobile-friendly interface using CSS Grid, Flexbox, and clamp() functions
- ✅ **Dual Dashboard System** - Separate admin and reseller interfaces
- ✅ **Multi-Protocol Support** - OpenVPN, SSH, WireGuard, IKEv2, SSTP
- ✅ **Secure Authentication** - Session management, CSRF protection, password hashing

### Server Management
- ✅ Add/Edit/Delete VPN servers
- ✅ Upload configuration files (.ovpn, .conf, .key, .crt, .pem)
- ✅ Manual server configuration
- ✅ Server status monitoring
- ✅ Connection and bandwidth limits
- ✅ Multi-protocol support

### User Management
- ✅ User registration and authentication
- ✅ Profile management
- ✅ Role-based access control (Admin, Reseller, Client)
- ✅ Account status management (Active, Suspended, Pending)
- ✅ Activity logging
- ✅ Login attempt limiting and lockout

### Reseller System
- ✅ Credit-based system
- ✅ Client management
- ✅ Revenue tracking
- ✅ Transaction history
- ✅ Commission tracking
- ✅ Independent dashboard

### Financial Management
- ✅ Invoice generation
- ✅ Payment tracking
- ✅ Receipt system
- ✅ Multi-currency support
- ✅ Tax calculation
- ✅ Payment history

## Technology Stack

- **Backend**: PHP 7.4+ (Pure PHP, no frameworks)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3 (Grid, Flexbox, Custom Properties)
- **JavaScript**: Vanilla JS (No dependencies)
- **Security**: PDO prepared statements, password_hash, CSRF tokens

## Installation

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3+
- Apache/Nginx web server
- mod_rewrite enabled (Apache)
- 512MB RAM minimum
- 1GB disk space

### Quick Start

1. **Upload files** to your web server

2. **Set permissions**:
   ```bash
   chmod -R 755 /path/to/vpn-panel
   chmod -R 777 /path/to/vpn-panel/assets/uploads
   ```

3. **Run installer**:
   ```
   http://yourdomain.com/install/
   ```

4. **Complete setup**:
   - Configure database connection
   - Create admin account
   - Delete `/install` directory

5. **Login**:
   - Admin: `/admin/login.php`
   - Reseller: `/reseller/login.php`

For detailed installation instructions, see [docs/INSTALLATION.md](docs/INSTALLATION.md)

## Directory Structure

```
vpn-panel/
├── admin/              # Admin panel files
│   ├── includes/       # Admin sidebar and components
│   ├── index.php       # Admin dashboard
│   ├── login.php       # Admin login
│   ├── servers.php     # Server management
│   ├── users.php       # User management
│   ├── resellers.php   # Reseller management
│   ├── invoices.php    # Invoice management
│   └── logout.php      # Logout handler
├── reseller/           # Reseller dashboard
│   ├── includes/       # Reseller sidebar and components
│   ├── index.php       # Reseller dashboard
│   ├── login.php       # Reseller login
│   └── logout.php      # Logout handler
├── api/                # API endpoints (future)
├── assets/             # Static assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── images/         # Images and icons
│   └── uploads/        # User uploads
├── config/             # Configuration files
│   ├── config.php      # Main config (created during install)
│   ├── config.sample.php  # Config template
│   └── database.sql    # Database schema
├── includes/           # Shared PHP classes
│   ├── Database.php    # Database wrapper
│   ├── Security.php    # Security utilities
│   └── Auth.php        # Authentication class
├── install/            # Installation wizard
│   └── index.php       # Installer script
└── docs/               # Documentation
    ├── INSTALLATION.md # Installation guide
    └── USER_MANUAL.md  # User manual
```

## Security Features

### Authentication & Authorization
- Secure password hashing (bcrypt)
- Session management with timeout
- Role-based access control
- Login attempt limiting
- Account lockout mechanism

### Data Protection
- PDO prepared statements (SQL injection prevention)
- Input validation and sanitization
- CSRF token protection
- XSS prevention
- Secure file upload validation

### Security Best Practices
- No hardcoded credentials
- Secure session configuration
- Error logging without exposure
- File permission management
- HTTPS recommended

## Configuration

### Database Settings
Edit `config/config.php` after installation:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpn_panel');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
```

### Application Settings
```php
define('APP_NAME', 'VPN Management Panel');
define('SESSION_LIFETIME', 7200);  // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('DEFAULT_CURRENCY', 'USD');
```

### Upload Settings
```php
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5242880);  // 5MB
define('ALLOWED_EXTENSIONS', ['ovpn', 'conf', 'key', 'crt', 'pem']);
```

## API Documentation

### Database Schema

#### Users Table
Stores all user accounts (admin, reseller, client)
- Role-based access control
- Status management
- Balance tracking for resellers

#### Servers Table
VPN server configurations
- Multi-protocol support
- Connection limits
- Bandwidth tracking
- Configuration file storage

#### Invoices Table
Invoice management
- Auto-generated invoice numbers
- Tax calculation
- Multiple status tracking
- Due date management

#### Reseller Credits Table
Credit transaction history
- Debit/credit tracking
- Balance snapshots
- Transaction descriptions

See `config/database.sql` for complete schema.

## Development

### Code Standards
- PSR-12 coding style
- Prepared statements for all queries
- Input validation on all user input
- Output escaping for all displayed data
- Comprehensive error handling

### Adding New Features
1. Create database migrations if needed
2. Add business logic to appropriate classes
3. Create UI components following existing patterns
4. Implement CSRF protection
5. Add appropriate logging
6. Test thoroughly

## Responsive Design

The application uses modern CSS techniques:
- CSS Grid for dashboard layouts
- Flexbox for component alignment
- CSS Custom Properties for theming
- `clamp()` for fluid typography and spacing
- Mobile-first approach
- Breakpoints: 480px, 768px, 1024px

## Browser Support

- Chrome/Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Database Connection Error
Check `config/config.php` credentials and ensure MySQL is running.

### File Upload Fails
Verify `assets/uploads/` has write permissions (777).

### Session Issues
Check PHP session configuration and ensure `/tmp` is writable.

### Login Issues
Verify account status and check for account lockout.

See [docs/USER_MANUAL.md](docs/USER_MANUAL.md) for detailed troubleshooting.

## Roadmap

Future enhancements:
- [ ] Two-factor authentication
- [ ] API endpoints for mobile apps
- [ ] Real-time connection monitoring
- [ ] Bandwidth usage charts
- [ ] Email notifications
- [ ] PDF invoice generation
- [ ] Multi-language support
- [ ] Dark mode theme
- [ ] Advanced reporting
- [ ] Backup/restore functionality

## Support

- Documentation: `/docs/`
- Installation Guide: `/docs/INSTALLATION.md`
- User Manual: `/docs/USER_MANUAL.md`

## License

Copyright © 2024 VPN Management Panel. All rights reserved.

## Credits

Developed with modern web technologies:
- Font Awesome for icons
- Pure PHP (no frameworks)
- MySQL/MariaDB database
- Responsive CSS design

## Version

Current Version: **1.0.0**

---

**Note**: This is a production-ready application. Ensure you follow security best practices, use HTTPS in production, keep software updated, and perform regular backups.
