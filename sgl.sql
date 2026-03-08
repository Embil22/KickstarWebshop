-- ==================================================
-- Kickstar Sneaker Webshop - Teljes adatbázis
-- Verzió: 1.0
-- Dátum: 2024
-- ==================================================

-- Adatbázis létrehozása
DROP DATABASE IF EXISTS kickstar_db;
CREATE DATABASE IF NOT EXISTS kickstar_db;
USE kickstar_db;

-- ==================================================
-- TÁBLÁK LÉTREHOZÁSA
-- ==================================================

-- --------------------------------------------------
-- 1. Adminisztrátorok tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'editor', 'viewer') DEFAULT 'admin',
    status ENUM('active', 'inactive', 'locked') DEFAULT 'active',
    last_login DATETIME,
    last_ip VARCHAR(45),
    failed_attempts INT DEFAULT 0,
    locked_until DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admins_username (username),
    INDEX idx_admins_email (email),
    INDEX idx_admins_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 2. Admin naplózás tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_admin_logs_admin_id (admin_id),
    INDEX idx_admin_logs_created_at (created_at),
    INDEX idx_admin_logs_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 3. Admin beállítások tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_admin_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 4. Kategóriák tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT DEFAULT NULL,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_categories_slug (slug),
    INDEX idx_categories_parent (parent_id),
    INDEX idx_categories_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 5. Termékek tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2),
    cost_price DECIMAL(10,2),
    sku VARCHAR(50) UNIQUE,
    barcode VARCHAR(50),
    stock INT DEFAULT 0,
    stock_status ENUM('in_stock', 'out_of_stock', 'pre_order', 'coming_soon') DEFAULT 'in_stock',
    weight DECIMAL(8,2),
    featured BOOLEAN DEFAULT FALSE,
    new BOOLEAN DEFAULT FALSE,
    on_sale BOOLEAN DEFAULT FALSE,
    image VARCHAR(255),
    gallery TEXT,
    attributes JSON,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    meta_keywords VARCHAR(255),
    views INT DEFAULT 0,
    sold_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_products_category (category_id),
    INDEX idx_products_slug (slug),
    INDEX idx_products_sku (sku),
    INDEX idx_products_status (status),
    INDEX idx_products_featured (featured),
    INDEX idx_products_price (price),
    FULLTEXT idx_products_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 6. Termék képek tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_images_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 7. Termék változatok tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(50),
    color VARCHAR(50),
    color_code VARCHAR(20),
    sku VARCHAR(50) UNIQUE,
    price DECIMAL(10,2),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_variants_product (product_id),
    INDEX idx_product_variants_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 8. Címkék tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 9. Termék-címke kapcsolótábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS product_tags (
    product_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 10. Felhasználók tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    newsletter BOOLEAN DEFAULT FALSE,
    role ENUM('customer', 'subscriber') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 11. Címek tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('shipping', 'billing') DEFAULT 'shipping',
    is_default BOOLEAN DEFAULT FALSE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    company VARCHAR(100),
    country VARCHAR(50) DEFAULT 'Magyarország',
    zip_code VARCHAR(10) NOT NULL,
    city VARCHAR(50) NOT NULL,
    street VARCHAR(255) NOT NULL,
    house_number VARCHAR(20),
    floor_door VARCHAR(50),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_addresses_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 12. Rendelések tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    shipping_method VARCHAR(50),
    payment_method VARCHAR(50),
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50),
    notes TEXT,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_status ENUM('pending', 'processing', 'shipped', 'delivered') DEFAULT 'pending',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_number (order_number),
    INDEX idx_orders_status (status),
    INDEX idx_orders_email (customer_email),
    INDEX idx_orders_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 13. Rendelés tételek tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    variant_id INT,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(50),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 14. Kuponok tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('percent', 'fixed') DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2),
    max_discount DECIMAL(10,2),
    usage_limit INT,
    used_count INT DEFAULT 0,
    per_user_limit INT DEFAULT 1,
    start_date DATETIME,
    end_date DATETIME,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_coupons_code (code),
    INDEX idx_coupons_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 15. Kupon használatok tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    order_id INT NOT NULL,
    user_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 16. Vélemények tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT,
    order_id INT,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(255),
    comment TEXT,
    pros TEXT,
    cons TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_reviews_product (product_id),
    INDEX idx_reviews_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 17. Hírlevél tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    ip_address VARCHAR(45),
    INDEX idx_newsletter_email (email),
    INDEX idx_newsletter_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 18. Kapcsolat üzenetek tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contact_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 19. Oldalak tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    status ENUM('published', 'draft') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------
