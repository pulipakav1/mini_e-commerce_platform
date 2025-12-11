-- Insert 6 Tulip-themed products into Category 4
-- Note: Make sure category_id = 4 exists before running this script
-- Images should be uploaded to the images folder and paths updated accordingly

-- 1. Pink Tulip Gift Box (product-19)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Pink Tulip Gift Box', 'Elegant pink rectangular gift box filled with coordinated tulip-themed items. Includes a small white ceramic vase with fresh pink tulips, heart-shaped dish with hand-painted tulips, greeting card, silver bracelet with tulip charm, and decorative tin. Tied with cream satin ribbon. Perfect gift for any occasion.', 4, 39.99, 20);
SET @giftbox_id = LAST_INSERT_ID();

-- 2. Tulip Poster Print (product-20)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Poster Print', 'Vibrant poster print featuring "TULIP GARDEN" text with a dense cluster of colorful tulips in red, yellow, pink, and purple. Flat graphic illustration style with subtle grainy texture on teal background. Clean, modern design perfect for home or office decor. Ready to frame.', 4, 18.99, 35);
SET @poster_id = LAST_INSERT_ID();

-- 3. Mini Tulip Statue (product-21)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Mini Tulip Statue', 'Ornate illuminated glass tulip sculpture with warm glow. Features glass bloom transitioning from deep red to orange and golden yellow, with translucent white center elements. Bronze stem and leaf with circular base adorned with dark green moss, river stones, and quartz crystals. Elegant decorative piece.', 4, 89.99, 15);
SET @statue_id = LAST_INSERT_ID();

-- 4. Tulip Greeting Card Set (product-22)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Greeting Card Set', 'Beautiful set of greeting cards in a white box with clear lid. Features delicate watercolor-style illustrations of tulips in soft shades of pink and cream with green stems. Includes cards with messages like "Thinking of You" and "Happy Birthday". Tied with light green satin ribbon. Perfect for any occasion.', 4, 16.99, 40);
SET @cards_id = LAST_INSERT_ID();

-- 5. Tulip-Themed Notebook (product-23)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip-Themed Notebook', 'Spiral-bound journal with cream-colored cover featuring watercolor illustrations of vibrant tulips in pink, red, orange, purple, and yellow with green stems. "JOURNAL" text in green font with tulip outline. Copper-colored metal spiral binding. Perfect for writing, sketching, or daily notes.', 4, 14.99, 45);
SET @notebook_id = LAST_INSERT_ID();

-- 6. Floral Desk Calendar (product-24)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Floral Desk Calendar', 'Spiral-bound desk calendar featuring "FLOWERS OF THE YEAR" title with watercolor illustrations of tulip bouquets. Includes monthly calendar grids with weekend dates highlighted. Features beautiful tulip artwork for each month. Brown cardboard stand with bronze spiral binding. Perfect for desk organization.', 4, 22.99, 30);
SET @calendar_id = LAST_INSERT_ID();

-- Insert images for all products
-- Note: Update the file_path values to match your actual image file locations
-- Place images in: images/category4/ folder with names: product-19.png through product-24.png

INSERT INTO images (product_id, file_path, alt_text) VALUES
(@giftbox_id, 'images/category4/product-19.png', 'Pink Tulip Gift Box'),
(@poster_id, 'images/category4/product-20.png', 'Tulip Poster Print'),
(@statue_id, 'images/category4/product-21.png', 'Mini Tulip Statue'),
(@cards_id, 'images/category4/product-22.png', 'Tulip Greeting Card Set'),
(@notebook_id, 'images/category4/product-23.png', 'Tulip-Themed Notebook'),
(@calendar_id, 'images/category4/product-24.png', 'Floral Desk Calendar');

-- Update inventory for all products
INSERT INTO inventory (product_id, quantity, last_updated) VALUES
(@giftbox_id, 20, CURRENT_TIMESTAMP),
(@poster_id, 35, CURRENT_TIMESTAMP),
(@statue_id, 15, CURRENT_TIMESTAMP),
(@cards_id, 40, CURRENT_TIMESTAMP),
(@notebook_id, 45, CURRENT_TIMESTAMP),
(@calendar_id, 30, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = CURRENT_TIMESTAMP;

