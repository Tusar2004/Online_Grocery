# 🛒 Online Grocery Store Database

This project contains the SQL schema and sample data for an **Online Grocery Store Management System**. It is designed to support essential features such as user management, product cataloging, order processing, payment tracking, and delivery logistics.

## 📄 File Included

- `online_grocery.sql`:  
  Contains SQL statements to:
  - Create all necessary tables
  - Define relationships with primary and foreign keys
  - Populate the database with sample data

## 🧰 Features

- **User Accounts** – Handles customer registration and login information
- **Product Management** – Categories, items, pricing, stock levels
- **Cart and Orders** – Shopping cart functionality, order placement, order status
- **Payments** – Records transactions and payment details
- **Delivery Tracking** – Stores addresses and shipment tracking data

## 💾 Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/Tusar2004/online-grocery-store-db.git
   cd online-grocery-store-db
Import the database into MySQL:

SOURCE path/to/online_grocery.sql;

Or via command line:
mysql -u your_username -p your_database_name < online_grocery.sql
Modify table or data as needed for integration with your backend application (e.g., PHP, Node.js, Django).

🛠️ Technologies
SQL (MySQL / MariaDB)

Relational Database Design

👨‍💻 Author
Tusar Goswami

GitHub: @Tusar2004


💡 This database is intended to be used as a backend for a full-stack grocery application. Pair it with your favorite backend technology to enable complete CRUD operations and user interface.


Let me know if you’d like me to add an ER diagram, sample queries, or API documentation next!