-- 20. Beállítások tábla
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (setting_key),
    INDEX idx_settings_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ==================================================
-- ALAPADATOK BESZÚRÁSA
-- ==================================================

-- --------------------------------------------------
-- Admin beállítások
-- --------------------------------------------------
INSERT INTO admin_settings (setting_key, setting_value, setting_type, description) VALUES
('max_login_attempts', '5', 'number', 'Maximális bejelentkezési kísérletek száma'),
('lockout_duration', '15', 'number', 'Fiú zárolás időtartama percekben'),
('session_timeout', '30', 'number', 'Munkamenet időtúllépés percekben'),
('two_factor_auth', '0', 'boolean', 'Kétfaktoros hitelesítés bekapcsolása'),
('maintenance_mode', '0', 'boolean', 'Karbantartási mód bekapcsolása');

-- --------------------------------------------------
-- Admin felhasználók (jelszó: admin123)
-- --------------------------------------------------
INSERT INTO admins (username, full_name, email, password, role) VALUES 
('admin', 'Rendszergazda', 'admin@kickstar.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- --------------------------------------------------
-- Kategóriák
-- --------------------------------------------------
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Férfi cipők', 'ferfi-cipok', 'Férfi sneakerek és sportcipők', 1),
('Női cipők', 'noi-cipok', 'Női sneakerek és divatcipők', 2),
('Gyerek cipők', 'gyerek-cipok', 'Gyerek sneakerek', 3),
('Sportcipők', 'sportcipok', 'Professzionális sportcipők', 4),
('Casual cipők', 'casual-cipok', 'Hétköznapi viseletre', 5);

-- --------------------------------------------------
-- Termékek
-- --------------------------------------------------
INSERT INTO products (category_id, name, slug, description, short_description, price, sku, stock, featured, image) VALUES
(1, 'Nike Air Max 270', 'nike-air-max-270', 'A Nike Air Max 270 a legújabb innováció a légpárnás technológiában. Kényelmes, stílusos és modern design.', 'Légpárnás férfi sneaker', 42990, 'NK-AM270-001', 25, TRUE, 'nike-air-max-270.jpg'),
(1, 'Adidas Ultraboost 22', 'adidas-ultraboost-22', 'Az Adidas Ultraboost 22 a tökéletes futócipő. Maximális kényelem és energiavisszaadás.', 'Futócipő férfiaknak', 45990, 'AD-UB22-001', 18, TRUE, 'adidas-ultraboost-22.jpg'),
(2, 'New Balance 574', 'new-balance-574', 'Klasszikus New Balance design modern megújulással. Kényelmes, stílusos, örök darab.', 'Női klasszikus sneaker', 38990, 'NB-574-001', 32, TRUE, 'new-balance-574.jpg'),
(2, 'Puma Cali', 'puma-cali', 'A Puma Cali a 80-as évek hangulatát idézi modern formában. Tökéletes választás mindennapokra.', 'Retro női sneaker', 32990, 'PM-CALI-001', 15, FALSE, 'puma-cali.jpg'),
(3, 'Converse Chuck Taylor', 'converse-chuck-taylor', 'Az ikonikus Converse Chuck Taylor most gyerek méretben is. Időtlen design, kényelmes viselet.', 'Gyerek vászoncipő', 18990, 'CN-CT-001', 22, TRUE, 'converse-chuck-taylor.jpg'),
(1, 'Vans Old Skool', 'vans-old-skool', 'A Vans Old Skool a klasszikus gördeszkás cipő. Tartós, stílusos, ikonikus.', 'Gördeszkás cipő', 27990, 'VN-OS-001', 12, FALSE, 'vans-old-skool.jpg');

-- --------------------------------------------------
-- Termék képek
-- --------------------------------------------------
INSERT INTO product_images (product_id, image, is_primary, sort_order) VALUES
(1, 'nike-air-max-270-1.jpg', TRUE, 1),
(1, 'nike-air-max-270-2.jpg', FALSE, 2),
(1, 'nike-air-max-270-3.jpg', FALSE, 3),
(2, 'adidas-ultraboost-22-1.jpg', TRUE, 1),
(2, 'adidas-ultraboost-22-2.jpg', FALSE, 2);

-- --------------------------------------------------
-- Termék változatok (méret, szín)
-- --------------------------------------------------
INSERT INTO product_variants (product_id, size, color, color_code, sku, price, stock) VALUES
(1, '42', 'Fekete', '#000000', 'NK-AM270-001-42-BLK', 42990, 5),
(1, '43', 'Fekete', '#000000', 'NK-AM270-001-43-BLK', 42990, 8),
(1, '44', 'Fekete', '#000000', 'NK-AM270-001-44-BLK', 42990, 6),
(1, '42', 'Fehér', '#FFFFFF', 'NK-AM270-001-42-WHT', 42990, 4),
(2, '42', 'Kék', '#0000FF', 'AD-UB22-001-42-BLU', 45990, 5),
(2, '43', 'Kék', '#0000FF', 'AD-UB22-001-43-BLU', 45990, 7);

-- --------------------------------------------------
-- Címkék
-- --------------------------------------------------
INSERT INTO tags (name, slug) VALUES
('Akciós', 'akcios'),
('Új', 'uj'),
('Népszerű', 'nepszeru'),
('Limitált', 'limitalt'),
('Kényelmes', 'kenyelmes');

-- --------------------------------------------------
-- Termék-címke kapcsolatok
-- --------------------------------------------------
INSERT INTO product_tags (product_id, tag_id) VALUES
(1, 3),
(1, 5),
(2, 2),
(2, 3),
(3, 3),
(3, 5);

-- --------------------------------------------------
-- Felhasználók
-- --------------------------------------------------
INSERT INTO users (email, password, first_name, last_name, phone) VALUES
('kiss.janos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'János', 'Kiss', '+36301234567'),
('nagy.eva@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Éva', 'Nagy', '+36307654321');

-- --------------------------------------------------
-- Címek
-- --------------------------------------------------
INSERT INTO addresses (user_id, type, is_default, first_name, last_name, zip_code, city, street, house_number, phone) VALUES
(1, 'shipping', TRUE, 'János', 'Kiss', '1011', 'Budapest', 'Fő utca', '10', '+36301234567'),
(1, 'billing', TRUE, 'János', 'Kiss', '1011', 'Budapest', 'Fő utca', '10', '+36301234567'),
(2, 'shipping', TRUE, 'Éva', 'Nagy', '6720', 'Szeged', 'Kossuth Lajos sugárút', '25', '+36307654321');

-- --------------------------------------------------
-- Kuponok
-- --------------------------------------------------
INSERT INTO coupons (code, type, value, min_order_amount, usage_limit, start_date, end_date) VALUES
('KICK10', 'percent', 10, 10000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('KICK20', 'percent', 20, 20000, 50, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('FREE1000', 'fixed', 1000, 5000, 200, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY));

-- --------------------------------------------------
-- Rendelések
-- --------------------------------------------------
INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, shipping_address, subtotal, shipping_cost, total_amount, status, payment_status) VALUES
('ORD-2024-0001', 1, 'Kiss János', 'kiss.janos@email.com', '+36301234567', '1011 Budapest, Fő utca 10', 42990, 1990, 44980, 'delivered', 'paid'),
('ORD-2024-0002', 2, 'Nagy Éva', 'nagy.eva@email.com', '+36307654321', '6720 Szeged, Kossuth Lajos sugárút 25', 45990, 1990, 47980, 'shipped', 'paid'),
('ORD-2024-0003', NULL, 'Teszt Elek', 'teszt.elek@email.com', '+36201234567', '7621 Pécs, Király utca 15', 61980, 1990, 63970, 'processing', 'pending');

-- --------------------------------------------------
-- Rendelés tételek
-- --------------------------------------------------
INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, price, total) VALUES
(1, 1, 'Nike Air Max 270', 'NK-AM270-001', 1, 42990, 42990),
(2, 2, 'Adidas Ultraboost 22', 'AD-UB22-001', 1, 45990, 45990),
(3, 3, 'New Balance 574', 'NB-574-001', 1, 38990, 38990),
(3, 5, 'Converse Chuck Taylor', 'CN-CT-001', 1, 18990, 18990);

-- --------------------------------------------------
-- Vélemények
-- --------------------------------------------------
INSERT INTO reviews (product_id, user_id, order_id, rating, title, comment, status) VALUES
(1, 1, 1, 5, 'Tökéletes cipő!', 'Nagyon kényelmes, pontosan olyan, mint a képeken. Gyors szállítás.', 'approved'),
(2, 2, 2, 4, 'Jó cipő', 'Kényelmes, de kicsit szűkös a méretezés.', 'approved');

-- --------------------------------------------------
-- Hírlevél feliratkozók
-- --------------------------------------------------
INSERT INTO newsletter (email, name, ip_address) VALUES
('kiss.janos@email.com', 'Kiss János', '192.168.1.100'),
('nagy.eva@email.com', 'Nagy Éva', '192.168.1.101');

-- --------------------------------------------------
-- Kapcsolat üzenetek
-- --------------------------------------------------
INSERT INTO contact_messages (name, email, phone, subject, message, ip_address) VALUES
('Kiss János', 'kiss.janos@email.com', '+36301234567', 'Méret információ', 'Szeretném tudni, hogy a Nike Air Max 270 cipőből van-e 45-ös méret?', '192.168.1.100');

-- --------------------------------------------------
-- Oldalak
-- --------------------------------------------------
INSERT INTO pages (title, slug, content, status) VALUES
('Rólunk', 'rolunk', '<h1>Kickstar Sneaker Webshop</h1><p>A Kickstar 2020-ban alakult azzal a céllal, hogy a legmenőbb sneakereket elhozza Magyarországra.</p>', 'published'),
('GYIK', 'gyik', '<h1>Gyakori kérdések</h1><p>Válaszok a leggyakrabban feltett kérdésekre.</p>', 'published'),
('Szállítás', 'szallitas', '<h1>Szállítási információk</h1><p>A rendeléseket 1-3 munkanapon belül kiszállítjuk.</p>', 'published');

-- --------------------------------------------------
-- Beállítások
-- --------------------------------------------------
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'Kickstar Sneaker', 'general'),
('site_email', 'info@kickstar.hu', 'general'),
('site_phone', '+36 1 234 5678', 'general'),
('site_address', '1055 Budapest, Kossuth tér 1-3.', 'general'),
('currency', 'HUF', 'general'),
('vat', '27', 'general'),
('shipping_cost', '1990', 'shipping'),
('free_shipping_threshold', '30000', 'shipping'),
('facebook_url', 'https://facebook.com/kickstar', 'social'),
('instagram_url', 'https://instagram.com/kickstar', 'social');

