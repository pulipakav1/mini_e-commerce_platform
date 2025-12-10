-- Verify and Add Foreign Key: receipts.order_id -> orders.order_id
-- This ensures data integrity between orders and receipts

-- Check if foreign key already exists (run this first to check)
-- SELECT CONSTRAINT_NAME 
-- FROM information_schema.KEY_COLUMN_USAGE 
-- WHERE TABLE_SCHEMA = 'jampav1_toronto' 
--   AND TABLE_NAME = 'receipts' 
--   AND COLUMN_NAME = 'order_id' 
--   AND REFERENCED_TABLE_NAME IS NOT NULL;

-- If the foreign key doesn't exist, run these ALTER TABLE statements:

-- First, ensure the order_id column exists and is properly typed
ALTER TABLE receipts 
MODIFY COLUMN order_id INT NOT NULL;

-- Add the foreign key constraint (if it doesn't exist)
ALTER TABLE receipts
ADD CONSTRAINT fk_receipt_order 
FOREIGN KEY (order_id) 
REFERENCES orders(order_id) 
ON UPDATE CASCADE 
ON DELETE CASCADE;

-- Add UNIQUE constraint to ensure one receipt per order
ALTER TABLE receipts
ADD UNIQUE KEY unique_order_receipt (order_id);

-- Verify the foreign key was created:
-- SHOW CREATE TABLE receipts;
-- You should see: CONSTRAINT `fk_receipt_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE

