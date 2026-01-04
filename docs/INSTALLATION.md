# VPN Management Panel - Installation Guide

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher
- Apache or Nginx web server
- mod_rewrite enabled (Apache)
- 512MB RAM minimum
- 1GB disk space

## Installation Steps

### 1. Download and Extract

Upload all files to your web server's document root or a subdirectory.

### 2. Set File Permissions

```bash
chmod -R 755 /path/to/vpn-panel
chmod -R 777 /path/to/vpn-panel/assets/uploads
chmod 644 /path/to/vpn-panel/config/config.sample.php
```

### 3. Run the Installer

Navigate to your installation URL in a web browser:

```
http://yourdomain.com/install/
```

The installer will guide you through:
- Database configuration and creation
- Admin account setup
- System configuration

### 4. Complete Installation

After successful installation:

1. **Delete the install directory** for security:
   ```bash
   rm -rf /path/to/vpn-panel/install
   ```

2. **Secure the config file**:
   ```bash
   chmod 644 /path/to/vpn-panel/config/config.php
   ```

3. **Set up a cron job** for automated tasks (optional):
   ```bash
   */5 * * * * php /path/to/vpn-panel/cron/maintenance.php
   ```

## Default Login Credentials

- **Admin Panel**: `/admin/login.php`
  - Username: (as created during installation)
  - Password: (as created during installation)

- **Reseller Portal**: `/reseller/login.php`
  - Create reseller accounts from admin panel

## Post-Installation Configuration

### 1. Configure Application Settings

Navigate to **Admin Panel > Settings** and configure:
- Site name and email
- Currency settings
- Commission rates
- Email notifications
- Security settings

### 2. Add VPN Protocols

Default protocols are already installed:
- OpenVPN
- SSH Tunnel
- WireGuard
- IKEv2/IPSec
- SSTP

### 3. Add VPN Servers

Navigate to **Admin Panel > Servers** and add your VPN servers:
- Server details (name, IP, port)
- Protocol configuration
- Upload configuration files
- Set bandwidth and connection limits

### 4. Create Resellers (Optional)

Navigate to **Admin Panel > Users** and:
- Create reseller accounts
- Allocate credits
- Set permissions

## Troubleshooting

### Database Connection Error

Check `config/config.php` and verify:
- Database host is correct
- Database name exists
- Username and password are correct
- MySQL service is running

### Permission Errors

Ensure proper file permissions:
```bash
chown -R www-data:www-data /path/to/vpn-panel
chmod -R 755 /path/to/vpn-panel
chmod -R 777 /path/to/vpn-panel/assets/uploads
```

### Session Issues

Verify PHP session configuration in `php.ini`:
```ini
session.save_path = "/tmp"
session.gc_maxlifetime = 7200
```

## Security Recommendations

1. **Use HTTPS**: Install an SSL certificate (Let's Encrypt recommended)
2. **Change database prefix**: Edit the SQL file before installation
3. **Strong passwords**: Use complex passwords for all accounts
4. **Regular backups**: Set up automated database and file backups
5. **Update regularly**: Keep PHP and MySQL updated
6. **Firewall rules**: Configure firewall to allow only necessary ports
7. **Disable directory listing**: Add to .htaccess or server config

## Support

For support and updates:
- Documentation: `/docs/`
- Issue reporting: Contact your system administrator
- Version: 1.0.0

## License

Copyright © 2024 VPN Management Panel. All rights reserved.