-- ==================================================
-- INDEXEK ÉS KORLÁTOZÁSOK
-- ==================================================

-- Külső kulcsok ellenőrzése
SET FOREIGN_KEY_CHECKS = 1;

-- ==================================================
-- NÉZETEK (VIEWS)
-- ==================================================

-- Termékek nézet - termék adatok kategória névvel
CREATE VIEW view_products_with_category AS
SELECT p.*, c.name as category_name, c.slug as category_slug
FROM products p
LEFT JOIN categories c ON p.category_id = c.id;

-- Rendelések nézet - rendelés adatok összesítéssel
CREATE VIEW view_orders_summary AS
SELECT o.*, 
       COUNT(oi.id) as item_count,
       SUM(oi.quantity) as total_items
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- Népszerű termékek nézet
CREATE VIEW view_popular_products AS
SELECT p.*, 
       COUNT(oi.id) as order_count,
       SUM(oi.quantity) as total_sold
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id
ORDER BY total_sold DESC;

-- ==================================================
-- TRIGGEREK
-- ==================================================

-- Termék rendelés után készlet csökkentés
DELIMITER $$
CREATE TRIGGER after_order_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products 
    SET stock = stock - NEW.quantity,
        sold_count = sold_count + NEW.quantity
    WHERE id = NEW.product_id;
