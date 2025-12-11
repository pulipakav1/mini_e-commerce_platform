-- Insert 6 Tulip-themed products into Category 3
-- Note: Make sure category_id = 3 exists before running this script
-- Images should be uploaded to the images folder and paths updated accordingly

-- 1. Tulip Tote Bag (product-13)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Tote Bag', 'Light, reusable canvas tote bag with a minimal floral design. Features a stylized drawing of two pink tulips with sage green stems and leaves, along with "TULIP" text. Cream-colored canvas material with comfortable handles. Perfect for shopping, beach trips, or everyday use.', 3, 24.99, 30);
SET @tote_id = LAST_INSERT_ID();

-- 2. Floral Phone Case (product-14)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Floral Phone Case', 'Stylish transparent phone case with elegant watercolor-style illustration of light pink tulips with green stems and leaves. Clear flexible design allows your phone\'s original color to show through. Precise cutouts for camera, buttons, and ports. Protects your phone while showcasing beautiful tulip art.', 3, 19.99, 45);
SET @phonecase_id = LAST_INSERT_ID();

-- 3. Keychain Charm (product-15)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Keychain Charm', 'Delicate tulip-shaped charm keychain in soft pastel pink with pale green stem. Features a shiny gold-colored metal split key ring and jump rings. Smooth, glossy finish with elegant design. Perfect for keys or bags, adding a touch of floral elegance to your everyday accessories.', 3, 12.99, 50);
SET @keychain_id = LAST_INSERT_ID();

-- 4. Mini Wallet (product-16)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Mini Wallet', 'Compact wallet in dusty rose pink with a subtle repeating floral pattern of white four-petaled flowers with light yellow centers. Features delicate green stems and leaves, gold snap button closure, and neat rounded edges. Perfect size for essentials with elegant craftsmanship.', 3, 22.99, 35);
SET @wallet_id = LAST_INSERT_ID();

-- 5. Tulip Hair Clip Set (product-17)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Tulip Hair Clip Set', 'Two-piece hair accessory set featuring gold-toned metal with white and pink enamel tulip designs. Includes a snap clip with two side-by-side tulip blossoms and a U-shaped hair pin with a single tulip blossom. Both feature delicate pink blushing and gold outlines. Elegant and sophisticated design.', 3, 16.99, 40);
SET @hairclip_id = LAST_INSERT_ID();

-- 6. Fabric Headband (product-18)
INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES
('Fabric Headband', 'Soft white fabric headband featuring a vibrant tulip pattern in various shades of red, pink, and orange with green stems and leaves. Decorative knot tied in the center front creating a gathered, bow-like appearance. Comfortable elasticized back for secure fit. Perfect for adding a floral touch to your style.', 3, 14.99, 38);
SET @headband_id = LAST_INSERT_ID();

-- Insert images for all products
-- Note: Update the file_path values to match your actual image file locations
-- Place images in: images/category3/ folder with names: product-13.png through product-18.png

INSERT INTO images (product_id, file_path, alt_text) VALUES
(@tote_id, 'images/category3/product-13.png', 'Tulip Tote Bag'),
(@phonecase_id, 'images/category3/product-14.png', 'Floral Phone Case'),
(@keychain_id, 'images/category3/product-15.png', 'Keychain Charm'),
(@wallet_id, 'images/category3/product-16.png', 'Mini Wallet'),
(@hairclip_id, 'images/category3/product-17.png', 'Tulip Hair Clip Set'),
(@headband_id, 'images/category3/product-18.png', 'Fabric Headband');

-- Update inventory for all products
INSERT INTO inventory (product_id, quantity, last_updated) VALUES
(@tote_id, 30, CURRENT_TIMESTAMP),
(@phonecase_id, 45, CURRENT_TIMESTAMP),
(@keychain_id, 50, CURRENT_TIMESTAMP),
(@wallet_id, 35, CURRENT_TIMESTAMP),
(@hairclip_id, 40, CURRENT_TIMESTAMP),
(@headband_id, 38, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = CURRENT_TIMESTAMP;

