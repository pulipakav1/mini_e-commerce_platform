-- Insert 6 Tulip-themed products into Category 2
-- Note: Make sure category_id = 2 exists before running this script
-- Images should be uploaded to the images folder and paths updated accordingly

-- 1. Glass Tea Infuser Bottle (product-7)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Floral Tea Infuser Bottle', 'Elegant clear glass tea infuser bottle with delicate floral and hummingbird designs. Includes stainless steel infuser basket for loose tea leaves. Perfect for steeping your favorite teas on the go. Features a beautiful natural aesthetic with steam-ready design.', 2, 29.99, 25);
SET @bottle1_id = LAST_INSERT_ID();

-- 2. Tulip Design Ceramic Mug (product-8)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Design Ceramic Mug', 'Beautiful white ceramic mug featuring a stylized red tulip design with green stem and leaves. Classic C-shaped handle, perfect for your morning coffee or tea. Steam-ready design for hot beverages. Adds elegance to any kitchen or office setting.', 2, 18.99, 35);
SET @mug_id = LAST_INSERT_ID();

-- 3. Tulip Pattern Reusable Coffee Cup (product-9)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Pattern Reusable Coffee Cup', 'Eco-friendly reusable coffee cup with creamy beige base and repeating pattern of stylized pink tulips and green stems. Light pink lid with ribbed light green silicone band. Features "NATURE\'S BLOOM" branding. Perfect for sustainable coffee lovers.', 2, 22.99, 32);
SET @coffeecup_id = LAST_INSERT_ID();

-- 4. Iced Tea Glasses Set (product-10)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Iced Tea Glasses Set with Tulip Accents', 'Beautiful set of two clear textured glasses perfect for iced tea or lemonade. Includes decorative pink and white tulip petals. Taller and shorter glass options for variety. Perfect for refreshing beverages on warm days. Ideal for entertaining or personal use.', 2, 19.99, 40);
SET @glasses_id = LAST_INSERT_ID();

-- 5. Stainless Steel Travel Mug (product-11)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Stainless Steel Travel Mug', 'Premium brushed stainless steel travel mug with matte black silicone band for secure grip. Clear translucent lid with flip-open tab. Durable construction perfect for commuting or outdoor adventures. Keeps beverages at the perfect temperature.', 2, 24.99, 30);
SET @travelmug_id = LAST_INSERT_ID();

-- 6. Floral Pattern Insulated Water Bottle (product-12)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Floral Pattern Insulated Water Bottle', 'Vibrant teal-colored insulated water bottle with intricate white line-art floral patterns and pink accents. Sleek curved design with brushed metallic silver lid. Keeps beverages cold for hours. Perfect for staying hydrated in style.', 2, 34.99, 28);
SET @bottle2_id = LAST_INSERT_ID();

-- Insert images for all products
-- Note: Update the file_path values to match your actual image file locations
-- Place images in: images/category2/ folder with names: product-7.png through product-12.png

INSERT INTO images (product_id, file_path, alt_text) VALUES
(@bottle1_id, 'images/category2/product-7.png', 'Floral Tea Infuser Bottle'),
(@mug_id, 'images/category2/product-8.png', 'Tulip Design Ceramic Mug'),
(@coffeecup_id, 'images/category2/product-9.png', 'Tulip Pattern Reusable Coffee Cup'),
(@glasses_id, 'images/category2/product-10.png', 'Iced Tea Glasses Set with Tulip Accents'),
(@travelmug_id, 'images/category2/product-11.png', 'Stainless Steel Travel Mug'),
(@bottle2_id, 'images/category2/product-12.png', 'Floral Pattern Insulated Water Bottle');

-- Update inventory for all products
INSERT INTO inventory (product_id, quantity, last_updated) VALUES
(@bottle1_id, 25, CURRENT_TIMESTAMP),
(@mug_id, 35, CURRENT_TIMESTAMP),
(@coffeecup_id, 32, CURRENT_TIMESTAMP),
(@glasses_id, 40, CURRENT_TIMESTAMP),
(@travelmug_id, 30, CURRENT_TIMESTAMP),
(@bottle2_id, 28, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = CURRENT_TIMESTAMP;

