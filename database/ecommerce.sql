-- NextGen E-Commerce Database Schema
-- Compatible with MySQL / MariaDB and phpMyAdmin

CREATE DATABASE IF NOT EXISTS `nextgen_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nextgen_ecommerce`;

-- --------------------------------------------------------
-- Users Table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(50) DEFAULT NULL,
  `state` VARCHAR(50) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Users (Password for admin: Admin@123, user: User@123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `city`, `state`, `pincode`, `role`) VALUES
(1, 'System Administrator', 'admin@nextgen.com', '$2y$10$w8T0M4j/c909e7F4A.2t3O5hU4tP9h5pQG4nQ8v6X5y5Z5y5Z5y5Z', '9876543210', '123 Tech Park', 'Bangalore', 'Karnataka', '560001', 'admin'),
(2, 'Alex Johnson', 'alex@example.com', '$2y$10$w8T0M4j/c909e7F4A.2t3O5hU4tP9h5pQG4nQ8v6X5y5Z5y5Z5y5Z', '9876543211', '45 Innovation Way', 'Mumbai', 'Maharashtra', '400001', 'customer');

-- --------------------------------------------------------
-- Categories Table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon_class` VARCHAR(50) DEFAULT 'fa-box',
  `image_url` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon_class`, `image_url`) VALUES
