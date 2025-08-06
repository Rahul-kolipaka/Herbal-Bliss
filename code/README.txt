# 🌿 Herbal Bliss - E-commerce Platform

Herbal Bliss is a web-based platform designed to showcase and sell herbal products. This repository contains the source code for the Herbal Bliss website, built using **PHP, HTML, CSS, JavaScript, MySQL**.

---

## 🚀 Features

- 🛍️ Product catalog with detailed descriptions  
- 🛒 Shopping cart functionality  
- 👤 User registration and login  
- 📦 Order management  
- 📱 Responsive design for mobile and desktop  

---

## 🛠 Getting Started

### ✅ Prerequisites

- XAMPP 
- PHP   
- MySQL
- HTML, CSS, JavaScript  

---

## 📁 Project Folder Structure

HerbalBliss/
├── README.md
└── code/
├── index.php
├── admin/ # Admin dashboard and user/order management
│ ├── admin_dashboard.php
│ ├── admin_login.php
│ ├── admin_logout.php
│ ├── admin_register.php
│ ├── admin_orders.php
│ ├── admin_order_details.php
│ ├── admin_update_order.php
│ ├── admin_user_order.php
│ └── admin_users.php
├── auth/ # User login, registration, logout, and auth
│ ├── login.php
│ ├── logout.php
│ ├── register.php
│ └── auth.php
├── orders/ # User order flow & cart
│ ├── checkout.php
│ ├── my_orders.php
│ ├── order_confirmation.php
│ ├── order_details.php
│ ├── order_tracking.php
│ └── save_cart.php
├── payment/ # Payment flow
│ ├── payment.php
│ └── payment_process.php
├── config/ # Database connection
│ └── config.php
├── db script/ # Database schema
│ └── mysql_script.sql
└── assets/ # Static files
├── style.css
└── script.js

---

## ⚙️ Installation

### 1. Clone the repository:

git clone https://github.com/Rahul-Kolipaka/Herbal-Bliss.git

2. Import the database:
Open phpMyAdmin

Create a new database (e.g., herbal_bliss)

Import the file: db script/mysql_script.sql

3. Configure database connection:
Open code/config/config.php and edit the following:

$db_name = "herbal_bliss";
$db_user = "root";
$db_pass = ""; // your database password

4. Run the application:
Move the project to the XAMPP htdocs folder:

C:\xampp\htdocs\HerbalBliss
Start Apache and MySQL from XAMPP.

Open your browser and go to:
http://localhost/code/index.php

🧑‍💻 Usage
Browse products, add them to your cart, and proceed to checkout.

Register and log in to track your orders and view order history.

🛠 Important Notes
If you reorganize folders (e.g., move login.php to auth/), make sure to update all file paths and redirects like:

header("Location: ../auth/login.php");
Also update paths to assets:
<link rel="stylesheet" href="../assets/style.css">

🤝 Contributing
Pull requests are welcome!
For major changes, please open an issue first to discuss what you would like to change.

📃 License
This project is for educational use. You're free to modify and share.
