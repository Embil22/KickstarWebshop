-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Már 16. 00:05
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `kickstar_db`
--
CREATE DATABASE IF NOT EXISTS `kickstar_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kickstar_db`;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('shipping','billing') DEFAULT 'shipping',
  `is_default` tinyint(1) DEFAULT 0,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'Magyarország',
  `zip_code` varchar(10) NOT NULL,
  `city` varchar(50) NOT NULL,
  `street` varchar(255) NOT NULL,
  `house_number` varchar(20) DEFAULT NULL,
  `floor_door` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `type`, `is_default`, `first_name`, `last_name`, `company`, `country`, `zip_code`, `city`, `street`, `house_number`, `floor_door`, `phone`, `created_at`, `updated_at`) VALUES
(1, 1, 'shipping', 1, 'János', 'Kiss', NULL, 'Magyarország', '1011', 'Budapest', 'Fő utca', '10', NULL, '+36301234567', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 1, 'billing', 1, 'János', 'Kiss', NULL, 'Magyarország', '1011', 'Budapest', 'Fő utca', '10', NULL, '+36301234567', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 2, 'shipping', 1, 'Éva', 'Nagy', NULL, 'Magyarország', '6720', 'Szeged', 'Kossuth Lajos sugárút', '25', NULL, '+36307654321', '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','editor','viewer') DEFAULT 'admin',
  `status` enum('active','inactive','locked') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `admins`
--

