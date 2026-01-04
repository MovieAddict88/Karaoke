# VPN Management Panel - User Manual

## Table of Contents

1. [Admin Panel](#admin-panel)
2. [Reseller Dashboard](#reseller-dashboard)
3. [Server Management](#server-management)
4. [User Management](#user-management)
5. [Financial Management](#financial-management)

---

## Admin Panel

### Dashboard

The admin dashboard provides an overview of your VPN management system:

- **Total Users**: Number of registered clients
- **Total Resellers**: Number of active resellers
- **Total Servers**: Number of configured VPN servers
- **Active Connections**: Current active VPN connections
- **Total Revenue**: Sum of all paid invoices
- **Pending Invoices**: Invoices awaiting payment

### Server Management

#### Adding a Server

1. Navigate to **Servers** from the sidebar
2. Click **Add Server**
3. Fill in the required information:
   - **Server Name**: Descriptive name for the server
   - **IP Address**: Server's public IP address
   - **Port**: VPN service port
   - **Protocol**: Select from OpenVPN, SSH, WireGuard, IKEv2, SSTP
   - **Location**: Country and city
   - **Max Connections**: Maximum simultaneous connections
   - **Bandwidth Limit**: Monthly bandwidth limit (GB)
4. Upload configuration file (optional)
5. Set server credentials if required
6. Click **Add Server**

#### Editing a Server

1. Click the **Edit** button next to the server
2. Modify the desired fields
3. Upload a new configuration file to replace existing
4. Click **Update Server**

#### Deleting a Server

1. Click the **Delete** button next to the server
2. Confirm deletion
3. Note: This will disconnect all active connections

### User Management

#### Creating Users

1. Navigate to **Users** from the sidebar
2. Click **Add User**
3. Enter user details:
   - Username (unique)
   - Email address
   - Password (minimum 8 characters)
   - Full name
   - Role (Admin, Reseller, or Client)
   - Status (Active, Pending, or Suspended)
4. Click **Add User**

#### Managing Users

- **Edit**: Modify user details
- **Suspend**: Temporarily disable user access
- **Activate**: Re-enable suspended accounts
- **Delete**: Permanently remove user account

#### Filtering Users

Use the filter options to find users:
- Search by username, email, or name
- Filter by role (Admin/Reseller/Client)
- Filter by status (Active/Suspended/Pending)

### Reseller Management

#### Adding Credits

1. Navigate to **Resellers**
2. Click the **+** button next to the reseller
3. Enter the credit amount
4. Add a description
5. Click **Add Credit**

#### Deducting Credits

1. Click the **−** button next to the reseller
2. Enter the amount to deduct
3. Add a description
4. Click **Deduct Credit**

#### Viewing Reseller Details

Click the **View** button to see:
- Client list
- Transaction history
- Revenue statistics
- Account activity

### Invoice Management

#### Creating Invoices

1. Navigate to **Invoices**
2. Click **Create Invoice**
3. Select the user
4. Enter description and amount
5. Add tax if applicable
6. Set due date
7. Click **Create Invoice**

#### Managing Invoices

- **View**: Display full invoice details
- **Mark as Paid**: Update invoice status
- **Cancel**: Cancel pending invoices
- **Delete**: Remove invoice from system

### Payment Tracking

Navigate to **Payments** to view:
- All payment transactions
- Payment methods used
- Transaction status
- Associated invoices

### System Settings

Configure system-wide settings:

1. **General Settings**
   - Site name and email
   - Maintenance mode
   - User registration options

2. **Currency Settings**
   - Default currency
   - Currency symbol
   - Tax rates

3. **Reseller Settings**
   - Commission rates
   - Credit allocation rules
   - Approval workflow

4. **Email Settings**
   - SMTP configuration
   - Email templates
   - Notification preferences

### Activity Logs

View system activity:
- User logins and logouts
- Administrative actions
- System changes
- Security events

---

## Reseller Dashboard

### Overview

The reseller dashboard shows:
- Current credit balance
- Total clients
- Active clients
- Total revenue
- Recent transactions

### Managing Clients

#### Adding Clients

1. Navigate to **My Clients**
2. Click **Add Client**
3. Enter client details
4. Click **Add Client**

Note: Creating clients will deduct credits from your balance.

### Viewing Servers

Access the **Servers** page to:
- View available VPN servers
- Check server status
- View connection limits
- Access configuration files

### Invoice Management

Create and manage invoices for your clients:
1. Navigate to **Invoices**
2. Create invoices for your clients
3. Track payment status
4. Generate PDF receipts

### Transaction History

View your credit transactions:
- Credits added by admin
- Credits spent on clients
- Current balance
- Transaction descriptions

---

## Server Management

### Supported Protocols

#### OpenVPN
- Upload `.ovpn` configuration files
- Set authentication credentials
- Configure encryption settings

#### SSH Tunnel
- Specify SSH server details
- Set port forwarding rules
- Configure authentication methods

#### WireGuard
- Upload `.conf` files
- Set public/private keys
- Configure peer settings

#### IKEv2/IPSec
- Upload certificate files
- Configure authentication methods
- Set encryption algorithms

#### SSTP
- Configure SSL certificate
- Set tunnel parameters
- Define routing rules

### Configuration File Management

Supported file types:
- `.ovpn` (OpenVPN)
- `.conf` (WireGuard, general config)
- `.key` (Private keys)
- `.crt` (Certificates)
- `.pem` (Certificate/key files)

Maximum file size: 5MB

---

## User Management

### User Roles

#### Admin
- Full system access
- Manage all users and servers
- Access financial data
- Configure system settings

#### Reseller
- Manage own clients
- View available servers
- Create invoices
- Track revenue and credits

#### Client
- Access VPN servers
- View usage statistics
- Manage profile
- View invoices and payments

### Account Status

- **Active**: Full access to system
- **Pending**: Awaiting approval
- **Suspended**: Temporarily disabled

---

## Financial Management

### Invoicing System

#### Invoice Status

- **Draft**: Being prepared
- **Sent**: Sent to client
- **Paid**: Payment received
- **Overdue**: Past due date
- **Cancelled**: Invoice cancelled

#### Invoice Components

- Invoice number (auto-generated)
- Client information
- Item descriptions
- Amount and tax
- Due date
- Payment status

### Payment Processing

Track payments with:
- Transaction ID
- Payment amount
- Payment method
- Transaction status
- Associated invoice
- Transaction date

### Credit System (Resellers)

Reseller credits are used to:
- Create new client accounts
- Allocate server resources
- Generate invoices

Credits are:
- Added by administrators
- Deducted when creating clients
- Tracked in transaction history

---

## Best Practices

### Security

1. Use strong passwords (minimum 12 characters)
2. Enable two-factor authentication when available
3. Regularly review activity logs
4. Keep server configurations secure
5. Regularly backup configuration files

### Server Management

1. Monitor server capacity and connections
2. Set appropriate bandwidth limits
3. Keep configuration files updated
4. Test servers before assigning to users
5. Document server changes

### User Management

1. Verify user emails during registration
2. Regularly review user activity
3. Suspend suspicious accounts
4. Keep user information updated
5. Respond to user support requests promptly

### Financial Management

1. Send invoices promptly
2. Follow up on overdue payments
3. Keep accurate transaction records
4. Reconcile accounts monthly
5. Generate regular financial reports

---

## Troubleshooting

### Common Issues

#### Cannot Login
- Verify username and password
- Check account status
- Clear browser cache
- Contact administrator

#### Server Connection Failed
- Check server status
- Verify configuration file
- Ensure port is not blocked
- Check connection limits

#### Upload Failed
- Verify file size (max 5MB)
- Check file type
- Ensure proper permissions
- Try again later

#### Invoice Not Generated
- Verify user selection
- Check amount entered
- Ensure required fields filled
- Contact administrator

---

## Support

For additional support:
- Contact your system administrator
- Review documentation in `/docs/`
- Check activity logs for errors
- Verify system requirements

---

## Version Information

- Version: 1.0.0
- Last Updated: 2024
- Compatible with: PHP 7.4+, MySQL 5.7+
