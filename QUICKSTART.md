# VPN Management Panel - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Upload Files
Upload all files to your web server (Apache/Nginx with PHP and MySQL).

### Step 2: Run Installer
Navigate to: `http://yourdomain.com/install/`

### Step 3: Complete Installation
Follow the 4-step wizard:
1. **Welcome** - Click "Next"
2. **Database** - Enter MySQL credentials
3. **Admin Account** - Create your admin account
4. **Complete** - Installation finished!

### Step 4: Delete Install Directory
```bash
rm -rf /path/to/vpn-panel/install
```

### Step 5: Login
- **Admin Panel**: `http://yourdomain.com/admin/login.php`
- **Reseller Portal**: `http://yourdomain.com/reseller/login.php`

---

## 📋 System Requirements

| Requirement | Version |
|------------|---------|
| PHP | 7.4+ |
| MySQL | 5.7+ or MariaDB 10.3+ |
| Web Server | Apache/Nginx |
| RAM | 512MB minimum |
| Disk Space | 1GB |

---

## 🔧 Quick Configuration

### Set Permissions
```bash
chmod -R 755 /path/to/vpn-panel
chmod -R 777 /path/to/vpn-panel/assets/uploads
```

### Default Credentials
Use the admin account you created during installation.

---

## 📊 First Steps After Installation

### 1. Configure Settings
Navigate to **Admin Panel > Settings** and configure:
- Site name and email
- Currency settings
- Reseller commission rate

### 2. Add VPN Servers
Go to **Servers** and click **Add Server**:
- Enter server details (name, IP, port)
- Select protocol (OpenVPN, SSH, WireGuard, etc.)
- Upload configuration file (optional)
- Set connection and bandwidth limits

### 3. Create Resellers (Optional)
Go to **Users** and create reseller accounts:
- Set role to "Reseller"
- Set status to "Active"
- Then go to **Resellers** to allocate credits

### 4. Add Clients
Go to **Users** and create client accounts:
- Set role to "Client"
- Set status to "Active"
- Assign to a reseller (optional)

---

## 🎯 Key Features

### Admin Panel
✅ Dashboard with real-time statistics  
✅ Server management (CRUD)  
✅ User management  
✅ Reseller credit system  
✅ Invoice generation  
✅ Payment tracking  
✅ Activity logs  
✅ Reports & analytics  

### Reseller Portal
✅ Independent dashboard  
✅ Client management  
✅ Credit balance tracking  
✅ Transaction history  
✅ Server access  

### Security
✅ Bcrypt password hashing  
✅ CSRF protection  
✅ SQL injection prevention  
✅ XSS prevention  
✅ Session management  
✅ Login attempt limiting  

---

## 📁 Quick File Reference

```
/admin/login.php        - Admin login
/reseller/login.php     - Reseller login
/admin/index.php        - Admin dashboard
/admin/servers.php      - Server management
/admin/users.php        - User management
/config/config.php      - Configuration (created during install)
/assets/uploads/        - Uploaded files
```

---

## 🔍 Troubleshooting

### Database Connection Error
- Verify database credentials in `config/config.php`
- Ensure MySQL service is running
- Check database user permissions

### File Upload Fails
- Verify `assets/uploads/` has write permissions (777)
- Check PHP `upload_max_filesize` setting
- Verify file type is allowed (.ovpn, .conf, .key, .crt, .pem)

### Can't Login
- Verify account status is "Active"
- Check for account lockout (5 failed attempts = 15 min lockout)
- Clear browser cache and cookies

### Page Not Found
- Ensure mod_rewrite is enabled (Apache)
- Check file permissions (755 for directories, 644 for files)
- Verify paths in configuration

---

## 📚 Documentation

| Document | Description |
|----------|-------------|
| [README.md](README.md) | Quick overview |
| [VPN_README.md](VPN_README.md) | Complete documentation |
| [INSTALLATION.md](docs/INSTALLATION.md) | Detailed installation guide |
| [USER_MANUAL.md](docs/USER_MANUAL.md) | User manual (17,000+ words) |
| [API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md) | Technical reference |
| [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) | Project overview |

---

## 🛡️ Security Best Practices

1. **Use HTTPS** - Install SSL certificate (Let's Encrypt is free)
2. **Strong Passwords** - Minimum 12 characters with mixed case, numbers, symbols
3. **Regular Backups** - Backup database and uploaded files daily
4. **Keep Updated** - Update PHP and MySQL regularly
5. **Delete Install** - Remove `/install/` directory after installation
6. **Secure Config** - Set `config/config.php` to chmod 644
7. **Disable Errors** - Set `display_errors = 0` in production

---

## 🎨 Customization

### Change Theme Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary: #667eea;      /* Primary color */
    --secondary: #764ba2;    /* Secondary color */
    --success: #10b981;      /* Success color */
    --danger: #ef4444;       /* Danger color */
}
```

### Change Currency
Admin Panel > Settings > Currency Settings

### Add Custom Logo
Add your logo to `assets/images/logo.png`

---

## 📞 Support

### Common Commands

**Backup Database:**
```bash
mysqldump -u username -p vpn_panel > backup.sql
```

**Restore Database:**
```bash
mysql -u username -p vpn_panel < backup.sql
```

**Check PHP Version:**
```bash
php -v
```

**Check MySQL Version:**
```bash
mysql --version
```

---

## ✅ Post-Installation Checklist

- [ ] Installation completed successfully
- [ ] `/install/` directory deleted
- [ ] Admin account created and working
- [ ] Settings configured
- [ ] At least one server added
- [ ] File permissions set correctly
- [ ] SSL certificate installed (HTTPS)
- [ ] Database backup scheduled
- [ ] Tested on mobile devices
- [ ] Activity logs verified

---

## 🚀 You're Ready!

Your VPN Management Panel is now installed and ready to use.

**Next Steps:**
1. Add your VPN servers
2. Create user accounts
3. Set up resellers (if applicable)
4. Generate invoices
5. Monitor connections

**Need Help?**
- Check the full documentation in [VPN_README.md](VPN_README.md)
- Review the user manual in [docs/USER_MANUAL.md](docs/USER_MANUAL.md)
- Contact your system administrator

---

**Version**: 1.0.0  
**Status**: Production Ready ✅  
**License**: Copyright © 2024 VPN Management Panel