INSERT INTO `admins` (`id`, `username`, `full_name`, `email`, `password`, `role`, `status`, `last_login`, `last_ip`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Rendszergazda', 'admin@kickstar.hu', '$2y$10$1Cfh1eqGc5JEXrV0e0obweCf96Az3f0XYg2LFL6d6hHmS5ZHXymAy', 'superadmin', 'active', '2026-03-15 23:42:11', '::1', 0, NULL, '2026-03-15 22:17:25', '2026-03-15 22:42:11');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'failed_login', 'Sikertelen bejelentkezés: admin', '::1', NULL, '2026-03-15 22:17:46'),
(2, 1, 'login', 'Sikeres bejelentkezés', '::1', NULL, '2026-03-15 22:17:49'),
(3, 1, 'login', 'Sikeres bejelentkezés', '::1', NULL, '2026-03-15 22:19:11'),
(4, 1, 'login', 'Sikeres bejelentkezés', '::1', NULL, '2026-03-15 22:42:11');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'max_login_attempts', '5', 'number', 'Maximális bejelentkezési kísérletek száma', NULL, '2026-03-15 22:17:25'),
(2, 'lockout_duration', '15', 'number', 'Fiú zárolás időtartama percekben', NULL, '2026-03-15 22:17:25'),
(3, 'session_timeout', '30', 'number', 'Munkamenet időtúllépés percekben', NULL, '2026-03-15 22:17:25'),
(4, 'two_factor_auth', '0', 'boolean', 'Kétfaktoros hitelesítés bekapcsolása', NULL, '2026-03-15 22:17:25'),
(5, 'maintenance_mode', '0', 'boolean', 'Karbantartási mód bekapcsolása', NULL, '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `image`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Férfi cipők', 'ferfi-cipok', 'Férfi sneakerek és sportcipők', NULL, NULL, 1, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 'Női cipők', 'noi-cipok', 'Női sneakerek és divatcipők', NULL, NULL, 2, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 'Gyerek cipők', 'gyerek-cipok', 'Gyerek sneakerek', NULL, NULL, 3, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(4, 'Sportcipők', 'sportcipok', 'Professzionális sportcipők', NULL, NULL, 4, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(5, 'Casual cipők', 'casual-cipok', 'Hétköznapi viseletre', NULL, NULL, 5, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'Kiss János', 'kiss.janos@email.com', '+36301234567', 'Méret információ', 'Szeretném tudni, hogy a Nike Air Max 270 cipőből van-e 45-ös méret?', 'new', '192.168.1.100', '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `per_user_limit` int(11) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order_amount`, `max_discount`, `usage_limit`, `used_count`, `per_user_limit`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'KICK10', 'percent', 10.00, 10000.00, NULL, 100, 0, 1, '2026-03-15 23:17:25', '2026-04-14 23:17:25', 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 'KICK20', 'percent', 20.00, 20000.00, NULL, 50, 0, 1, '2026-03-15 23:17:25', '2026-04-14 23:17:25', 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 'FREE1000', 'fixed', 1000.00, 5000.00, NULL, 200, 0, 1, '2026-03-15 23:17:25', '2026-03-30 23:17:25', 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` datetime DEFAULT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `newsletter`
--

INSERT INTO `newsletter` (`id`, `email`, `name`, `subscribed_at`, `unsubscribed_at`, `status`, `ip_address`) VALUES
(1, 'kiss.janos@email.com', 'Kiss János', '2026-03-15 22:17:25', NULL, 'active', '192.168.1.100'),
(2, 'nagy.eva@email.com', 'Nagy Éva', '2026-03-15 22:17:25', NULL, 'active', '192.168.1.101');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_method` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `shipping_status` enum('pending','processing','shipped','delivered') DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `billing_address`, `shipping_method`, `payment_method`, `subtotal`, `shipping_cost`, `discount`, `tax`, `total_amount`, `coupon_code`, `notes`, `status`, `payment_status`, `shipping_status`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD-2024-0001', 'Kiss János', 'kiss.janos@email.com', '+36301234567', '1011 Budapest, Fő utca 10', NULL, NULL, NULL, 42990.00, 1990.00, 0.00, 0.00, 44980.00, NULL, NULL, 'delivered', 'paid', 'pending', NULL, NULL, '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 2, 'ORD-2024-0002', 'Nagy Éva', 'nagy.eva@email.com', '+36307654321', '6720 Szeged, Kossuth Lajos sugárút 25', NULL, NULL, NULL, 45990.00, 1990.00, 0.00, 0.00, 47980.00, NULL, NULL, 'shipped', 'paid', 'pending', NULL, NULL, '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, NULL, 'ORD-2024-0003', 'Teszt Elek', 'teszt.elek@email.com', '+36201234567', '7621 Pécs, Király utca 15', NULL, NULL, NULL, 61980.00, 1990.00, 0.00, 0.00, 63970.00, NULL, NULL, 'processing', 'pending', 'pending', NULL, NULL, '2026-03-15 22:17:25', '2026-03-15 22:17:25');

--
-- Eseményindítók `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_status_update` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO admin_logs (admin_id, action, details) 
        VALUES (NULL, 'order_status_change', 
                CONCAT('Rendelés #', NEW.id, ' státusz: ', OLD.status, ' -> ', NEW.status));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `product_sku`, `size`, `quantity`, `price`, `total`, `created_at`) VALUES
(1, 1, 1, 1, 'Nike Air Max 270 - Méret: 39', 'NK-AM270-39', '39', 1, 42990.00, 42990.00, '2026-03-15 22:17:25'),
(2, 2, 2, 10, 'Adidas Ultraboost 22 - Méret: 42', 'AD-UB22-42', '42', 1, 45990.00, 45990.00, '2026-03-15 22:17:25'),
(3, 3, 3, 18, 'New Balance 574 - Méret: 39', 'NB-574-39', '39', 1, 38990.00, 38990.00, '2026-03-15 22:17:25'),
(4, 3, 5, 32, 'Converse Chuck Taylor - Méret: 30', 'CN-CT-30', '30', 1, 18990.00, 18990.00, '2026-03-15 22:17:25');

--
-- Eseményindítók `order_items`
--
DELIMITER $$
CREATE TRIGGER `after_order_item_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    -- Alap termék készlet csökkentés
    UPDATE products 
    SET sold_count = sold_count + NEW.quantity
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `status` enum('published','draft') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `meta_title`, `meta_description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rólunk', 'rolunk', '<h1>Kickstar Sneaker Webshop</h1><p>A Kickstar 2020-ban alakult azzal a céllal, hogy a legmenőbb sneakereket elhozza Magyarországra.</p>', NULL, NULL, 'published', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 'GYIK', 'gyik', '<h1>Gyakori kérdések</h1><p>Válaszok a leggyakrabban feltett kérdésekre.</p>', NULL, NULL, 'published', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 'Szállítás', 'szallitas', '<h1>Szállítási információk</h1><p>A rendeléseket 1-3 munkanapon belül kiszállítjuk.</p>', NULL, NULL, 'published', '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_status` enum('in_stock','out_of_stock','pre_order','coming_soon') DEFAULT 'in_stock',
  `weight` decimal(8,2) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `new` tinyint(1) DEFAULT 0,
  `on_sale` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `compare_price`, `cost_price`, `sku`, `barcode`, `stock`, `stock_status`, `weight`, `featured`, `new`, `on_sale`, `image`, `gallery`, `attributes`, `meta_title`, `meta_description`, `meta_keywords`, `views`, `sold_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nike Air Max 270', 'nike-air-max-270', 'A Nike Air Max 270 a legújabb innováció a légpárnás technológiában. Kényelmes, stílusos és modern design.', 'Légpárnás férfi sneaker', 42990.00, NULL, NULL, 'NK-AM270', NULL, 0, 'in_stock', NULL, 1, 0, 0, 'nike-air-max-270.jpg', NULL, NULL, NULL, NULL, NULL, 0, 1, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 1, 'Adidas Ultraboost 22', 'adidas-ultraboost-22', 'Az Adidas Ultraboost 22 a tökéletes futócipő. Maximális kényelem és energiavisszaadás.', 'Futócipő férfiaknak', 45990.00, NULL, NULL, 'AD-UB22', NULL, 0, 'in_stock', NULL, 1, 0, 0, 'adidas-ultraboost-22.jpg', NULL, NULL, NULL, NULL, NULL, 0, 1, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 2, 'New Balance 574', 'new-balance-574', 'Klasszikus New Balance design modern megújulással. Kényelmes, stílusos, örök darab.', 'Női klasszikus sneaker', 38990.00, NULL, NULL, 'NB-574', NULL, 0, 'in_stock', NULL, 1, 0, 0, 'new-balance-574.jpg', NULL, NULL, NULL, NULL, NULL, 0, 1, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(4, 2, 'Puma Cali', 'puma-cali', 'A Puma Cali a 80-as évek hangulatát idézi modern formában. Tökéletes választás mindennapokra.', 'Retro női sneaker', 32990.00, NULL, NULL, 'PM-CALI', NULL, 0, 'in_stock', NULL, 0, 0, 0, 'puma-cali.jpg', NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(5, 3, 'Converse Chuck Taylor', 'converse-chuck-taylor', 'Az ikonikus Converse Chuck Taylor most gyerek méretben is. Időtlen design, kényelmes viselet.', 'Gyerek vászoncipő', 18990.00, NULL, NULL, 'CN-CT', NULL, 0, 'in_stock', NULL, 1, 0, 0, 'converse-chuck-taylor.jpg', NULL, NULL, NULL, NULL, NULL, 0, 1, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(6, 1, 'Vans Old Skool', 'vans-old-skool', 'A Vans Old Skool a klasszikus gördeszkás cipő. Tartós, stílusos, ikonikus.', 'Gördeszkás cipő', 27990.00, NULL, NULL, 'VN-OS', NULL, 0, 'in_stock', NULL, 0, 0, 0, 'vans-old-skool.jpg', NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(7, NULL, 'Air Jordan 1 Mid Diamond Shorts', 'Air-Jordan-1-Mid-Diamond-Shorts', 'Az Air Jordan 1 Mid Diamond Shorts a streetwear és a kosárlabda örökség tökéletes ötvözete. Fényűző bőr felsőrész, merész színblokkok és a feltűnő Diamond márkajelzés – ikonikus sziluett, ütős részletekkel.', 'Férficipő', 59990.00, NULL, NULL, 'AJ-1MID-DS', NULL, 0, 'in_stock', NULL, 0, 0, 0, 'AJ1MDS.jpg', NULL, NULL, NULL, NULL, NULL, 0, 0, 'active', '2026-03-15 22:27:26', '2026-03-15 23:00:55');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `product_tags`
--

CREATE TABLE `product_tags` (
  `product_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `product_tags`
--

INSERT INTO `product_tags` (`product_id`, `tag_id`) VALUES
(1, 3),
(1, 5),
(2, 2),
(2, 3),
(3, 3),
(3, 5),
(4, 1),
(5, 5),
(6, 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `sku`, `price`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, '39', 'NK-AM270-39', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 1, '40', 'NK-AM270-40', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 1, '41', 'NK-AM270-41', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(4, 1, '42', 'NK-AM270-42', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(5, 1, '43', 'NK-AM270-43', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(6, 1, '44', 'NK-AM270-44', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(7, 1, '45', 'NK-AM270-45', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(8, 1, '46', 'NK-AM270-46', 42990.00, 'nike-air-max-270.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(9, 2, '39', 'AD-UB22-39', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(10, 2, '40', 'AD-UB22-40', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(11, 2, '41', 'AD-UB22-41', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(12, 2, '42', 'AD-UB22-42', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(13, 2, '43', 'AD-UB22-43', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(14, 2, '44', 'AD-UB22-44', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(15, 2, '45', 'AD-UB22-45', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(16, 2, '46', 'AD-UB22-46', 45990.00, 'adidas-ultraboost-22.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(17, 3, '36', 'NB-574-36', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(18, 3, '37', 'NB-574-37', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(19, 3, '38', 'NB-574-38', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(20, 3, '39', 'NB-574-39', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(21, 3, '40', 'NB-574-40', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(22, 3, '41', 'NB-574-41', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(23, 3, '42', 'NB-574-42', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(24, 3, '43', 'NB-574-43', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(25, 3, '44', 'NB-574-44', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(26, 3, '45', 'NB-574-45', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(27, 3, '46', 'NB-574-46', 38990.00, 'new-balance-574.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(28, 4, '36', 'PM-CALI-36', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(29, 4, '37', 'PM-CALI-37', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(30, 4, '38', 'PM-CALI-38', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(31, 4, '39', 'PM-CALI-39', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(32, 4, '40', 'PM-CALI-40', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(33, 4, '41', 'PM-CALI-41', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(34, 4, '42', 'PM-CALI-42', 32990.00, 'puma-cali.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(35, 5, '28', 'CN-CT-28', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(36, 5, '29', 'CN-CT-29', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(37, 5, '30', 'CN-CT-30', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(38, 5, '31', 'CN-CT-31', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(39, 5, '32', 'CN-CT-32', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(40, 5, '33', 'CN-CT-33', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(41, 5, '34', 'CN-CT-34', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(42, 5, '35', 'CN-CT-35', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(43, 5, '36', 'CN-CT-36', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(44, 5, '37', 'CN-CT-37', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(45, 5, '38', 'CN-CT-38', 18990.00, 'converse-chuck-taylor.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(46, 6, '39', 'VN-OS-39', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(47, 6, '40', 'VN-OS-40', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(48, 6, '41', 'VN-OS-41', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(49, 6, '42', 'VN-OS-42', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(50, 6, '43', 'VN-OS-43', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(51, 6, '44', 'VN-OS-44', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(52, 6, '45', 'VN-OS-45', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(53, 6, '46', 'VN-OS-46', 27990.00, 'vans-old-skool.jpg', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(54, 7, '40', 'AJ-1MID-DS-40', 59990.00, 'AJ1MDS.jpg', '2026-03-15 22:39:33', '2026-03-15 22:58:58'),
(56, 7, '41', 'AJ-1MID-DS-41', 59990.00, 'AJ1MDS.jpg', '2026-03-15 22:58:52', '2026-03-15 22:58:52'),
(57, 7, '42', 'AJ-1MID-DS-42', 59990.00, 'AJ1MDS.jpg', '2026-03-15 22:59:19', '2026-03-15 22:59:19'),
(58, 7, '43', 'AJ-1MID-DS-43', 59990.00, 'AJ1MDS.jpg', '2026-03-15 22:59:30', '2026-03-15 22:59:30'),
(59, 7, '44', 'AJ-1MID-DS-44', 59990.00, 'AJ1MDS.jpg', '2026-03-15 22:59:40', '2026-03-15 22:59:40'),
(60, 7, '45', 'AJ-1MID-DS-45', 59990.00, 'AJ1MDS.jpg', '2026-03-15 22:59:57', '2026-03-15 22:59:57');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `pros`, `cons`, `is_verified`, `status`, `helpful_count`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 5, 'Tökéletes cipő!', 'Nagyon kényelmes, pontosan olyan, mint a képeken. Gyors szállítás.', NULL, NULL, 0, 'approved', 0, '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 2, 2, 2, 4, 'Jó cipő', 'Kényelmes, de kicsit szűkös a méretezés.', NULL, NULL, 0, 'approved', 0, '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Kickstar Sneaker', 'general', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 'site_email', 'info@kickstar.hu', 'general', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(3, 'site_phone', '+36 1 234 5678', 'general', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(4, 'site_address', '1055 Budapest, Kossuth tér 1-3.', 'general', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(5, 'currency', 'HUF', 'general', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(6, 'vat', '27', 'general', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(7, 'shipping_cost', '1990', 'shipping', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(8, 'free_shipping_threshold', '30000', 'shipping', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(9, 'facebook_url', 'https://facebook.com/kickstar', 'social', '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(10, 'instagram_url', 'https://instagram.com/kickstar', 'social', '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Akciós', 'akcios', '2026-03-15 22:17:25'),
(2, 'Új', 'uj', '2026-03-15 22:17:25'),
(3, 'Népszerű', 'nepszeru', '2026-03-15 22:17:25'),
(4, 'Limitált', 'limitalt', '2026-03-15 22:17:25'),
(5, 'Kényelmes', 'kenyelmes', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `newsletter` tinyint(1) DEFAULT 0,
  `role` enum('customer','subscriber') DEFAULT 'customer',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `phone`, `newsletter`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'kiss.janos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'János', 'Kiss', '+36301234567', 0, 'customer', 'active', NULL, '2026-03-15 22:17:25', '2026-03-15 22:17:25'),
(2, 'nagy.eva@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Éva', 'Nagy', '+36307654321', 0, 'customer', 'active', NULL, '2026-03-15 22:17:25', '2026-03-15 22:17:25');

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `view_orders_summary`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `view_orders_summary` (
`id` int(11)
,`user_id` int(11)
,`order_number` varchar(50)
,`customer_name` varchar(100)
,`customer_email` varchar(100)
,`customer_phone` varchar(20)
,`shipping_address` text
,`billing_address` text
,`shipping_method` varchar(50)
,`payment_method` varchar(50)
,`subtotal` decimal(10,2)
,`shipping_cost` decimal(10,2)
,`discount` decimal(10,2)
,`tax` decimal(10,2)
,`total_amount` decimal(10,2)
,`coupon_code` varchar(50)
,`notes` text
,`status` enum('pending','processing','shipped','delivered','cancelled','refunded')
,`payment_status` enum('pending','paid','failed','refunded')
,`shipping_status` enum('pending','processing','shipped','delivered')
,`ip_address` varchar(45)
,`user_agent` text
,`created_at` timestamp
,`updated_at` timestamp
,`item_count` bigint(21)
,`total_items` decimal(32,0)
);

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `view_products_with_category`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `view_products_with_category` (
`id` int(11)
,`category_id` int(11)
,`name` varchar(255)
,`slug` varchar(255)
,`description` text
,`short_description` varchar(500)
,`price` decimal(10,2)
,`compare_price` decimal(10,2)
,`cost_price` decimal(10,2)
,`sku` varchar(50)
,`barcode` varchar(50)
,`stock` int(11)
,`stock_status` enum('in_stock','out_of_stock','pre_order','coming_soon')
,`weight` decimal(8,2)
,`featured` tinyint(1)
,`new` tinyint(1)
,`on_sale` tinyint(1)
,`image` varchar(255)
,`gallery` text
,`attributes` longtext
,`meta_title` varchar(255)
,`meta_description` varchar(500)
,`meta_keywords` varchar(255)
,`views` int(11)
,`sold_count` int(11)
,`status` enum('active','inactive','draft')
,`created_at` timestamp
,`updated_at` timestamp
,`category_name` varchar(100)
,`category_slug` varchar(100)
);

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `view_products_with_variants`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `view_products_with_variants` (
`product_id` int(11)
,`product_name` varchar(255)
,`base_price` decimal(10,2)
,`main_image` varchar(255)
,`variant_id` int(11)
,`size` varchar(50)
,`actual_price` decimal(10,2)
,`variant_image` varchar(255)
,`sku` varchar(50)
);

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `view_product_stats`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `view_product_stats` (
`id` int(11)
,`name` varchar(255)
,`variant_count` bigint(21)
,`order_count` bigint(21)
,`total_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Nézet szerkezete `view_orders_summary`
--
DROP TABLE IF EXISTS `view_orders_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_orders_summary`  AS SELECT `o`.`id` AS `id`, `o`.`user_id` AS `user_id`, `o`.`order_number` AS `order_number`, `o`.`customer_name` AS `customer_name`, `o`.`customer_email` AS `customer_email`, `o`.`customer_phone` AS `customer_phone`, `o`.`shipping_address` AS `shipping_address`, `o`.`billing_address` AS `billing_address`, `o`.`shipping_method` AS `shipping_method`, `o`.`payment_method` AS `payment_method`, `o`.`subtotal` AS `subtotal`, `o`.`shipping_cost` AS `shipping_cost`, `o`.`discount` AS `discount`, `o`.`tax` AS `tax`, `o`.`total_amount` AS `total_amount`, `o`.`coupon_code` AS `coupon_code`, `o`.`notes` AS `notes`, `o`.`status` AS `status`, `o`.`payment_status` AS `payment_status`, `o`.`shipping_status` AS `shipping_status`, `o`.`ip_address` AS `ip_address`, `o`.`user_agent` AS `user_agent`, `o`.`created_at` AS `created_at`, `o`.`updated_at` AS `updated_at`, count(`oi`.`id`) AS `item_count`, sum(`oi`.`quantity`) AS `total_items` FROM (`orders` `o` left join `order_items` `oi` on(`o`.`id` = `oi`.`order_id`)) GROUP BY `o`.`id` ;

-- --------------------------------------------------------

--
-- Nézet szerkezete `view_products_with_category`
--
DROP TABLE IF EXISTS `view_products_with_category`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_products_with_category`  AS SELECT `p`.`id` AS `id`, `p`.`category_id` AS `category_id`, `p`.`name` AS `name`, `p`.`slug` AS `slug`, `p`.`description` AS `description`, `p`.`short_description` AS `short_description`, `p`.`price` AS `price`, `p`.`compare_price` AS `compare_price`, `p`.`cost_price` AS `cost_price`, `p`.`sku` AS `sku`, `p`.`barcode` AS `barcode`, `p`.`stock` AS `stock`, `p`.`stock_status` AS `stock_status`, `p`.`weight` AS `weight`, `p`.`featured` AS `featured`, `p`.`new` AS `new`, `p`.`on_sale` AS `on_sale`, `p`.`image` AS `image`, `p`.`gallery` AS `gallery`, `p`.`attributes` AS `attributes`, `p`.`meta_title` AS `meta_title`, `p`.`meta_description` AS `meta_description`, `p`.`meta_keywords` AS `meta_keywords`, `p`.`views` AS `views`, `p`.`sold_count` AS `sold_count`, `p`.`status` AS `status`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `c`.`name` AS `category_name`, `c`.`slug` AS `category_slug` FROM (`products` `p` left join `categories` `c` on(`p`.`category_id` = `c`.`id`)) ;

-- --------------------------------------------------------

--
-- Nézet szerkezete `view_products_with_variants`
--
DROP TABLE IF EXISTS `view_products_with_variants`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_products_with_variants`  AS SELECT `p`.`id` AS `product_id`, `p`.`name` AS `product_name`, `p`.`price` AS `base_price`, `p`.`image` AS `main_image`, `v`.`id` AS `variant_id`, `v`.`size` AS `size`, coalesce(`v`.`price`,`p`.`price`) AS `actual_price`, `v`.`image` AS `variant_image`, `v`.`sku` AS `sku` FROM (`products` `p` left join `product_variants` `v` on(`p`.`id` = `v`.`product_id`)) ORDER BY `p`.`id` ASC, CASE WHEN `v`.`size` regexp '^[0-9]+$' THEN cast(`v`.`size` as unsigned) ELSE 999 END ASC ;

-- --------------------------------------------------------

--
-- Nézet szerkezete `view_product_stats`
--
DROP TABLE IF EXISTS `view_product_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_product_stats`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, count(distinct `v`.`id`) AS `variant_count`, count(distinct `oi`.`id`) AS `order_count`, sum(`oi`.`quantity`) AS `total_sold` FROM ((`products` `p` left join `product_variants` `v` on(`p`.`id` = `v`.`product_id`)) left join `order_items` `oi` on(`p`.`id` = `oi`.`product_id`)) GROUP BY `p`.`id`, `p`.`name` ;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- A tábla indexei `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admins_username` (`username`),
  ADD KEY `idx_admins_email` (`email`),
  ADD KEY `idx_admins_status` (`status`);

--
-- A tábla indexei `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_logs_admin_id` (`admin_id`),
  ADD KEY `idx_admin_logs_created_at` (`created_at`),
  ADD KEY `idx_admin_logs_action` (`action`);

--
-- A tábla indexei `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_admin_settings_key` (`setting_key`);

--
-- A tábla indexei `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_slug` (`slug`),
  ADD KEY `idx_categories_parent` (`parent_id`),
  ADD KEY `idx_categories_status` (`status`);

--
-- A tábla indexei `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_status` (`status`);

--
-- A tábla indexei `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_coupons_code` (`code`),
  ADD KEY `idx_coupons_status` (`status`);

--
-- A tábla indexei `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_newsletter_email` (`email`),
  ADD KEY `idx_newsletter_status` (`status`);

--
-- A tábla indexei `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_number` (`order_number`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_email` (`customer_email`),
  ADD KEY `idx_orders_date` (`created_at`);

--
-- A tábla indexei `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- A tábla indexei `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_pages_slug` (`slug`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_slug` (`slug`),
  ADD KEY `idx_products_sku` (`sku`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_featured` (`featured`),
  ADD KEY `idx_products_price` (`price`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_products_search` (`name`,`description`);

--
-- A tábla indexei `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product` (`product_id`);

--
-- A tábla indexei `product_tags`
--
ALTER TABLE `product_tags`
  ADD PRIMARY KEY (`product_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- A tábla indexei `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_product_variants_product` (`product_id`),
  ADD KEY `idx_product_variants_size` (`size`),
  ADD KEY `idx_product_variants_sku` (`sku`);

--
-- A tábla indexei `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_reviews_product` (`product_id`),
  ADD KEY `idx_reviews_status` (`status`);

--
-- A tábla indexei `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_settings_key` (`setting_key`),
  ADD KEY `idx_settings_group` (`setting_group`);

--
-- A tábla indexei `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_status` (`status`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a táblához `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT a táblához `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD CONSTRAINT `admin_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `product_tags`
--
ALTER TABLE `product_tags`
  ADD CONSTRAINT `product_tags_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