END$$
DELIMITER ;

-- Rendelés státusz változás naplózás
DELIMITER $$
CREATE TRIGGER after_order_status_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO admin_logs (admin_id, action, details) 
        VALUES (NULL, 'order_status_change', 
                CONCAT('Rendelés #', NEW.id, ' státusz: ', OLD.status, ' -> ', NEW.status));
    END IF;
END$$
DELIMITER ;

-- ==================================================
-- PROCEDÚRÁK
-- ==================================================

-- Rendelés összeg számítás
DELIMITER $$
CREATE PROCEDURE CalculateOrderTotal(IN orderId INT)
BEGIN
    DECLARE total DECIMAL(10,2);
    
    SELECT SUM(quantity * price) INTO total
    FROM order_items
    WHERE order_id = orderId;
    
    UPDATE orders 
    SET subtotal = total,
        total_amount = total + shipping_cost - discount
    WHERE id = orderId;
    
    SELECT total as order_total;
END$$
DELIMITER ;

-- Havi statisztika
DELIMITER $$
CREATE PROCEDURE GetMonthlyStats(IN year INT, IN month INT)
BEGIN
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        COUNT(DISTINCT user_id) as unique_customers
    FROM orders
    WHERE YEAR(created_at) = year 
      AND MONTH(created_at) = month;
