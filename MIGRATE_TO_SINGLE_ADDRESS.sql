-- Full Migration: Replace shipping_address and billing_address with single 'address' column
-- Run this script in your database

-- Step 1: Add new 'address' column to users table
ALTER TABLE users ADD COLUMN address TEXT NOT NULL AFTER phone_number;

-- Step 2: Migrate existing data (use shipping_address, fallback to billing_address)
UPDATE users SET address = COALESCE(NULLIF(shipping_address, ''), billing_address, 'Not provided');

-- Step 3: Drop old columns from users table
ALTER TABLE users DROP COLUMN shipping_address;
ALTER TABLE users DROP COLUMN billing_address;

-- Step 4: Add 'address' column to orders table
ALTER TABLE orders ADD COLUMN address TEXT NOT NULL AFTER user_id;

-- Step 5: Migrate existing order data (use shipping_address, fallback to billing_address)
UPDATE orders SET address = COALESCE(NULLIF(shipping_address, ''), billing_address, 'Not provided');

-- Step 6: Drop old columns from orders table
ALTER TABLE orders DROP COLUMN shipping_address;
ALTER TABLE orders DROP COLUMN billing_address;

-- Migration complete!

