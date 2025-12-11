-- Option 1: Keep both columns (RECOMMENDED - No changes needed)
-- The current code already works with this approach:
-- - When inserting: uses the same address for both shipping_address and billing_address
-- - When reading: uses shipping_address as primary, falls back to billing_address
-- This maintains backward compatibility with existing data.

-- NO CHANGES NEEDED - Your current setup already works!


-- ============================================
-- Option 2: Add single 'address' column (OPTIONAL)
-- Only use this if you want to simplify the schema completely
-- ============================================

-- Step 1: Add new address column
-- ALTER TABLE users ADD COLUMN address TEXT NOT NULL AFTER phone_number;

-- Step 2: Copy data from shipping_address to address (for existing users)
-- UPDATE users SET address = shipping_address WHERE address IS NULL OR address = '';

-- Step 3: Make shipping_address and billing_address nullable (so old code doesn't break)
-- ALTER TABLE users MODIFY shipping_address TEXT NULL;
-- ALTER TABLE users MODIFY billing_address TEXT NULL;

-- Step 4: Keep both columns for backward compatibility, but use 'address' for new records
-- (This allows gradual migration)

-- ============================================
-- Option 3: Complete migration to single column (NOT RECOMMENDED)
-- This would break existing code and require more changes
-- ============================================

-- After adding 'address' column and migrating data:
-- ALTER TABLE users DROP COLUMN shipping_address;
-- ALTER TABLE users DROP COLUMN billing_address;
-- Then update all PHP code to use 'address' instead

