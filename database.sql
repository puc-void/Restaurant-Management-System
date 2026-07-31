-- Restaurant Management System Database Dump for XAMPP / MariaDB / MySQL
-- Database: `online_food`

CREATE DATABASE IF NOT EXISTS `online_food` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `online_food`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_plain` VARCHAR(255) NOT NULL,
  `password_md5` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `avatar_url` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_plain` VARCHAR(255) NOT NULL,
  `password_md5` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE IF NOT EXISTS `restaurants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'General',
  `opening_hours` VARCHAR(100) DEFAULT '10:00 AM - 10:00 PM',
  `image_url` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE IF NOT EXISTS `foods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT(11) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `category` VARCHAR(50) DEFAULT 'Main Course',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `image_url` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_foods_restaurant` (`restaurant_id`),
  CONSTRAINT `fk_foods_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
  `payment_number` VARCHAR(50) DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_orders_user` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `food_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `fk_items_order` (`order_id`),
  KEY `fk_items_food` (`food_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_food` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews` (NEW FEATURE)
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `food_id` INT(11) DEFAULT NULL,
  `restaurant_id` INT(11) DEFAULT NULL,
  `rating` INT(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reviews_user` (`user_id`),
  KEY `fk_reviews_food` (`food_id`),
  KEY `fk_reviews_restaurant` (`restaurant_id`),
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_food` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Seed Data Insertion
--

-- Default Admin Account (Email: admin@restaurant.com, Pass: admin123)
INSERT INTO `admins` (`id`, `name`, `email`, `password_plain`, `password_md5`, `created_at`) VALUES
(1, 'System Admin', 'admin@restaurant.com', 'admin123', '0192023a7bbd73250516f069df18b500', NOW());

-- Seed Users (Pass: password123)
INSERT INTO `users` (`id`, `name`, `email`, `password_plain`, `password_md5`, `phone`, `address`, `created_at`) VALUES
(1, 'John Doe', 'john@example.com', 'password123', '482c811da5d5b4bc6c497ffa98491e38', '01711223344', '123 Green Street, Dhaka, Bangladesh', NOW()),
(2, 'Jane Smith', 'jane@example.com', 'password123', '482c811da5d5b4bc6c497ffa98491e38', '01899887766', '456 Blue Avenue, Chittagong, Bangladesh', NOW());

-- Seed Restaurants
INSERT INTO `restaurants` (`id`, `name`, `email`, `phone`, `address`, `category`, `opening_hours`, `image_url`, `created_at`) VALUES
(1, 'Gourmet Burger Haven', 'contact@burgerhaven.com', '+880 1700-111222', '78 Foodie Boulevard, Gulshan, Dhaka', 'Fast Food', '10:00 AM - 11:00 PM', 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=800&q=80', NOW()),
(2, 'Bella Italia Bistro', 'info@bellaitalia.com', '+880 1700-333444', '12 Italian Way, Banani, Dhaka', 'Italian', '11:30 AM - 10:30 PM', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80', NOW()),
(3, 'Spice Garden Indian', 'reserve@spicegarden.com', '+880 1700-555666', '45 Curry Lane, Dhanmondi, Dhaka', 'Indian', '12:00 PM - 10:00 PM', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80', NOW()),
(4, 'Tokyo Sushi & Ramen Bar', 'hello@tokyosushi.com', '+880 1700-777888', '89 Sakura Avenue, Uttara, Dhaka', 'Asian', '12:00 PM - 11:00 PM', 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=800&q=80', NOW());

-- Seed Foods / Dishes
INSERT INTO `foods` (`id`, `restaurant_id`, `name`, `description`, `price`, `category`, `is_active`, `image_url`, `created_at`) VALUES
(1, 1, 'Classic BBQ Bacon Cheeseburger', 'Juicy grilled beef patty with crispy bacon, melted cheddar cheese, lettuce, and smoky barbecue sauce on a brioche bun.', 12.99, 'Fast Food', 1, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80', NOW()),
(2, 1, 'Crispy Spicy Chicken Wings', '8 pieces of crispy fried chicken wings tossed in fiery hot sauce served with creamy ranch dip.', 8.99, 'Fast Food', 1, 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?auto=format&fit=crop&w=800&q=80', NOW()),
(3, 1, 'Double Cheese Truffle Fries', 'Golden French fries smothered in melted Gruyère cheese, truffle oil, and fresh herbs.', 6.50, 'Fast Food', 1, 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=800&q=80', NOW()),
(4, 2, 'Authentic Neapolitan Pepperoni Pizza', 'Hand-tossed pizza with San Marzano tomato sauce, fresh mozzarella, spicy pepperoni, and basil.', 15.99, 'Pizza', 1, 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?auto=format&fit=crop&w=800&q=80', NOW()),
(5, 2, 'Creamy Fettuccine Alfredo', 'Rich egg fettuccine tossed in a parmesan cream sauce with garlic and grilled chicken breast.', 14.50, 'Italian', 1, 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?auto=format&fit=crop&w=800&q=80', NOW()),
(6, 2, 'Classic Tiramisu Dessert', 'Espresso-soaked ladyfingers layered with whipped mascarpone cream and cocoa powder.', 7.00, 'Desserts', 1, 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=800&q=80', NOW()),
(7, 3, 'Butter Chicken Supreme', 'Tender chicken pieces cooked in a rich, buttery tomato gravy with Indian spices and fresh cream.', 13.99, 'Indian', 1, 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=800&q=80', NOW()),
(8, 3, 'Garlic Butter Naan Bread', 'Freshly baked oven naan brushed with garlic butter and fresh cilantro.', 3.50, 'Indian', 1, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=800&q=80', NOW()),
(9, 3, 'Hyderabadi Chicken Biryani', 'Aromatic basmati rice cooked with marinated chicken, saffron, herbs, and caramelized onions.', 14.99, 'Indian', 1, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=800&q=80', NOW()),
(10, 4, 'Dragon Salmon Roll (8pcs)', 'Fresh salmon roll topped with avocado, spicy mayo, unagi sauce, and crispy tempura flakes.', 16.50, 'Asian', 1, 'https://images.unsplash.com/photo-1611143669185-af224c5e3252?auto=format&fit=crop&w=800&q=80', NOW()),
(11, 4, 'Tonkotsu Pork Ramen', 'Rich pork bone broth served with springy noodles, chashu pork, soft-boiled egg, and green onions.', 13.50, 'Asian', 1, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80', NOW()),
(12, 4, 'Matcha Green Tea Boba', 'Refreshing iced green tea boba milk drink with tapioca pearls.', 5.50, 'Beverages', 1, 'https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=800&q=80', NOW());

-- Seed Sample Orders
INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `payment_method`, `payment_number`, `shipping_address`, `created_at`) VALUES
(1001, 1, 28.48, 'Delivered', 'Cash on Delivery', NULL, '123 Green Street, Dhaka, Bangladesh', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1002, 1, 18.49, 'Out for Delivery', 'bKash', '01711223344', '123 Green Street, Dhaka, Bangladesh', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1003, 2, 30.49, 'Preparing', 'Nagad', '01899887766', '456 Blue Avenue, Chittagong, Bangladesh', NOW());

-- Seed Sample Order Items
INSERT INTO `order_items` (`id`, `order_id`, `food_id`, `quantity`, `price`) VALUES
(1, 1001, 1, 1, 12.99),
(2, 1001, 3, 1, 6.50),
(3, 1001, 2, 1, 8.99),
(4, 1002, 7, 1, 13.99),
(5, 1002, 8, 1, 3.50),
(6, 1003, 4, 1, 15.99),
(7, 1003, 5, 1, 14.50);

-- Seed Sample Reviews
INSERT INTO `reviews` (`id`, `user_id`, `food_id`, `restaurant_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 1, 5, 'Absolute best BBQ burger in town! Perfectly juicy and crispy bacon.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 2, 4, 2, 5, 'The crust on this Neapolitan pizza is crisp and airy. Highly recommended!', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 1, 7, 3, 4, 'Rich and flavorful butter chicken with fresh garlic naan. Will order again.', NOW());
