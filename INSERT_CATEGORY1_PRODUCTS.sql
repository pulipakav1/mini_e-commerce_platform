-- Insert 6 Tulip-themed products into Category 1
-- Note: Make sure category_id = 1 exists before running this script
-- Images should be uploaded to the images folder and paths updated accordingly

-- 1. Tulip Bouquet Table Lamp
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Bouquet Table Lamp', 'Elegant decorative table lamp featuring five individual tulip-shaped light fixtures with warm, soft lighting. Golden-bronze stems and base with frosted glass tulip shades. Perfect for adding a cozy, floral touch to any room.', 1, 89.99, 15);
SET @lamp_id = LAST_INSERT_ID();

-- 2. Tulip Bloom Scented Candle
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Bloom Light Floral Scented Candle', 'Premium scented candle from the Cozy Home Collection. Features a light floral tulip bloom fragrance in a clear glass jar with floating rose petals. Creates a warm, inviting atmosphere for your home.', 1, 24.99, 30);
SET @candle_id = LAST_INSERT_ID();

-- 3. Tulip Wall Clock
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Wall Clock', 'Beautiful hand-painted wall clock featuring vibrant orange-red and yellow-orange tulips on a creamy off-white face. Light wooden frame with classic design. Perfect for kitchen or home decor.', 1, 39.99, 20);
SET @clock_id = LAST_INSERT_ID();

-- 4. Tulip Vase with Floral Arrangement
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Sculptural Tulip Vase with Floral Arrangement', 'Unique organically shaped ceramic vase resembling opening tulip buds. Includes a beautiful arrangement of pink roses, white freesias, purple lavender, eucalyptus, and dried seed pods. Modern and elegant centerpiece.', 1, 79.99, 12);
SET @vase_id = LAST_INSERT_ID();

-- 5. Tulip Watercolor Painting
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Watercolor Art Print', 'Delicate watercolor painting of a single vibrant tulip with gradient petals transitioning from soft reddish-pink to warm peach. Framed in a light wooden frame with white mat board. Perfect for minimalist home decor.', 1, 49.99, 25);
SET @painting_id = LAST_INSERT_ID();

-- 6. Tulip Pattern Throw Pillow
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Pattern Decorative Throw Pillow', 'Vibrant square throw pillow featuring an artistic design of numerous orange and red tulips with green stems and leaves. Soft plush fabric with a cozy, comfortable feel. Adds a pop of color to any sofa or armchair.', 1, 34.99, 18);
SET @pillow_id = LAST_INSERT_ID();

-- Insert images for all products
-- Note: Update the file_path values to match your actual image file locations
-- Place images in: images/category1/ folder with names: product-1.png, product-2.png, etc.

INSERT INTO images (product_id, file_path, alt_text) VALUES
(@lamp_id, 'images/category1/product-1.png', 'Tulip Bouquet Table Lamp'),
(@candle_id, 'images/category1/product-2.png', 'Tulip Bloom Scented Candle'),
(@clock_id, 'images/category1/product-3.png', 'Tulip Wall Clock'),
(@vase_id, 'images/category1/product-4.png', 'Sculptural Tulip Vase with Floral Arrangement'),
(@painting_id, 'images/category1/product-5.png', 'Tulip Watercolor Art Print'),
(@pillow_id, 'images/category1/product-6.png', 'Tulip Pattern Decorative Throw Pillow');

-- Update inventory for all products
INSERT INTO inventory (product_id, quantity, last_updated) VALUES
(@lamp_id, 15, CURRENT_TIMESTAMP),
(@candle_id, 30, CURRENT_TIMESTAMP),
(@clock_id, 20, CURRENT_TIMESTAMP),
(@vase_id, 12, CURRENT_TIMESTAMP),
(@painting_id, 25, CURRENT_TIMESTAMP),
(@pillow_id, 18, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = CURRENT_TIMESTAMP;

