-- JMF 509 Warehouse - PostgreSQL schema for PostgREST
-- Run: psql -U postgres -d jmf509_warehouse -f schema_postgres.sql

-- Role for PostgREST (create before tables if using separate DB user)
-- CREATE ROLE web_user WITH LOGIN PASSWORD 'your_password';
-- GRANT ALL ON SCHEMA public TO web_user;

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    image BYTEA,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS carts (
    cart_id SERIAL PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (user_email, product_id)
);

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
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
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (product_id, user_email)
);

CREATE TABLE IF NOT EXISTS incoming_shipments (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Enable RLS (Row Level Security) for PostgREST - optional, configure per-table as needed
-- ALTER TABLE products ENABLE ROW LEVEL SECURITY;

-- Sample products (skip if any exist)
INSERT INTO products (name, price, category, description, stock_quantity)
SELECT * FROM (VALUES
    ('Portable Solar Panel 20W', 49.99, 'Solar products', 'Lightweight solar panel for charging phones and small devices. Ideal for areas with unreliable grid power.', 0),
    ('Solar LED Lantern', 24.99, 'Solar products', 'Rechargeable solar lantern with USB output. Perfect for blackouts and off-grid use.', 0),
    ('Basic Smartphone', 89.99, 'Phones & electronics', 'Affordable smartphone with dual SIM. Reliable for calls and messaging.', 0),
    ('Power Bank 10000mAh', 29.99, 'Phones & electronics', 'Portable charger for phones and small electronics. Essential when power is scarce.', 0),
    ('First Aid Kit', 34.99, 'Survival supplies', 'Comprehensive first aid kit for home and travel.', 0),
    ('Water Purification Tablets', 14.99, 'Survival supplies', 'Tablets to make water safe to drink. Pack of 50.', 0),
    ('Canned Food Bundle', 39.99, 'Essential household items', 'Assorted canned goods for emergency pantry.', 0),
    ('Flashlight + Batteries', 19.99, 'Essential household items', 'Durable flashlight with extra batteries.', 0)
) AS v(name, price, category, description, stock_quantity)
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

-- Admin user (password: admin123)
INSERT INTO users (username, email, password)
VALUES ('admin', 'admin@jmf509.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON CONFLICT (email) DO NOTHING;
