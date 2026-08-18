-- Database Schema for Coffee Shop Management & POS System
-- Database Name: coffeeshop_db

CREATE DATABASE IF NOT EXISTS `coffeeshop_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `coffeeshop_db`;

-- 1. Users Table (Admin & Karyawan)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default User Accounts:
-- Admin    : username = admin    | password = admin123
-- Karyawan : username = karyawan | password = karyawan123
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`) VALUES
(1, 'admin', '$2y$10$a75qmXwBYhrqmuP2WabUtevQ5AxJqTvwKgz0X98v/0tcMw6GGzYne', 'Administrator Utama', 'admin'),
(2, 'karyawan', '$2y$10$bF3api0l/4.7kDC7hdN6M..myJoLdkoNXca87OGj2SBQUcqJKKhZm', 'Karyawan Kasir', 'karyawan')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`);

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'coffee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Espresso Based', 'espresso-based', 'fa-mug-hot'),
(2, 'Manual Brew', 'manual-brew', 'fa-filter'),
(3, 'Non-Coffee', 'non-coffee', 'fa-glass-water'),
(4, 'Food & Main Course', 'food-main', 'fa-utensils'),
(5, 'Pastry & Snacks', 'pastry-snacks', 'fa-cookie-bite')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 3. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`) VALUES
(1, 1, 'Kopi Espresso Dual Shot', 'Ekstraksi kopi murni dengan aroma pekat dan krema tebal.', 18000.00, 'espresso.jpg', 1),
(2, 1, 'Americano Hot/Iced', 'Espresso klasik diseduh dengan air panas atau es segar.', 22000.00, 'americano.jpg', 1),
(3, 1, 'Caffè Latte Warm Dark', 'Espresso lembut dipadukan dengan steamed milk dan microfoam halus.', 26000.00, 'latte.jpg', 1),
(4, 1, 'Cappuccino Vanilla', 'Espresso, susu segar, dan foam tebal dengan sentuhan vanila alami.', 28000.00, 'cappuccino.jpg', 1),
(5, 1, 'Caramel Macchiato', 'Espresso kaya rasa dipadu saus karamel lezat dan foam susu manis.', 32000.00, 'caramel_macchiato.jpg', 1),
(6, 2, 'V60 Pour Over Arabica Gayo', 'Kopi Arabica Gayo pilihan diseduh metode pour over V60 fruitty & floral.', 30000.00, 'v60.jpg', 1),
(7, 2, 'Japanese Drip Iced Coffee', 'Kopi filter es penyegaran tinggi dengan acidity yang seimbang.', 32000.00, 'japanese_drip.jpg', 1),
(8, 3, 'Matcha Green Tea Latte', 'Bubuk matcha Jepang premium dipadu susu segar gurih.', 30000.00, 'matcha.jpg', 1),
(9, 3, 'Chocolate Signature Hot', 'Cokelat hitam pekat lelehan asli disajikan hangat.', 28000.00, 'chocolate.jpg', 1),
(10, 3, 'Berry Refreshing Sparkler', 'Minuman soda dingin rasa beri segar dan daun mint segar.', 27000.00, 'berry_sparkler.jpg', 1),
(11, 4, 'Nasi Goreng Special Coffee Shop', 'Nasi goreng bumbu rempah spesial dengan telur ceplok dan sosis premium.', 35000.00, 'nasgor.jpg', 1),
(12, 4, 'Spaghetti Carbonara Creamy', 'Pasta pasta spageti dengan saus krim gurih dan potongan smoked beef.', 38000.00, 'carbonara.jpg', 1),
(13, 5, 'Butter Croissant Premium', 'Kue pastry khas Prancis renyah berserat tinggi dengan mentega murni.', 24000.00, 'croissant.jpg', 1),
(14, 5, 'French Fries Truffle Seasoning', 'Kentang goreng renyah bumbu truffle dan keju parut parmesan.', 25000.00, 'fries.jpg', 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 4. Tables / Meja Table
CREATE TABLE IF NOT EXISTS `tables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `table_number` VARCHAR(20) NOT NULL UNIQUE,
  `capacity` INT NOT NULL DEFAULT 2,
  `location` VARCHAR(50) DEFAULT 'Indoor Main Area',
  `status` ENUM('available', 'occupied', 'reserved') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tables` (`id`, `table_number`, `capacity`, `location`, `status`) VALUES
(1, 'M-01', 2, 'Indoor Window Side', 'available'),
(2, 'M-02', 2, 'Indoor Window Side', 'available'),
(3, 'M-03', 4, 'Indoor Central', 'available'),
(4, 'M-04', 4, 'Indoor Central', 'available'),
(5, 'M-05', 6, 'Outdoor Terrace', 'available'),
(6, 'M-06', 6, 'Outdoor Terrace', 'available'),
(7, 'VIP-1', 8, 'VIP Meeting Room', 'available')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 5. Reservations Table (Empty & Ready for Demo)
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_code` VARCHAR(20) NOT NULL UNIQUE,
  `table_id` INT NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `customer_email` VARCHAR(100) DEFAULT NULL,
  `reservation_date` DATE NOT NULL,
  `reservation_time` TIME NOT NULL,
  `number_of_guests` INT NOT NULL DEFAULT 1,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`table_id`) REFERENCES `tables`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Orders Table (Empty & Ready for Demo)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(20) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) NOT NULL,
  `table_number` VARCHAR(20) DEFAULT 'Takeaway',
  `order_type` ENUM('dine_in', 'takeaway') DEFAULT 'dine_in',
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
  `order_status` ENUM('pending', 'processing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT 'Cash',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Order Items Table (Empty & Ready for Demo)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Expenses Table (Empty & Ready for Demo)
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT 'Admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
