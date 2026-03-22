# Spendwise — PHP Full-Stack App

## Project Structure

```
spendwise_php/
├── backend/
│   ├── config.php          ← DB + JWT settings
│   ├── helpers.php         ← JWT, CORS, Auth middleware
│   ├── database.sql        ← MySQL schema (run this first!)
│   ├── .htaccess
│   └── api/
│       ├── auth.php        ← Register / Login / Profile
│       ├── expenses.php    ← CRUD for expenses
│       ├── budgets.php     ← CRUD for budgets
│       └── stats.php       ← Summary / Monthly / Category stats
└── public/
    ├── login.html
    ├── register.html
    ├── dashboard.html
    ├── add-expense.html
    ├── history.html
    ├── budget.html
    ├── analytics.html
    ├── reports.html
    ├── ai.html
    ├── profile.html
    ├── sidebar.js
    ├── api.js              ← Frontend API client
    ├── app.js              ← Shared utilities
    └── style.css
```

---

## Setup Steps (XAMPP / Local Server)

### Step 1 — Folder copy karo
`spendwise_php` folder ne XAMPP ना `htdocs` folder મા મૂકો:
```
C:\xampp\htdocs\spendwise_php\
```

### Step 2 — Database create karo
phpMyAdmin ખોલો (http://localhost/phpmyadmin) અને `database.sql` import કરો:
```sql
-- ya command line thi:
mysql -u root -p < spendwise_php/backend/database.sql
```

### Step 3 — Config edit karo (optional)
`backend/config.php` ખોલો અને DB password set કરો:
```php
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default = blank
```

### Step 4 — App open karo
Browser ma open karo:
```
http://localhost/spendwise_php/public/login.html
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /backend/api/auth.php?action=register | New user register |
| POST | /backend/api/auth.php?action=login | Login |
| GET  | /backend/api/auth.php?action=me | Profile fetch |
| PUT  | /backend/api/auth.php?action=update | Profile update |
| PUT  | /backend/api/auth.php?action=password | Password change |
| GET  | /backend/api/expenses.php | All expenses |
| POST | /backend/api/expenses.php | Add expense |
| PUT  | /backend/api/expenses.php?id=X | Update expense |
| DELETE | /backend/api/expenses.php?id=X | Delete expense |
| GET  | /backend/api/budgets.php | Get budgets |
| POST | /backend/api/budgets.php | Set/update budget |
| DELETE | /backend/api/budgets.php?category=X | Delete budget |
| GET  | /backend/api/stats.php?type=summary | Overall stats |
| GET  | /backend/api/stats.php?type=monthly | Monthly breakdown |
| GET  | /backend/api/stats.php?type=categories | Category breakdown |

---

## Notes
- Apache mod_rewrite and mod_headers must be enabled
- PHP 8.0+ required (uses named arguments, str_starts_with)
- All API responses are JSON
- JWT tokens expire in 24 hours
