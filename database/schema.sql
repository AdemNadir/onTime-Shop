-- Ontime E-commerce Database Schema
-- Import this in phpMyAdmin (XAMPP) to create the "ontime_shop" database

CREATE DATABASE IF NOT EXISTS ontime_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ontime_shop;

-- Admin users (dashboard login)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username = admin / password = password  (CHANGE THIS after first login, see README)
INSERT INTO admin_users (username, password_hash) VALUES
('admin', '$2y$10$DYTCcl25.i8g/2i55Lsa7uB9piWb2V2g0jumYDe2JlDAOUK9kcY0a');

-- Product categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO categories (name) VALUES ('Homme'), ('Femme'), ('Unisexe'), ('Edition Limitee');

-- Products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

INSERT INTO products (name, slug, description, price, cost_price, stock, image, category_id) VALUES
('Ontime Classic Noir', 'ontime-classic-noir', 'Montre elegante a cadran noir, bracelet acier inoxydable, mouvement quartz japonais. Etanche 3ATM.', 8900.00, 4200.00, 25, 'sample-watch-1.jpg', 1),
('Ontime Rose Gold', 'ontime-rose-gold', 'Montre pour femme, boitier rose gold, bracelet cuir veritable, cadran nacre.', 7500.00, 3600.00, 18, 'sample-watch-2.jpg', 2),
('Ontime Sport Chrono', 'ontime-sport-chrono', 'Chronographe sportif, bracelet silicone, resistant a l eau, ideal usage quotidien.', 10500.00, 5100.00, 12, 'sample-watch-3.jpg', 3);

-- Orders (placed from the storefront checkout)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    wilaya VARCHAR(50) NOT NULL,
    commune VARCHAR(100) DEFAULT NULL,
    address TEXT,
    delivery_type ENUM('domicile','stopdesk') NOT NULL DEFAULT 'domicile',
    shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    tracking_number VARCHAR(100) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order line items (snapshot at order time, so editing a product later never changes past profit history)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Store-wide settings (API keys, store name, etc.)
CREATE TABLE settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO settings (setting_key, setting_value) VALUES
('store_name', 'Ontime'),
('zrexpress_token', ''),
('zrexpress_key', ''),
('currency', 'DA');
