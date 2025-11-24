SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Hapus tabel lama biar bersih
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `users`;

-- 1. Tabel Orders
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal_price` int(11) NOT NULL,
  `shipping_cost` int(11) NOT NULL DEFAULT 0,
  `total_price` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `selected_variant` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Products (20 Dummy Data)
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `description` text NOT NULL,
  `available_variants` text DEFAULT NULL,
  `shipping_cost` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`name`, `price`, `stock`, `description`, `available_variants`, `shipping_cost`, `image`) VALUES
('Basreng Pedas', 15000, 50, 'Pedas nampol.', 'Level 1,Level 5', 5000, 'https://loremflickr.com/600/600/snack,spicy?random=1'),
('Keripik Kaca', 12000, 100, 'Tipis renyah.', 'Original,Pedas', 5000, 'https://loremflickr.com/600/600/chips?random=2'),
('Makaroni Bantet', 10000, 45, 'Kriuk abis.', 'Asin,Pedas', 5000, 'https://loremflickr.com/600/600/pasta?random=3'),
('Usus Crispy', 18000, 30, 'Gurih pol.', 'Original,Balado', 5000, 'https://loremflickr.com/600/600/fried?random=4'),
('Cimol Kering', 13000, 60, 'Renyah.', 'Keju,Pedas', 5000, 'https://loremflickr.com/600/600/food?random=5'),
('Lidi-lidian', 8000, 150, 'Nostalgia.', 'Asin,Pedas', 5000, 'https://loremflickr.com/600/600/snack?random=6'),
('Seblak Instan', 20000, 25, 'Tinggal seduh.', 'Biasa,Spesial', 8000, 'https://loremflickr.com/600/600/soup?random=7'),
('Cuanki Cup', 15000, 40, 'Kuah segar.', NULL, 8000, 'https://loremflickr.com/600/600/bowl?random=8'),
('Batagor Kuah', 16000, 35, 'Ikan asli.', 'Pedas,Sedang', 8000, 'https://loremflickr.com/600/600/meatball?random=9'),
('Pilus Cikur', 11000, 80, 'Teman makan.', NULL, 5000, 'https://loremflickr.com/600/600/white?random=10'),
('Kerupuk Seblak', 12000, 55, 'Bantet gurih.', 'Daun Jeruk,Cikur', 5000, 'https://loremflickr.com/600/600/cracker?random=11'),
('Sus Coklat', 25000, 20, 'Lumer.', 'Coklat,Matcha', 5000, 'https://loremflickr.com/600/600/chocolate?random=12'),
('Soes Kering', 22000, 25, 'Gurih asin.', 'Keju,Ori', 5000, 'https://loremflickr.com/600/600/pastry?random=13'),
('Sumpia Udang', 28000, 15, 'Udang asli.', NULL, 5000, 'https://loremflickr.com/600/600/shrimp?random=14'),
('Sale Pisang', 18000, 40, 'Manis legit.', 'Basah,Kering', 5000, 'https://loremflickr.com/600/600/banana?random=15'),
('Keripik Tempe', 14000, 65, 'Sagu renyah.', 'Ori,Pedas', 5000, 'https://loremflickr.com/600/600/tempeh?random=16'),
('Tahu Walik', 20000, 10, 'Frozen.', 'Isi 10,Isi 20', 15000, 'https://loremflickr.com/600/600/tofu?random=17'),
('Cireng Rujak', 18000, 12, 'Bumbu rujak.', NULL, 10000, 'https://loremflickr.com/600/600/fried?random=18'),
('Cilok Goang', 22000, 15, 'Kuah pedas.', NULL, 10000, 'https://loremflickr.com/600/600/spicy?random=19'),
('Es Lilin', 5000, 100, 'Segar.', 'Coklat,Buah', 20000, 'https://loremflickr.com/600/600/icecream?random=20');

-- 3. Tabel Settings (Isi Singkat)
CREATE TABLE `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('admin_password', '$2y$10$f5.3.D/S5Z.g8e.E2QpY.OFb6v4zO8T5bGIiB.P61.lS9X/3GKuB2'),
('admin_user', 'admin'),
('owner_name', 'Admin'),
('payment_bank_account', '123456 (BCA)'),
('payment_bank_name', 'BCA'),
('payment_ewallet_name', 'Dana'),
('payment_ewallet_number', '08123456789'),
('shop_address', 'Jl. Snack No. 1'),
('shop_about_image', 'https://loremflickr.com/600/600/store'),
('shop_about_text', 'Snack enak, harga pas.'),
('shop_email', 'hi@snacklab.id'),
('shop_name', 'Snack Lab'),
('shop_whatsapp', '628123456789');

-- 4. Tabel Users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `username`, `password_hash`) VALUES
(1, 'admin', '$2y$10$f5.3.D/S5Z.g8e.E2QpY.OFb6v4zO8T5bGIiB.P61.lS9X/3GKuB2');

-- Constraints
ALTER TABLE `orders` ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

COMMIT;