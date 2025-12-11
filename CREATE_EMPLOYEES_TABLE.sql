-- Create employees table (no password, no employee_userid)
-- Just role, email, salary, and hire_date

CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_type ENUM('inventory_manager', 'business_manager', 'owner') NOT NULL,
    salary DECIMAL(10,2),
    hire_date DATE,
    email VARCHAR(255)
);

-- Optional: Insert owner account
INSERT INTO employees (employee_type, email, salary, hire_date) 
VALUES ('owner', 'owner@flowershop.com', 100000.00, CURDATE());

