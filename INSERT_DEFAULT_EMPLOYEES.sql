-- Insert Default Employee Accounts
-- Run this SQL file in phpMyAdmin after creating the employees table

-- ============================================
-- IMPORTANT: Password hashes are unique each time they're generated!
-- ============================================
-- Instead of using hardcoded hashes, run fix_employee_passwords.php after inserting
-- OR use the PHP script to generate correct hashes first
-- ============================================

-- Owner Account
-- NOTE: The password_hash below may not work. Use fix_employee_passwords.php instead
INSERT INTO employees (employee_userid, employee_type, email, salary, employee_password, hire_date) 
VALUES (
    'owner',
    'owner',
    'owner@flowershop.com',
    100000.00,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: 'password'
    CURDATE()
);

-- Business Manager Account
INSERT INTO employees (employee_userid, employee_type, email, salary, employee_password, hire_date) 
VALUES (
    'manager',
    'business_manager',
    'manager@flowershop.com',
    75000.00,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: 'password'
    CURDATE()
);

-- Inventory Manager Account
INSERT INTO employees (employee_userid, employee_type, email, salary, employee_password, hire_date) 
VALUES (
    'inventory',
    'inventory_manager',
    'inventory@flowershop.com',
    50000.00,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: 'password'
    CURDATE()
);

-- ============================================
-- AFTER RUNNING THIS SQL:
-- 1. Access: http://your-site.com/fix_employee_passwords.php (as owner)
-- 2. This will update all employee passwords with correct hashes
-- OR manually update each password using password_hash('password', PASSWORD_DEFAULT) in PHP
-- ============================================

-- ============================================
-- DEFAULT LOGIN CREDENTIALS:
-- ============================================
-- 
-- OWNER:
--   Username: owner
--   Password: password
--   Access: Full access (Products, Orders, HR, Reports)
--
-- BUSINESS MANAGER:
--   Username: manager
--   Password: password
--   Access: Products, Orders
--
-- INVENTORY MANAGER:
--   Username: inventory
--   Password: password
--   Access: Update Inventory only
--
-- ============================================
-- IMPORTANT: 
-- 1. Run this SQL file in phpMyAdmin after creating tables
-- 2. Change these passwords after first login!
-- ============================================
