-- JMF 509 Warehouse - Database schema
-- Run this to create the required tables (MySQL)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    image LONGBLOB,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_email, product_id)
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    shipping_address TEXT,
    shipping_destination VARCHAR(20) DEFAULT 'US',
    delivery_type VARCHAR(20) DEFAULT 'delivery',
    recipient_name VARCHAR(200),
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (product_id, user_email)
);

CREATE TABLE IF NOT EXISTS incoming_shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sample products for Haiti / diaspora store
INSERT IGNORE INTO products (name, price, category, description, stock_quantity) VALUES
('Portable Solar Panel 20W', 49.99, 'Solar products', 'Lightweight solar panel for charging phones and small devices. Ideal for areas with unreliable grid power.'),
('Solar LED Lantern', 24.99, 'Solar products', 'Rechargeable solar lantern with USB output. Perfect for blackouts and off-grid use.'),
('Basic Smartphone', 89.99, 'Phones & electronics', 'Affordable smartphone with dual SIM. Reliable for calls and messaging.'),
('Power Bank 10000mAh', 29.99, 'Phones & electronics', 'Portable charger for phones and small electronics. Essential when power is scarce.'),
('First Aid Kit', 34.99, 'Survival supplies', 'Comprehensive first aid kit for home and travel.'),
('Water Purification Tablets', 14.99, 'Survival supplies', 'Tablets to make water safe to drink. Pack of 50.'),
('Canned Food Bundle', 39.99, 'Essential household items', 'Assorted canned goods for emergency pantry.'),
('Flashlight + Batteries', 19.99, 'Essential household items', 'Durable flashlight with extra batteries.');

-- Admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password) VALUES
('admin', 'admin@jmf509.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
