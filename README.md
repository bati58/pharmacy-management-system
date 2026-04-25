# BatiFlow Smart Pharma – Pharmacy Management System

A complete, role-based Pharmacy Management System built with **PHP (no framework)**, **MySQL**, and vanilla **HTML/CSS/JavaScript**.  
Manage inventory, sales, stock transfers, users, branches, and analytics – all from a responsive web interface.

---

## ✨ Features

### Manager (Owner)
- **Branch Management** – create, edit, delete branches.
- **User Management** – invite/activate/deactivate pharmacists and store keepers via secure email links.
- **Inventory Oversight** – view all drugs across branches, monitor stock levels, expiry alerts.
- **Pricing Management** – update drug prices globally.
- **Sales & Reporting** – daily/weekly/monthly sales reports with **PDF generation**, revenue by branch/pharmacist, top drugs.
- **Analytics** – revenue trends, slow-moving drugs, profit analysis.

### Pharmacist
- **Sales Processing** – search drugs, add to cart, generate invoice (printable).
- **Prescription Handling** – optional prescription reference for strict medications.
- **Sales Tracking** – view own daily/weekly/monthly sales.
- **Stock Usage** – automatic stock deduction upon sale completion.

### Store Keeper
- **Drug Inventory** – add/edit drugs, batch tracking, expiry dates, supplier/manufacturer details.
- **Stock Management** – update stock (receive, adjust, record damaged/expired).
- **Stock Transfers** – move stock from main store to branch dispensaries, view transfer history.

### General (All Roles)
- **Authentication** – Secure login/logout with encrypted passwords and visibility toggles.
- **Invitation-only Onboarding** – Managers invite personnel via email; no open signup, ensuring enterprise security.
- **Smart Notifications** – Role and branch-scoped alerts. Real-time notifications for incoming stock transfers and new user registrations. Automated low stock and expiry alerts via scheduled background jobs.
- **Role-Based Menus** – Sidebar adapts dynamically to user role permissions.
- **Modern UI** – Premium glassmorphic interface with loading states and micro-animations.
- **Search & Filter** – Drugs by name/category/batch, reports by date/branch.

---

## 👥 User Roles & Permissions

| Feature                             | Manager |   Pharmacist   | Store Keeper |
| ----------------------------------- | :-----: | :------------: | :----------: |
| Manage branches                     |    ✅    |       ❌        |      ❌       |
| Manage users                        |    ✅    |       ❌        |      ❌       |
| View all drugs (all branches)       |    ✅    | ✅ (own branch) |      ✅       |
| Add/edit drugs                      |    ❌    |       ❌        |      ✅       |
| Update stock                        |    ❌    |       ❌        |      ✅       |
| Transfer stock (store ↔ dispensary) |    ❌    |       ❌        |      ✅       |
| Process sales / invoices            |    ❌    |       ✅        |      ❌       |
| View own sales                      |    ❌    |       ✅        |      ❌       |
| View global reports                 |    ✅    |       ❌        |      ❌       |
| Receive low stock / expiry alerts   |    ✅    |       ✅        |      ✅       |

---

## 🛠 Technology Stack

- **Backend**: PHP 8+ (native, no framework)
- **Database**: MySQL (MariaDB)
- **Frontend**: HTML5, CSS3 (Tailwind CSS), vanilla JavaScript
- **Charts**: Chart.js CDN
- **Icons**: Font Awesome 6
- **Server**: Apache (XAMPP / WAMP / LAMP)

---

## 📦 Installation

### Requirements
- PHP 7.4 or higher (8.x recommended)
- MySQL 5.7 or higher
- Apache with `mod_rewrite` enabled
- Composer (optional, for email/PDF libraries)

### Step-by-Step Setup

1. **Clone or download** the project into your web server’s document root (e.g., `C:\xampp\htdocs\` for XAMPP).

2. **Create the database**  
   - Open phpMyAdmin or your MySQL command line.  
   - Create a database named `pms_db`.  
   - Import the schema: `database/schema.sql`.  
   - Import sample data: `database/seed.sql`. *(Note: For richer testing data, you can optionally import `database/manual_crud_seed.sql`)*

3. **Configure database connection**  
   Edit `backend/config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'pms_db';
   $username = 'root';
   $password = ''; // Set your local DB password if applicable
   ```

4. **Background Jobs**
   Set up a cron job (Linux) or Task Scheduler (Windows) to run `backend/helpers/expiry_checker.php` daily to generate automated expiry and low-stock notifications.

---