(1, 'Laptops & Computers', 'laptops-computers', 'High performance laptops for gaming, coding, and productivity.', 'fa-laptop', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80'),
(2, 'Smartphones & Mobile', 'smartphones-mobile', 'Latest flagship smartphones, foldable devices, and accessories.', 'fa-mobile-screen-button', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80'),
(3, 'Audio & Headphones', 'audio-headphones', 'Noise cancelling headphones, wireless earbuds, and spatial sound.', 'fa-headphones', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80'),
(4, 'Gaming & Consoles', 'gaming-consoles', 'Next-gen gaming gear, mechanical keyboards, monitors, and controllers.', 'fa-gamepad', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=600&q=80'),
(5, 'Smart Wearables', 'smart-wearables', 'Smartwatches, fitness bands, and health tracking wearables.', 'fa-clock', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80'),
(6, 'Cameras & Accessories', 'cameras-accessories', 'Mirrorless cameras, studio lighting, drones, and vlogging equipment.', 'fa-camera', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80');

-- --------------------------------------------------------
-- Products Table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `short_description` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `original_price` DECIMAL(10, 2) DEFAULT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 10,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `rating` DECIMAL(3, 2) DEFAULT 4.50,
  `total_reviews` INT DEFAULT 12,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_trending` TINYINT(1) DEFAULT 0,
  `tags` VARCHAR(255) DEFAULT NULL, -- AI extraction keywords e.g. "coding,16gb,battery,gaming,lightweight"
  `specs_json` TEXT DEFAULT NULL, -- Key-value parameters for AI Match Scoring
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `price`, `original_price`, `stock_quantity`, `image_url`, `rating`, `total_reviews`, `is_featured`, `is_trending`, `tags`, `specs_json`) VALUES
(1, 1, 'ZenBook Pro 16 AI Ultra', 'zenbook-pro-16-ai-ultra', 'Ultra-thin AI laptop with 16GB RAM, OLED 120Hz display & 14hr battery life.', 'Engineered for developers, creators, and power users. Powered by Intel Core Ultra 7 processor, 16GB LPDDR5X RAM, 1TB NVMe SSD, and dedicated NPU for fast local AI processing.', 58999.00, 69999.00, 15, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80', 4.90, 48, 1, 1, 'laptop,coding,college,16gb,battery,oled,intel,thin', '{"ram":"16GB","storage":"1TB SSD","battery":"14 Hours","cpu":"Intel Core Ultra 7","weight":"1.4 kg","screen":"16-inch OLED"}'),

(2, 1, 'Legion Pro Gaming 5i', 'legion-pro-gaming-5i', 'High performance gaming laptop with RTX 4060, 16GB DDR5, 165Hz Display.', 'Unleash full gaming potential. Features 16GB DDR5 RAM, 512GB SSD, NVIDIA GeForce RTX 4060 8GB GPU, Coldfront 5.0 cooling, and RGB backlit keyboard.', 64999.00, 74999.00, 8, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=800&q=80', 4.80, 36, 1, 1, 'laptop,gaming,coding,16gb,rtx4060,gpu,performance', '{"ram":"16GB","storage":"512GB SSD","battery":"6 Hours","cpu":"Intel Core i7-13700HX","weight":"2.3 kg","gpu":"NVIDIA RTX 4060"}'),

(3, 1, 'Swift Go 14 Student Edition', 'swift-go-14-student-edition', 'Budget student laptop with 16GB RAM, Ryzen 5, and all-day battery.', 'The ultimate companion for college and daily productivity. Features AMD Ryzen 5 7530U, 16GB RAM, 512GB SSD, anti-glare FHD screen, and lightweight aluminum chassis.', 44999.00, 52999.00, 25, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80', 4.60, 29, 0, 1, 'laptop,student,college,coding,16gb,budget,lightweight,battery', '{"ram":"16GB","storage":"512GB SSD","battery":"11 Hours","cpu":"AMD Ryzen 5 7530U","weight":"1.25 kg","screen":"14-inch FHD"}'),

(4, 2, 'Galaxy S25 Ultra 5G', 'galaxy-s25-ultra-5g', 'Flagship AI smartphone with 200MP camera, Snapdragon 8 Elite & S-Pen.', 'Experience next generation mobile technology with Galaxy AI, titanium frame, 12GB RAM, 256GB storage, and quad camera setup.', 79999.00, 89999.00, 12, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=800&q=80', 4.90, 64, 1, 1, 'phone,smartphone,5g,camera,flagship,galaxy,spen,ai', '{"ram":"12GB","storage":"256GB","battery":"5000 mAh","camera":"200MP Quad","screen":"6.8-inch AMOLED 120Hz"}'),

(5, 2, 'Pixel 9 Pro 5G', 'pixel-9-pro-5g', 'Pure Android AI phone with Tensor G4, pro camera controls & 16GB RAM.', 'Advanced AI studio in your pocket. Features Google Tensor G4, 16GB RAM, 128GB storage, super res zoom, and 7 years of OS updates.', 68999.00, 75999.00, 10, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=800&q=80', 4.70, 22, 1, 0, 'phone,smartphone,5g,camera,android,pixel,ai,16gb', '{"ram":"16GB","storage":"128GB","battery":"4700 mAh","camera":"50MP Triple Pro","screen":"6.3-inch Super Actua"}'),

(6, 3, 'SonicPro Wireless ANC Headphones', 'sonicpro-wireless-anc-headphones', 'Hybrid Active Noise Cancelling headphones with 40-hour battery and HD mic.', 'Block out background noise while studying or working. Features 40mm titanium drivers, multipoint Bluetooth 5.4, ultra-clear microphone for calls, and quick charge.', 4499.00, 6999.00, 40, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80', 4.80, 85, 1, 1, 'headphones,audio,anc,wireless,mic,gaming,music,budget,battery', '{"battery":"40 Hours","anc":"Hybrid ANC 42dB","mic":"AI Dual Mic","driver":"40mm Titanium","weight":"240g"}'),

(7, 3, 'AirSound Pro True Wireless Earbuds', 'airsound-pro-tws-earbuds', 'Low-latency TWS earbuds with ANC, spatial audio, and IPX5 water resistance.', 'Compact audiophile sound. Includes spatial audio, 8ms ultra low latency mode for mobile gaming, touch controls, and 30-hour total playback with USB-C case.', 2999.00, 4999.00, 60, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80', 4.60, 53, 0, 1, 'earbuds,audio,tws,gaming,wireless,anc,budget,compact', '{"battery":"30 Hours Total","latency":"8ms","waterproof":"IPX5","anc":"30dB ANC"}'),

(8, 4, 'Apex Precision RGB Mechanical Keyboard', 'apex-precision-rgb-mechanical-keyboard', 'Wireless hot-swappable mechanical keyboard with custom linear switches.', 'Designed for fast typing and low-latency gaming. PBT keycaps, per-key RGB lighting, tri-mode connection (Bluetooth/2.4G/USB-C), and sound dampening foam.', 3999.00, 5499.00, 30, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80', 4.85, 41, 1, 1, 'keyboard,gaming,mechanical,coding,rgb,wireless,accessories', '{"switches":"Custom Hot-Swap Linear","layout":"75% Compact","battery":"4000 mAh","connection":"Bluetooth/2.4G/USB-C"}'),

(9, 4, 'Pulsefire Ultra Wireless Gaming Mouse', 'pulsefire-ultra-wireless-gaming-mouse', 'Ultra-lightweight 49g wireless mouse with 26K DPI sensor.', 'Maximum precision for esports and productivity. 26,000 DPI optical sensor, 80 million click optical switches, PTFE feet, and 90-hour battery life.', 2499.00, 3999.00, 35, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=800&q=80', 4.75, 38, 0, 1, 'mouse,gaming,wireless,coding,accessories,lightweight', '{"weight":"49g","sensor":"26K DPI Optical","battery":"90 Hours","switches":"Optical 80M Clicks"}'),

(10, 5, 'FitPulse Watch 3 Pro', 'fitpulse-watch-3-pro', 'AMOLED smartwatch with GPS, SpO2, Heart Rate, and 12-day battery.', 'Track fitness, sleep, stress, and receive Bluetooth phone calls. IP68 water resistance, customizable watch faces, and stainless steel bezel.', 3499.00, 5999.00, 50, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80', 4.70, 77, 1, 1, 'smartwatch,watch,wearable,fitness,gps,battery,budget', '{"screen":"1.43-inch AMOLED","battery":"12 Days","sensors":"Heart Rate, SpO2, Sleep, GPS","waterproof":"IP68"}');

-- --------------------------------------------------------
-- Orders & Order Items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `discount_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `final_amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'UPI',
  `payment_status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Paid',
  `order_status` ENUM('Processing', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Processing',
  `shipping_name` VARCHAR(100) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `city` VARCHAR(50) NOT NULL,
  `state` VARCHAR(50) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `tracking_number` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `quantity` INT NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Order
INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `discount_amount`, `final_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_name`, `shipping_phone`, `shipping_address`, `city`, `state`, `pincode`, `tracking_number`, `created_at`) VALUES
(1001, 2, 58999.00, 0.00, 58999.00, 'UPI / QR', 'Paid', 'Shipped', 'Alex Johnson', '9876543211', '45 Innovation Way', 'Mumbai', 'Maharashtra', '400001', 'TRACK-NEXTGEN-8942', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1001, 1, 'ZenBook Pro 16 AI Ultra', 58999.00, 1, 58999.00);

-- --------------------------------------------------------
-- Wishlist & Cart Tables
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Reviews & Coupons
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reviews` (`product_id`, `user_id`, `user_name`, `rating`, `comment`) VALUES
(1, 2, 'Alex Johnson', 5, 'Super fast laptop! Handles heavy VS Code sessions and local AI models effortlessly. Battery lasts 12+ hours easily.'),
(6, 2, 'Alex Johnson', 5, 'Noise cancelling is top notch for studying. The microphone clear sound surprised me.');

CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `discount_percentage` INT NOT NULL,
  `min_order_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `max_discount` DECIMAL(10, 2) DEFAULT 5000.00,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `coupons` (`code`, `discount_percentage`, `min_order_amount`, `max_discount`) VALUES
('NEXTGEN10', 10, 1000.00, 2000.00),
('AI500', 15, 3000.00, 3000.00),
('WELCOME20', 20, 5000.00, 5000.00);

-- --------------------------------------------------------
-- AI Requirement Log & Interactions Matrix
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_requirements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `raw_prompt` TEXT NOT NULL,
  `parsed_category` VARCHAR(50) DEFAULT NULL,
  `parsed_budget` DECIMAL(10, 2) DEFAULT NULL,
  `parsed_purpose` VARCHAR(100) DEFAULT NULL,
  `parsed_features` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ai_requirements` (`raw_prompt`, `parsed_category`, `parsed_budget`, `parsed_purpose`, `parsed_features`) VALUES
('I need a laptop for coding and college under 60000 with 16GB RAM and long battery', 'laptops-computers', 60000.00, 'coding, college', '16gb ram, battery life'),
('Headphones for gaming and calls under 5000', 'audio-headphones', 5000.00, 'gaming, calls', 'anc, microphone');
