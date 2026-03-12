-- Run these SQL commands in your PostgreSQL database to update the schema

-- Add category column if it doesn't exist
ALTER TABLE products ADD COLUMN IF NOT EXISTS category VARCHAR(100);

-- Update category from the categories table
UPDATE products p
SET category = c.name
FROM categories c
WHERE p.category_id = c.id;

-- If any products don't have a category, set a default
UPDATE products SET category = 'Electronics' WHERE category IS NULL;

-- Verify the data
SELECT id, name, category, price, stock_quantity FROM products LIMIT 10;