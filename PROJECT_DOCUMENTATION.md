# 🏥 PharmaFlow: Enterprise Pharmacy Management System

## 🌟 Executive Summary
**PharmaFlow** is a premium, professional-grade Pharmacy Management System (PMS) designed to streamline pharmaceutical operations across multiple branches. It bridges the gap between complex inventory logistics and real-time sales processing, ensuring regulatory compliance, patient safety, and business profitability through data-driven insights.

---

## 👥 Role-Based Access Control (RBAC)
The system is built on a strict hierarchy to ensure operational integrity and security, as defined in the Software Requirements Specification (SRS).

### 1. 👑 The Manager (Owner/Admin)
*Focus: Strategic Oversight & Administration*
- **Branch Management**: Create, update, and monitor multiple branches.
- **User Administration**: Invite and manage Pharmacists and Store Keepers.
- **Global Inventory**: View stock levels and expiry status across all locations.
- **Advanced Analytics**: Access revenue reports, profit analysis, and sales performance by branch or employee.
- **System Maintenance**: Perform full database backups for disaster recovery.

### 2. 📦 The Store Keeper
*Focus: Inventory Logistics & Supply Chain*
- **Inventory Control**: Add new drugs, update details (manufacturer, supplier, cost), and adjust quantities.
- **Stock Transfers**: Initiate and track stock movements from the warehouse to specific dispensaries.
- **Safety Alerts**: Receive automated notifications for low stock and near-expiry items.

### 3. 💊 The Pharmacist (Druggist)
*Focus: Sales Processing & Patient Safety*
- **Smart POS**: Process sales with real-time stock and expiry validation.
- **Prescription Validation**: Mandatory professional check-off for controlled medications.
- **Personal Tracking**: View daily, weekly, and monthly personal sales performance.
- **Invoicing**: Generate professional, itemized receipts for customers.

---

## 🚀 Key Functional Features

### 🛡️ Safety & Compliance First
- **Automatic Expiry Blocking**: The system proactively blocks the sale of medication that has reached its expiry date.
- **Prescription Checkpoint**: A dedicated validation step ensures pharmacists have verified medical prescriptions before completing a transaction.
- **Expiry Notifications**: Automated daily scans notify staff of drugs expiring within 30 days.

### 📈 Intelligent Inventory
- **Category-Based Search**: Rapidly find medication by name, batch, or therapeutic category (e.g., "Antibiotic").
- **Low Stock Automation**: Real-time alerts are triggered the moment stock drops below a defined threshold (e.g., 10 units).
- **Movement Auditing**: Every stock adjustment is logged with a reason (Sale, Transfer, Manual Correction) and the user responsible.

### 📊 Reports & Analytics
- **Multi-Period Filtering**: Analyze data by Today, Weekly, Monthly, or Custom Date ranges.
- **Top-Moving Drugs**: Identify best-sellers to optimize ordering cycles.
- **Branch Comparison**: Compare performance across different locations to identify growth opportunities.

---

## 💻 Technical Stack
The system is built using a modern, performant, and maintainable architecture:
- **Backend**: PHP (Modular Controller-Model architecture).
- **Database**: MySQL / MariaDB (Relational design with Foreign Key integrity).
- **Security**: PDO Prepared Statements (SQL Injection protection) and Bcrypt Hashing (Password security).
- **Frontend**: Tailwind CSS (Modern, responsive UI), Vanilla JavaScript (Interactive POS and Charts).
- **Branding**: Fully customizable design system with glassmorphism and premium micro-animations.

---

## 🛠️ Installation & Setup
1. **Environment**: Best run on XAMPP (Apache + MySQL).
2. **Database**: 
   - Run `database/schema.sql` to create the structure.
   - Run `database/seed.sql` for professional demo data.
3. **Reset Utility**: Use the included `reset_database.php` for a one-click fresh start.
4. **Email Config**: Set your SMTP credentials in `backend/config/config.local.php` to enable real-time notifications and user invitations.

---

## 🎯 Value Proposition
PharmaFlow transforms a traditional pharmacy into a **Smart Pharmacy**. By automating the "boring" parts of inventory and enforcing "critical" safety checks, it allows healthcare professionals to focus on what matters most: **Patient Care.**
