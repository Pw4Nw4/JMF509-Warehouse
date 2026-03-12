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
    ('Wireless Bluetooth Earbuds', 49.99, 'Electronics', 'High-quality wireless earbuds with noise cancellation and long battery life.', 50),
    ('Smart Watch Series 8', 299.99, 'Electronics', 'Advanced smartwatch with health tracking, GPS, and waterproof design.', 25),
    ('Portable Power Bank 20000mAh', 39.99, 'Electronics', 'Fast charging portable battery for all your devices on the go.', 100),
    ('4K Webcam with Microphone', 79.99, 'Electronics', 'Crystal clear video for meetings and streaming.', 40),
    ('Men''s Classic Cotton T-Shirt Pack', 29.99, 'Clothing & Fashion', 'Pack of 5 premium cotton t-shirts in assorted colors.', 200),
    ('Women''s Summer Dress', 45.99, 'Clothing & Fashion', 'Lightweight and breathable summer dress perfect for any occasion.', 80),
    ('Running Shoes Pro', 89.99, 'Clothing & Fashion', 'Professional running shoes with cushioned sole for maximum comfort.', 60),
    ('Leather Wallet', 34.99, 'Clothing & Fashion', 'Genuine leather wallet with multiple card slots.', 120),
    ('Garden Tool Set', 59.99, 'Home & Garden', 'Complete 12-piece garden tool set with carrying case.', 45),
    ('LED Desk Lamp', 24.99, 'Home & Garden', 'Adjustable LED desk lamp with USB charging port.', 90),
    ('Memory Foam Pillow', 39.99, 'Home & Garden', 'Premium memory foam pillow for better sleep quality.', 75),
    ('Indoor Plant Set', 34.99, 'Home & Garden', 'Set of 3 easy-care indoor plants with decorative pots.', 55),
    ('Anti-Aging Skincare Set', 69.99, 'Beauty & Health', 'Complete skincare routine with moisturizer, serum, and cleanser.', 65),
    ('Vitamin D3 Supplements', 19.99, 'Beauty & Health', '90-day supply of Vitamin D3 softgels for bone health.', 150),
    ('Professional Hair Dryer', 79.99, 'Beauty & Health', 'Ion hair dryer with multiple heat settings and attachments.', 35),
    ('Yoga Mat Premium', 34.99, 'Sports & Outdoors', 'Non-slip yoga mat with carrying strap.', 85),
    ('Camping Tent 4-Person', 129.99, 'Sports & Outdoors', 'Waterproof camping tent easy to set up.', 30),
    ('Basketball Official Size', 29.99, 'Sports & Outdoors', 'Professional basketball with excellent grip.', 70),
    ('Board Game Classic Strategy', 39.99, 'Toys & Games', 'Family-friendly strategy board game for all ages.', 55),
    ('Remote Control Car', 49.99, 'Toys & Games', 'High-speed RC car with rechargeable battery.', 40),
    ('Building Blocks Set 500pc', 34.99, 'Toys & Games', 'Creative building blocks for kids and adults.', 60),
    ('Bestselling Novel Collection', 49.99, 'Books & Media', 'Collection of 10 bestselling novels in hardcover.', 45),
    ('Wireless Gaming Headset', 79.99, 'Books & Media', 'Surround sound gaming headset with microphone.', 50),
    ('Car Phone Mount', 19.99, 'Automotive', 'Universal magnetic phone mount for car dashboard.', 200),
    ('Car Vacuum Cleaner', 49.99, 'Automotive', 'Portable car vacuum with multiple attachments.', 55),
    ('Organic Coffee Beans 2lb', 29.99, 'Food & Grocery', 'Premium organic coffee beans, medium roast.', 80),
    ('Protein Bar Pack', 34.99, 'Food & Grocery', 'Box of 12 protein bars in assorted flavors.', 100),
    ('Baby Stroller Lightweight', 199.99, 'Baby & Kids', 'Foldable baby stroller with sun canopy.', 20),
    ('Educational Learning Tablet', 79.99, 'Baby & Kids', 'Kids tablet with educational games and parental controls.', 35),
    ('School Backpack', 39.99, 'Office & School Supplies', 'Durable backpack with laptop compartment.', 90),
    ('Desk Organizer Set', 24.99, 'Office & School Supplies', '6-piece desk organizer for pens, papers, and supplies.', 75),
    ('Gold Plated Necklace', 44.99, 'Jewelry & Watches', 'Elegant gold plated necklace perfect for gifts.', 65),
    ('Stainless Steel Watch', 89.99, 'Jewelry & Watches', 'Water-resistant stainless steel watch with leather band.', 40),
    ('Premium Dog Food 20lb', 49.99, 'Pet Supplies', 'Nutritious dry dog food for all life stages.', 60),
    ('Cat Tower Multi-Level', 79.99, 'Pet Supplies', 'Multi-level cat tree with scratching posts.', 25),
    ('Acoustic Guitar Beginner', 129.99, 'Music & Instruments', 'Full-size acoustic guitar with accessories kit.', 30),
    ('Digital Piano 88 Keys', 399.99, 'Music & Instruments', 'Weighted key digital piano with stand and pedals.', 15),
    ('Website Hosting 1 Year', 79.99, 'Services & Digital', '1-year premium website hosting with domain.', 500),
    ('Online Course Bundle', 99.99, 'Services & Digital', 'Access to 50+ online courses for personal development.', 200)
) AS v(name, price, category, description, stock_quantity)
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

-- Admin user (password: admin123)
INSERT INTO users (username, email, password)
VALUES ('admin', 'admin@jmf509.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON CONFLICT (email) DO NOTHING;
