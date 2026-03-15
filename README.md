# Spendwise — Personal Finance Management System

A modern PHP-based expense tracking and budget management application with JWT authentication.

## Features

- 👤 User authentication (Register/Login)
- 💰 Expense tracking and management
- 📊 Budget planning by category
- 📈 Financial analytics and reports
- 📱 Responsive web interface
- 🔐 JWT token-based security

## Project Structure

```
spendwise/
├── backend/
│   ├── config.php           # Database & JWT configuration
│   ├── database.sql         # Database schema
│   ├── .htaccess            # URL rewriting rules
│   └── api/
│       ├── auth.php         # Authentication endpoints
│       ├── expenses.php     # Expense management
│       ├── budgets.php      # Budget management
│       └── stats.php        # Statistics & analytics
├── frontend/
│   ├── login.html           # Login page
│   ├── register.html        # Registration page
│   ├── dashboard.html       # Main dashboard
│   ├── expenses.html        # Expenses management
│   ├── budgets.html         # Budget management
│   ├── analytics.html       # Analytics & reports
│   ├── api.js               # API client
│   ├── styles.css           # Global styles
│   └── app.js               # Frontend logic
└── README.md
```

## Prerequisites

- XAMPP/MAMP with PHP 8.0+
- MySQL 5.7+
- Modern web browser

## Installation & Setup

### 1. Clone/Download Project

Place the project in XAMPP htdocs:
```bash
/Applications/XAMPP/xamppfiles/htdocs/spendwise/
```

### 2. Start XAMPP Services

```bash
# Start Apache
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start

# Start MySQL
sudo /Applications/XAMPP/xamppfiles/bin/mysqld_safe &
```

Or open XAMPP Control Panel:
```bash
open /Applications/XAMPP/XAMPP\ Control\ Panel.app
```

### 3. Create Database

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/spendwise
mysql -u root < backend/database.sql
```

### 4. Configure Backend

Edit `backend/config.php`:
```php
<?php
define('DB_HOST',     'localhost');
define('DB_NAME',     'spendwise');
define('DB_USER',     'root');           // your MySQL username
define('DB_PASS',     '');               // your MySQL password
define('DB_CHARSET',  'utf8mb4');

define('JWT_SECRET',  'change-this-to-a-long-random-string-in-production');
define('JWT_EXPIRY',  86400);            // 24 hours

define('ALLOWED_ORIGIN', '*');           // Restrict to your domain in production
```

### 5. Verify Frontend API URL

Confirm `frontend/api.js` has correct backend URL:
```js
const BASE_URL = 'http://localhost/spendwise/backend/api/';
```

### 6. Access Application

Open in browser:
```
http://localhost/spendwise/frontend/login.html
```

## API Endpoints Reference

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth.php?action=register` | Register new user |
| POST | `/api/auth.php?action=login` | User login |
| GET  | `/api/auth.php?action=me` | Get current user |
| PUT  | `/api/auth.php?action=update` | Update profile |
| PUT  | `/api/auth.php?action=password` | Change password |

### Expenses
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET  | `/api/expenses.php` | Get all expenses |
| POST | `/api/expenses.php` | Add new expense |
| PUT  | `/api/expenses.php?id=X` | Update expense |
| DELETE | `/api/expenses.php?id=X` | Delete expense |

### Budgets
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET  | `/api/budgets.php` | Get all budgets |
| POST | `/api/budgets.php` | Set/update budget |
| DELETE | `/api/budgets.php?category=X` | Delete budget |

### Statistics
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET  | `/api/stats.php?type=summary` | Overall statistics |
| GET  | `/api/stats.php?type=monthly` | Monthly breakdown |
| GET  | `/api/stats.php?type=categories` | Category breakdown |

## Quick Start Commands

```bash
# Navigate to project
cd /Applications/XAMPP/xamppfiles/htdocs/spendwise

# Start services
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start
sudo /Applications/XAMPP/xamppfiles/bin/mysqld_safe &

# Import database
mysql -u root < backend/database.sql

# Open in browser
open "http://localhost/spendwise/frontend/login.html"

# Test API
curl -X GET "http://localhost/spendwise/backend/api/auth.php?action=me"
```

## CORS Headers

All API endpoints include CORS headers for cross-origin requests:
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
```

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Email address (unique)
- `password` - Hashed password
- `created_at` - Account creation timestamp

### Expenses Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `category` - Expense category
- `amount` - Expense amount
- `description` - Expense description
- `date` - Expense date
- `created_at` - Record creation timestamp

### Budgets Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `category` - Budget category
- `limit_amount` - Budget limit
- `created_at` - Record creation timestamp

## Troubleshooting

### 403 Access Forbidden Error
```bash
# Fix permissions
chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/spendwise/backend/
chmod 644 /Applications/XAMPP/xamppfiles/htdocs/spendwise/backend/api/*.php

# Restart Apache
sudo /Applications/XAMPP/xamppfiles/bin/apachectl restart
```

### Database Connection Error
```bash
# Check MySQL is running
mysql -u root -e "SELECT 1;"

# Verify database exists
mysql -u root -e "USE spendwise; SHOW TABLES;"
```

### API Not Responding
```bash
# Check Apache error log
tail -100 /Applications/XAMPP/xamppfiles/logs/httpd.log

# Test API endpoint
curl -X GET "http://localhost/spendwise/backend/api/auth.php?action=me"
```

## Security Notes

⚠️ **Before production deployment:**
1. Change `JWT_SECRET` to a strong random string
2. Set `ALLOWED_ORIGIN` to your domain only
3. Use HTTPS instead of HTTP
4. Add input validation and sanitization
5. Implement rate limiting
6. Use environment variables for sensitive data

## Technologies Used

- **Backend**: PHP 8.2, MySQL 5.7+, JWT
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Server**: Apache 2.4, XAMPP
- **Authentication**: JWT (JSON Web Tokens)

## License

MIT License - Feel free to use and modify

## Support

For issues or questions, check the error logs:
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/httpd.log
```

---

**Last Updated**: March 15, 2026
**Version**: 1.0.0
