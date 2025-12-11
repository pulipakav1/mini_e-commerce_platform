-- Create employees table with password
-- Role, email, salary, hire_date, and password

CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_type ENUM('inventory_manager', 'business_manager', 'owner') NOT NULL,
    salary DECIMAL(10,2),
    hire_date DATE,
    email VARCHAR(255),
    employee_password VARCHAR(255) NOT NULL
);

