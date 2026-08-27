-- =============================================
-- DATABASE: car_dealer
-- CHERY MOBIL OFFICIAL
-- =============================================

CREATE DATABASE IF NOT EXISTS car_dealer;
USE car_dealer;

-- =============================================
-- TABLE: users
-- =============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- Insert admin account
INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES 
('rohman_developer', 'admin@cherymobil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rohman Developer', '081288052242', 'admin', 'active');

-- =============================================
-- TABLE: products
-- =============================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    type VARCHAR(50),
    year INT,
    price DECIMAL(15,2) NOT NULL,
    price_old DECIMAL(15,2),
    image VARCHAR(255),
    images TEXT,
    description TEXT,
    specs TEXT,
    features TEXT,
    color VARCHAR(50),
    transmission VARCHAR(50),
    fuel_type VARCHAR(50),
    engine_cc INT,
    stock INT DEFAULT 0,
    status ENUM('available', 'sold', 'coming', 'preorder') DEFAULT 'available',
    is_featured BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    is_hot BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_brand (brand),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    FULLTEXT idx_search (name, brand, model, description)
);

-- Insert sample products
INSERT INTO products (name, brand, model, type, year, price, price_old, description, stock, status, is_featured, is_new) VALUES 
('TIGGO CROSS CSH', 'CHERY', 'TIGGO CROSS', 'SUV', 2024, 329800000, 359800000, 'Mobil SUV terbaru dari Chery dengan desain modern dan fitur canggih. Dilengkapi dengan mesin 1.5L Turbo yang bertenaga namun efisien.', 5, 'available', TRUE, TRUE),
('J6 T', 'CHERY', 'J6', 'Sedan', 2024, 580500000, 620000000, 'Sedan premium dengan performa tinggi dan kenyamanan maksimal. Interior mewah dengan teknologi terbaru.', 3, 'available', TRUE, TRUE),
('OMODA 5', 'OMODA', '5', 'SUV', 2024, 398000000, 428000000, 'SUV kompak dengan desain futuristik dan teknologi terkini. Cocok untuk gaya hidup modern.', 7, 'available', TRUE, FALSE),
('TIGGO 8 PRO', 'CHERY', 'TIGGO 8', 'SUV', 2023, 478000000, 498000000, 'SUV 7-seater premium dengan kabin luas dan fitur keselamatan lengkap.', 4, 'available', FALSE, FALSE),
('OMODA 7', 'OMODA', '7', 'SUV', 2024, 458000000, NULL, 'SUV premium dengan performa tinggi dan desain elegan.', 2, 'available', FALSE, TRUE),
('TIGGO CROSS CSH PREMIUM', 'CHERY', 'TIGGO CROSS', 'SUV', 2024, 389000000, 419000000, 'Versi premium dari TIGGO CROSS dengan fitur tambahan dan interior lebih mewah.', 3, 'available', FALSE, FALSE);

-- =============================================
-- TABLE: orders
-- =============================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(15,2),
    discount DECIMAL(15,2) DEFAULT 0,
    final_amount DECIMAL(15,2),
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled', 'refund') DEFAULT 'pending',
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    customer_address TEXT,
    notes TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid', 'paid', 'partial') DEFAULT 'unpaid',
    shipping_method VARCHAR(50),
    shipping_cost DECIMAL(15,2) DEFAULT 0,
    tracking_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_user (user_id)
);

-- =============================================
-- TABLE: order_items
-- =============================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(15,2),
    subtotal DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
);

-- =============================================
-- TABLE: cart
-- =============================================
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(100),
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id)
);

-- =============================================
-- TABLE: offers
-- =============================================
CREATE TABLE offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    message TEXT,
    offer_amount DECIMAL(15,2),
    status ENUM('new', 'seen', 'responded', 'accepted', 'rejected') DEFAULT 'new',
    admin_response TEXT,
    responded_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_status (status)
);

-- =============================================
-- TABLE: ads
-- =============================================
CREATE TABLE ads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200),
    image VARCHAR(255),
    link VARCHAR(255),
    position ENUM('home', 'sidebar', 'footer', 'popup', 'banner') DEFAULT 'home',
    is_active BOOLEAN DEFAULT TRUE,
    clicks INT DEFAULT 0,
    impressions INT DEFAULT 0,
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_position (position)
);

-- =============================================
-- TABLE: activity_logs
-- =============================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(100),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- =============================================
-- TABLE: settings
-- =============================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group)
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES 
('site_name', 'Chery Mobil Official', 'general'),
('site_logo', '', 'general'),
('site_favicon', '', 'general'),
('contact_phone', '6282117985579', 'contact'),
('contact_email', 'info@cherymobil.com', 'contact'),
('address', 'Jl. Raya Mobil No. 123, Jakarta Selatan, Indonesia', 'contact'),
('social_instagram', 'https://instagram.com/cherymobil', 'social'),
('social_facebook', 'https://facebook.com/cherymobil', 'social'),
('social_youtube', 'https://youtube.com/cherymobil', 'social'),
('social_tiktok', 'https://tiktok.com/@cherymobil', 'social'),
('whatsapp_number', '6282117985579', 'contact'),
('maintenance_mode', '0', 'general'),
('theme_color', '#D61C1C', 'design'),
('hero_title', 'CHERY OMODA', 'design'),
('hero_subtitle', 'Mobil Impian, Harga Terjangkau', 'design'),
('hero_description', 'Dapatkan mobil Chery favorit Anda dengan promo spesial dan layanan terbaik dari dealer resmi kami.', 'design'),
('meta_title', 'Chery Mobil Official - Dealer Mobil Terpercaya', 'seo'),
('meta_description', 'Dealer resmi Chery mobil di Indonesia. Temukan mobil impian Anda dengan promo menarik.', 'seo'),
('meta_keywords', 'Chery, OMODA, mobil baru, dealer mobil, Chery Indonesia', 'seo');

-- =============================================
-- TABLE: transactions
-- =============================================
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    transaction_number VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(15,2),
    fee DECIMAL(15,2) DEFAULT 0,
    net_amount DECIMAL(15,2),
    type ENUM('payment', 'refund', 'deposit') DEFAULT 'payment',
    method VARCHAR(50),
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    reference VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_transaction_number (transaction_number),
    INDEX idx_status (status)
);

-- =============================================
-- TABLE: wishlist
-- =============================================
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- =============================================
-- TABLE: testimonials
-- =============================================
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255),
    rating INT DEFAULT 5,
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
