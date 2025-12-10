CREATE TABLE users (
user_id INT AUTO_INCREMENT PRIMARY KEY,
user_name VARCHAR(150) NOT NULL UNIQUE,
name VARCHAR(255) NOT NULL,
role ENUM('client', 'inventory_manager', 'business_manager', 'owner')
NOT NULL DEFAULT 'client',
phone_number VARCHAR(20),
shipping_address TEXT NOT NULL,
billing_address TEXT NOT NULL
);
CREATE TABLE orders (
order_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
shipping_address TEXT NOT NULL,
billing_address TEXT NOT NULL,
total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
CONSTRAINT fk_orders_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE TABLE receipts (
receipt_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL UNIQUE,
receipt_number VARCHAR(100) NOT NULL UNIQUE,
receipt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT fk_receipt_order
FOREIGN KEY (order_id) REFERENCES orders(order_id)
ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE TABLE order_items (
order_item_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
product_id INT NOT NULL,
quantity INT NOT NULL CHECK (quantity > 0),
unit_price DECIMAL(10,2) NOT NULL,
CONSTRAINT fk_orderitems_order
FOREIGN KEY (order_id) REFERENCES orders(order_id)
ON UPDATE CASCADE ON DELETE CASCADE,
CONSTRAINT fk_orderitems_product
FOREIGN KEY (product_id) REFERENCES products(product_id)
ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE TABLE payment (
payment_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
payment_method VARCHAR(50) NOT NULL,
total_amount DECIMAL(10,2) NOT NULL,
payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT fk_payment_order
FOREIGN KEY (order_id) REFERENCES orders(order_id)
ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE TABLE employees (
employee_id INT AUTO_INCREMENT PRIMARY KEY,
employee_type ENUM('inventory_manager', 'business_manager', 'owner') NOT NULL,
salary DECIMAL(10,2),
hire_date DATE
);
CREATE TABLE inventory (
inventory_id INT AUTO_INCREMENT PRIMARY KEY,
product_id INT NOT NULL,
quantity INT NOT NULL DEFAULT 0,
last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT fk_inventory_product
FOREIGN KEY (product_id) REFERENCES products(product_id)
ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE TABLE category (
category_id INT AUTO_INCREMENT PRIMARY KEY,
category_name VARCHAR(150) NOT NULL UNIQUE,
category_description TEXT
);
CREATE TABLE products (
product_id INT AUTO_INCREMENT PRIMARY KEY,
product_name VARCHAR(255) NOT NULL,
product_description TEXT,
category_id INT NOT NULL,
cost DECIMAL(10,2) NOT NULL,
quantity INT NOT NULL DEFAULT 0,
CONSTRAINT fk_products_category
FOREIGN KEY (category_id) REFERENCES category(category_id)
ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE TABLE images (
image_id INT AUTO_INCREMENT PRIMARY KEY,
product_id INT NOT NULL,
file_path VARCHAR(255) NOT NULL,
alt_text VARCHAR(255),
CONSTRAINT fk_images_product
FOREIGN KEY (product_id) REFERENCES products(product_id)
ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE TABLE cart (
cart_id INT AUTO_INCREMENT PRIMARY KEY,
product_id INT NOT NULL,
user_id INT NOT NULL,
quantity INT NOT NULL CHECK (quantity > 0),
CONSTRAINT fk_cart_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON UPDATE CASCADE ON DELETE CASCADE,
CONSTRAINT fk_cart_product
);
FOREIGN KEY (product_id) REFERENCES products(product_id)
ON UPDATE CASCADE ON DELETE CASCADE