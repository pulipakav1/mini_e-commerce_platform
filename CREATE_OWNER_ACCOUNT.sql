-- Create Owner Account
-- Run this SQL script to create an owner account
-- Default password: password123 (you can change it after logging in)

-- Check if owner already exists
SELECT COUNT(*) as owner_count FROM employees WHERE employee_type = 'owner';

-- If no owner exists, insert one
-- Change 'password123' to your desired password before running
INSERT INTO employees (employee_type, email, salary, employee_password, hire_date) 
VALUES ('owner', 'owner@flowershop.com', 100000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', CURDATE());

-- Note: The password hash above is for "password123"
-- To use a different password, generate a new hash using PHP's password_hash() function
-- Example: password_hash('yourpassword', PASSWORD_DEFAULT)

