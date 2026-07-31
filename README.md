# 🍽️ GourmetHub - Restaurant Management & Food Ordering System

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-XAMPP-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![DaisyUI](https://img.shields.io/badge/DaisyUI-4.x-5A0E2D?style=for-the-badge&logo=daisyui&logoColor=white)

**GourmetHub** is a modern, responsive full-stack **Restaurant Management System & Online Food Ordering Platform** built with PHP, MySQL, Tailwind CSS, and DaisyUI 4. It provides a seamless food ordering experience for customers and a feature-rich administration control panel for restaurant management.

---

## ✨ Features Breakdown

### 🛍️ Customer Front-End Portal
* **DaisyUI Modern Design & Modal Popups**: Built with modern typography, smooth glassmorphism, responsive cards, and **interactive DaisyUI `<dialog>` modals / popups** across all features (Quick Dish View, Review Submissions, Cart Confirmations, and Admin Actions).
* **Multi-Theme Switcher**: Live theme switching with persistence in `localStorage` supporting **Emerald** (Default), **Light**, **Dark**, **Cupcake**, **Synthwave**, **Cyberpunk**, and **Retro**.
* **Category & Cuisine Filter Bar**: Instant filtering for Fast Food, Italian, Indian, Asian, Pizza, Desserts, and Beverages.
* **Star Ratings & Customer Reviews**: 1–5 star ratings and feedback for dishes and partner restaurants with calculated average rating badges.
* **Interactive Cart & Quick Add**: Shopping cart drawer preview, direct "Add to Cart" from homepage cards, quantity updates, and cart item management.
* **Interactive Order Tracking Timeline**: Real-time step progress indicator (`Order Placed ➔ Preparing ➔ Out for Delivery ➔ Delivered`).
* **Multi-Payment Gateway Options**: Support for Cash on Delivery, bKash, Nagad, Rocket, and Credit/Debit Cards.
* **User Profile & Address Management**: Edit name, phone, default delivery address, and change passwords on `profile.php`.
* **1-Click Quick Re-Order**: Re-load items from past orders directly into the active cart from the user dashboard.

### 🛡️ Admin Control Panel
* **Analytics Metrics & Statistics**: Real-time tracking of Total Revenue ($), Total Orders, Active Dishes, Partner Restaurants, Registered Users, and Order Status breakdowns.
* **Order Management & Status Progression**: Advance order status transitions (`Pending` ➔ `Preparing` ➔ `Out for Delivery` ➔ `Delivered` / `Cancelled`).
* **Dish & Menu Management**: Add, edit, search, filter, and toggle active status (`is_active`) for menu items with instant AJAX updates.
* **Restaurant Partner Management**: Manage restaurant details, cuisine categories, contact info, opening hours, and banner photos.
* **User Account Management**: Admin capability to manage registered customer profiles and reset user passwords.

---

## 🛠️ Technology Stack

| Layer | Technologies Used |
| :--- | :--- |
| **Backend** | PHP 8.x, MySQLi Prepared Statements |
| **Database** | MySQL / MariaDB (XAMPP / phpMyAdmin) |
| **Frontend Styling** | Tailwind CSS CDN, DaisyUI 4.x |
| **Icons & Typography** | FontAwesome 6, Google Fonts (Outfit & Inter) |
| **Client Logic** | JavaScript (ES6+), jQuery 3.7 |

---

## 🚀 Quick Setup & Installation (XAMPP)

### 1. Prerequisites
* Install [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 7.4/8.x).

### 2. Project Setup
Place the project directory into your XAMPP `htdocs` folder:
```bash
# Path for XAMPP
htdocs/Restaurant-Management-System/
```

### 3. Database Import via phpMyAdmin
1. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open your browser and navigate to `http://localhost/phpmyadmin`.
3. Click on the **Import** tab at the top.
4. Click **Choose File** and select `database.sql` located at the root of the project:
   ```
   Restaurant-Management-System/database.sql
   ```
5. Click **Import** (or **Go**). The script will automatically create the `online_food` database and populate all tables with sample seed data.

### 4. Database Configuration (Optional)
Database credentials are pre-configured for standard XAMPP in `includes/config.php`:
```php
$host = "localhost";
$user = "root";
$pass = "";         // Default XAMPP MySQL password
$db   = "online_food";
```

### 5. Access the Web Application
* **Customer Front-End Portal**: `http://localhost/Restaurant-Management-System/`
* **Admin Control Panel**: `http://localhost/Restaurant-Management-System/admin/`

---

## 🔑 Default Login Credentials

### 🛡️ Admin Portal (`/admin/login.php`)
| Role | Email | Password |
| :--- | :--- | :--- |
| **System Admin** | `admin@restaurant.com` | `admin123` |

### 👤 Customer Account (`/login.php`)
| Customer | Email | Password |
| :--- | :--- | :--- |
| **John Doe** | `john@example.com` | `password123` |
| **Jane Smith** | `jane@example.com` | `password123` |

---

## 🗂️ Project Directory Structure

```text
Restaurant-Management-System/
├── admin/                     # Admin Control Panel Directory
│   ├── add_food.php           # Add new dish form
│   ├── add_restaurant.php     # Add new restaurant form
│   ├── dashboard.php          # Admin analytics dashboard
│   ├── edit_food.php          # Edit dish form
│   ├── edit_restaurant.php    # Edit restaurant form
│   ├── edit_user.php          # Edit user account form
│   ├── footer.php             # Admin layout footer
│   ├── header.php             # Admin layout navbar & sidebar drawer
│   ├── login.php              # Admin login page
│   ├── logout.php             # Admin session logout
│   ├── manage_foods.php       # Manage dishes table with AJAX filter
│   ├── manage_orders.php      # Manage customer orders
│   ├── manage_restaurants.php # Manage restaurants table
│   ├── manage_users.php       # Manage registered users
│   └── order_details.php      # Admin order details & status update
├── assets/                    # Static JavaScript assets
│   └── js/
│       ├── checkout.js
│       ├── search.js
│       ├── update.js
│       └── update_restaurant.js
├── includes/                  # Shared PHP Include Files
│   ├── config.php             # Database connection setup
│   ├── footer.php             # Shared front-end footer
│   ├── functions.php          # Rating, cart & helper functions
│   └── header.php             # Shared front-end DaisyUI navbar
├── cart.php                   # Shopping cart page
├── checkout.php               # Order summary & payment gateway page
├── create_admin.php           # Create initial admin account
├── dashboard.php              # Customer dashboard & order history
├── database.sql               # XAMPP database dump file
├── dish.php                   # Dish details & customer reviews page
├── index.php                  # Homepage with hero, categories & menu
├── login.php                  # Customer login page
├── logout.php                 # Customer session logout
├── order_details.php          # Live order tracking timeline page
├── profile.php                # Customer profile & address management
├── register.php               # Customer registration page
├── restaurant.php             # Restaurant profile & dishes menu page
├── search.php                 # Live menu search & filter page
└── README.md                  # Project documentation
```

---

## 💾 Database Schema Overview

* `users`: `id`, `name`, `email`, `password_plain`, `password_md5`, `phone`, `address`, `created_at`
* `admins`: `id`, `name`, `email`, `password_plain`, `password_md5`, `created_at`
* `restaurants`: `id`, `name`, `email`, `phone`, `address`, `category`, `opening_hours`, `image_url`, `created_at`
* `foods`: `id`, `restaurant_id`, `name`, `description`, `price`, `category`, `is_active`, `image_url`, `created_at`
* `orders`: `id`, `user_id`, `total`, `status`, `payment_method`, `payment_number`, `shipping_address`, `created_at`
* `order_items`: `id`, `order_id`, `food_id`, `quantity`, `price`
* `reviews`: `id`, `user_id`, `food_id`, `restaurant_id`, `rating`, `comment`, `created_at`

---

## 📜 License

This project is open-source under the **MIT License**. Feel free to use, modify, and distribute for educational or commercial purposes.
