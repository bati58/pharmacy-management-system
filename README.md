# 🏥 PharmaFlow System - Enterprise Pharmacy Management System

![Project Status](https://img.shields.io/badge/Status-Completed-success)
![Version](https://img.shields.io/badge/Version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.0-38B2AC?logo=tailwind-css&logoColor=white)

PharmaFlow System is a comprehensive, multi-branch Pharmacy Management System engineered to streamline operations, enforce strict inventory control, and provide real-time analytical insights. Built entirely on a native PHP architecture with a modern, glassmorphic UI, it eliminates the need for heavy frameworks while delivering enterprise-grade performance.

---

## 🚀 System Overview

The application serves as a centralized hub for managing daily pharmacy operations across multiple geographical branches. It enforces strict **Role-Based Access Control (RBAC)**, ensuring data integrity and security while providing tailored interfaces for different staff roles.

### Core Objectives
* **Inventory Mastery:** Eliminate stockouts and prevent expired drug dispensing through automated alerts and batch tracking.
* **Operational Efficiency:** Streamline the Point-of-Sale (POS) experience with rapid drug searching, stock deduction, and invoicing.
* **Managerial Oversight:** Empower administrators with holistic dashboards, granular stock movement audits, and dynamic KPI reporting.
* **Multi-Branch Capability:** Manage independent inventory silos and securely facilitate inter-branch stock transfers.

---

## 👥 Role-Based Functionality

The system is strictly partitioned into three core operational roles, each with designated privileges:

### 👑 1. Manager (Administrator)
The highest privilege level, possessing unrestricted access to the system.
* **Global Dashboard:** View aggregated revenue, total inventory valuation, and critical alerts across *all* branches.
* **Branch Management:** Register new pharmacy locations, edit physical addresses, and archive closed branches.
* **Human Resources:** Manage staff accounts. Issue secure email invitations for new hires and deactivate compromised accounts.
* **Advanced Analytics:** Generate custom-dated revenue reports, identify top-performing drugs, and review slow-moving inventory.
* **Audit Control:** View immutable stock movement logs (audit trails) for accountability.

### 📦 2. Store Keeper (Inventory Specialist)
Responsible for maintaining accurate stock levels and processing supply shipments.
* **Catalog Management:** Add new pharmaceuticals to the database (Name, Category, Manufacturer, Supplier, Batch No., Expiry Date).
* **Stock Adjustments:** Perform manual stock counts and adjustments, requiring mandatory justification remarks (e.g., "Damaged goods", "Audit correction").
* **Transfer Logistics:** Initiate and receive stock transfers between the main store and dispensary branches.
* **Alert Monitoring:** Track low-stock thresholds and imminent drug expiries through the notification center.

### 💊 3. Pharmacist (Sales & Dispensing)
The frontline operational role, focused entirely on secure and rapid patient transactions.
* **Point of Sale (POS):** Utilize a streamlined interface to search active inventory, add items to a cart, and compute totals automatically.
* **Transaction Processing:** Record sales with optional fields for **Prescription References** and **Discount Amounts**.
* **Automated Deduction:** Finalized sales instantly deduct stock from the assigned branch's inventory pool.
* **Sales Ledger:** Review historical transactions, reverse erroneous entries (if permitted), and generate patient receipts.

---

## 🛠️ Technology Stack

PharmaFlow System is intentionally built without heavy backend frameworks to demonstrate native language proficiency, speed, and raw architectural design.

* **Backend Environment:** Native PHP 8.x (Procedural & Object-Oriented blend)
* **Database Management:** MySQL / MariaDB (Relational design, strict foreign keys)
* **Frontend Presentation:** HTML5, Vanilla JavaScript (ES6+), CSS3
* **CSS Framework:** Tailwind CSS (via CDN) for rapid, responsive layout creation
* **Data Visualization:** Chart.js for dynamic, interactive KPI rendering
* **Email Infrastructure:** PHPMailer (via Composer) for secure account invitation distribution
* **Iconography:** FontAwesome 6

---

## ⚙️ Architecture & Database Design

The system employs a customized MVC-like structure to separate concerns:
* `/backend/api/`: REST-like JSON endpoints serving the frontend via AJAX/Fetch API.
* `/backend/models/`: Database abstraction, query generation, and data validation.
* `/frontend/pages/`: View templates combining PHP session logic with HTML markup.

### Key Database Entities
* `users`: Authentication credentials, role mapping, and branch assignment.
* `branches`: Geographical entities that silo stock data.
* `drugs`: The master pharmaceutical catalog including pricing and expiry metadata.
* `sales` & `sale_items`: Normalized transaction ledgers supporting one-to-many item associations.
* `transfers`: Tracking logistics and state (Pending/Completed) of inter-branch movements.
* `stock_movements`: A critical, append-only audit table logging every addition, deduction, or transfer of a drug.

---

## 💻 Installation & Deployment

Follow these instructions to deploy the system on a local XAMPP/WAMP environment.

### 1. Prerequisites
* **XAMPP** installed (Apache & MySQL).
* **PHP 7.4 or higher** (PHP 8.0+ strongly recommended).
* **Composer** (required for the PHPMailer vendor dependency).

### 2. File Placement
Clone or extract the project repository into your local server directory.
**Crucial:** The project folder *must* be named exactly `pharmacy-management-system` for relative routing to function out-of-the-box.
`C:\xampp\htdocs\pharmacy-management-system`

### 3. Database Initialization
1. Launch **Apache** and **MySQL** via the XAMPP Control Panel.
2. Navigate to `http://localhost/phpmyadmin`.
3. Create a new, empty database named `pharmacy_db`.
4. Import the SQL schemas located in the `/database` folder in the following strict order:
   * 📄 `schema.sql` (Creates core tables and relationships)
   * 📄 `seed.sql` (Injects demo data and default administrator accounts)

### 4. Configuration
1. Open `backend/config/database.php`.
2. Ensure the connection credentials match your local MySQL setup:
```php
<?php
$host = 'localhost';
$dbname = 'pharmacy_db';
$username = 'root';
$password = ''; // Default XAMPP password is empty
```
3. *(Optional)* Configure your SMTP credentials in `backend/config/config.local.php` if you wish to test the email invitation functionality.

### 5. Dependency Installation
Open your terminal, navigate to the project root, and run:
```bash
composer install
```
*(This ensures the `vendor/` directory is populated with PHPMailer for email services).*

### 6. Launch Application
Access the system via your browser:
* **Login Portal:** `http://localhost/pharmacy-management-system/frontend/index.php`

---

## 🔑 Demo Access Credentials

The `seed.sql` file provisions three primary accounts for testing distinct role capabilities:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Manager** | `manager@pharmaflow.com` | `Admin@123` |
| **Store Keeper** | `storekeeper@pharmaflow.com` | `Admin@123` |
| **Pharmacist** | `pharmacist@pharmaflow.com` | `Admin@123` |

---

## 📱 Mobile Responsiveness & UI Experience

PharmaFlow System guarantees a premium user experience across all devices. 
* **Glassmorphism Aesthetic:** Modern translucent backgrounds with blur effects, soft shadows, and clean gradients.
* **Fluid Layouts:** The responsive sidebar automatically converts into an off-canvas mobile drawer on screens below 768px.
* **Data Tables:** Complex inventory and sales tables utilize horizontal touch-scrolling on mobile devices to prevent layout breakage.
* **Dynamic Modals:** All CRUD (Create, Read, Update, Delete) operations occur seamlessly via animated, centralized modal overlays without requiring page reloads.

---
*Built as a professional demonstration of Native PHP architecture and Modern Web Design.*
