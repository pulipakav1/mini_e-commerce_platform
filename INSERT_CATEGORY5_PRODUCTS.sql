-- Insert 6 Tulip-themed products into Category 5
-- Note: Make sure category_id = 5 exists before running this script
-- Images should be uploaded to the images folder and paths updated accordingly

-- 1. Mini Jade Plant (product-25)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Mini Jade Plant', 'Small, low-maintenance indoor jade plant (Crassula ovata) with thick, gnarled trunk and plump, glossy green leaves with reddish-brown edges. Features a handcrafted ceramic pot with earthy brown base and creamy-white glaze. Perfect for beginners, requires minimal watering and bright indirect light.', 5, 24.99, 30);
SET @jade_id = LAST_INSERT_ID();

-- 2. Snake Plant Starter (product-26)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Snake Plant Starter', 'Easy beginner-friendly indoor snake plant (Sansevieria trifasciata) with upright, sword-shaped variegated leaves in dark green and yellowish-green stripes. Comes in classic terracotta pot with saucer. Includes plant marker. Low maintenance, perfect for low-light conditions.', 5, 19.99, 35);
SET @snake_id = LAST_INSERT_ID();

-- 3. Aloe Vera Pot (product-27)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Aloe Vera Pot', 'Healthy aloe vera plant with thick, fleshy succulent leaves arranged in a rosette pattern. Features vibrant green leaves with light spots and serrated edges. Comes in decorative terracotta pot with Greek key pattern around the rim. Includes rustic wooden coaster stand. Great for air purification.', 5, 22.99, 28);
SET @aloe_id = LAST_INSERT_ID();

-- 4. Lavender Mini Pot (product-28)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Lavender Mini Pot', 'Calming lavender plant for desktops with slender green stems and soft purple flower spikes. Housed in weathered terracotta pot with distressed texture. Includes white hexagonal "Relax" coaster. Perfect for adding a touch of nature and fragrance to your workspace. Promotes relaxation and focus.', 5, 18.99, 32);
SET @lavender_id = LAST_INSERT_ID();

-- 5. Peace Lily Small (product-29)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Peace Lily Small', 'Compact peace lily plant with lush dark green leaves and multiple bright white spathes (blooms). Features modern white cylindrical ceramic pot with subtle textured finish. Perfect for indoor spaces, helps purify air and adds elegance to any room. Low maintenance and thrives in indirect light.', 5, 26.99, 25);
SET @peacelily_id = LAST_INSERT_ID();

-- 6. Mini Bonsai Tree (product-30)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Mini Bonsai Tree', 'Beginner-friendly mini bonsai tree with dense canopy of small, bright green oval leaves and thick, gnarled dark brown trunk. Includes copper wire for shaping, light gray ceramic pot, and wooden saucer. Perfect for learning bonsai techniques. Comes with care instructions for easy maintenance.', 5, 34.99, 20);
SET @bonsai_id = LAST_INSERT_ID();

-- Insert images for all products
-- Note: Update the file_path values to match your actual image file locations
-- Place images in: images/category5/ folder with names: product-25.png through product-30.png

INSERT INTO images (product_id, file_path, alt_text) VALUES
(@jade_id, 'images/category5/product-25.png', 'Mini Jade Plant'),
(@snake_id, 'images/category5/product-26.png', 'Snake Plant Starter'),
(@aloe_id, 'images/category5/product-27.png', 'Aloe Vera Pot'),
(@lavender_id, 'images/category5/product-28.png', 'Lavender Mini Pot'),
(@peacelily_id, 'images/category5/product-29.png', 'Peace Lily Small'),
(@bonsai_id, 'images/category5/product-30.png', 'Mini Bonsai Tree');

-- Update inventory for all products
INSERT INTO inventory (product_id, quantity, last_updated) VALUES
(@jade_id, 30, CURRENT_TIMESTAMP),
(@snake_id, 35, CURRENT_TIMESTAMP),
(@aloe_id, 28, CURRENT_TIMESTAMP),
(@lavender_id, 32, CURRENT_TIMESTAMP),
(@peacelily_id, 25, CURRENT_TIMESTAMP),
(@bonsai_id, 20, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = CURRENT_TIMESTAMP;

