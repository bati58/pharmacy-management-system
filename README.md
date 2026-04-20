# BatiFlow Smart Pharma - Pharmacy Management System

A role-based Pharmacy Management System built with native PHP (no framework), MySQL, and vanilla HTML/CSS/JavaScript.

## Overview

This project helps a pharmacy manage:
- Drug inventory and expiry
- Sales and invoices
- Stock transfers between store and dispensary
- Multi-branch operation
- Role-based access for manager, pharmacist, and store keeper
- Reports and analytics

## Screenshots

### Login Page
![Login](frontend/assets/login.jpg)

### Manager Dashboard
![Dashboard](frontend/assets/dashboard.jpg)

## Features

### Manager (Owner)
- Manage branches (create, edit, delete, list)
- Manage users (invite, activate/deactivate, update, delete)
- Monitor inventory across branches
- Update drug prices
- View reports by period, branch, and pharmacist
- View analytics (revenue trends, top and slow-moving drugs)

### Store Keeper
- Add and update drugs (name, category, batch, manufacturer, supplier, expiry)
- Update stock with reason tracking
- Create stock transfers and review transfer history
- Monitor low-stock and near-expiry alerts

### Pharmacist
- Search/select drugs and create sales
- Automatic stock deduction on sale
- Optional prescription reference capture
- Optional discount amount on sale
- View sales history

### General
- Login/logout with role-based authorization
- Password reset and invite-based account activation
- Notifications (low stock, expiry, system)
- Search and filter for inventory and reports
- Stock movement audit logging

## User Roles and Access

| Feature | Manager | Pharmacist | Store Keeper |
|---|:---:|:---:|:---:|
| Manage branches | Yes | No | No |
| Manage users | Yes | No | No |
| View all branches inventory | Yes | Limited (own branch) | Limited (own branch) |
| Add/edit drugs | Yes | No | Yes |
| Update stock | Yes | No | Yes |
| Create transfers | Yes | No | Yes |
| Process sales | Yes | Yes | No |
| Access global reports | Yes | No | No |
| Receive notifications | Yes | Yes | Yes |

## Tech Stack

- Backend: PHP 8+ (native, no framework)
- Database: MySQL / MariaDB
- Frontend: HTML5, CSS3, JavaScript
- Charts: Chart.js
- Server: Apache (XAMPP/WAMP/LAMP)

## Installation (XAMPP)

### Requirements
- PHP 7.4+ (PHP 8 recommended)
- MySQL 5.7+ / MariaDB
- Apache with `mod_rewrite` enabled

### Setup Steps

1. Place the project in:
   - `C:\xampp\htdocs\pharmacy-management-system`

2. Start Apache and MySQL from XAMPP Control Panel.

3. Create database:
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create `pharmacy_db`

4. Import SQL files in this order:
   - `database/schema.sql`
   - `database/seed.sql`
   - `database/migration_add_sales_discount_and_prescription.sql` (for discount/prescription sale fields)

   Note:
   - `migration_add_invitations_and_drug_fields.sql` should only be run on older databases that do not already have those columns/tables.
   - If MySQL says "Duplicate column name", skip that migration because it was already applied in your schema.

5. Configure DB connection in `backend/config/database.php`:

```php
$host = 'localhost';
$dbname = 'pharmacy_db';
$username = 'root';
$password = '';
```

6. Open the app:
   - Frontend: `http://localhost/pharmacy-management-system/frontend/index.php`
   - API entry: `http://localhost/pharmacy-management-system/backend/index.php`

## Demo Accounts (from seed.sql)

- Manager: `manager@batiflow.com`
- Pharmacist: `pharmacist@batiflow.com`
- Store Keeper: `storekeeper@batiflow.com`
- Password (all demo users): `Admin@123`

## Database Notes

- Main tables: `users`, `branches`, `drugs`, `sales`, `sale_items`, `transfers`, `notifications`, `invitations`, `stock_movements`
- `stock_movements` stores inventory audit trail entries
- Sales now include:
  - `discount_amount`
  - `prescription_reference`

## Useful URLs

- phpMyAdmin: `http://localhost/phpmyadmin`
- App: `http://localhost/pharmacy-management-system/frontend/index.php`

## License

Academic project for web programming course (HTML, CSS, JS, PHP+MySQL without framework).