END$$
DELIMITER ;

-- ==================================================
-- ESEMÉNYEK (EVENTS)
-- ==================================================

-- Régi rendelések archiválása (éjszakai futás)
DELIMITER $$
CREATE EVENT IF NOT EXISTS archive_old_orders
ON SCHEDULE EVERY 1 DAY
STARTS '2024-01-01 02:00:00'
DO
BEGIN
    -- Itt lehetne archiválni a 3 évnél régebbi rendeléseket
    UPDATE orders 
    SET status = 'archived' 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 YEAR);
END$$
DELIMITER ;

-- ==================================================
-- FELHASZNÁLÓK ÉS JOGOSULTSÁGOK
-- ==================================================

-- Alkalmazás felhasználó létrehozása (opcionális)
CREATE USER IF NOT EXISTS 'kickstar_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON kickstar_db.* TO 'kickstar_user'@'localhost';
FLUSH PRIVILEGES;

-- ==================================================
-- MEGJEGYZÉSEK
-- ==================================================

/*
====================================================
Telepítési útmutató:
1. Futtasd ezt az SQL fájlt a MySQL szerveren
2. Az admin belépéshez: admin / admin123
3. Módosítsd a jelszót éles környezetben!
====================================================

Kapcsolatok:
- admins -> admin_logs (admin_id)
- categories -> categories (parent_id)
- products -> categories (category_id)
- products -> product_images (product_id)
- products -> product_variants (product_id)
- products -> product_tags (product_id)
- tags -> product_tags (tag_id)
- users -> addresses (user_id)
- users -> orders (user_id)
- orders -> order_items (order_id)
- orders -> coupon_usage (order_id)
- products -> order_items (product_id)
- coupons -> coupon_usage (coupon_id)

Táblák száma: 20
Nézetek száma: 3
Trigger: 2
Procedure: 2
Event: 1
====================================================
*/