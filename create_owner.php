<?php
// Quick script to create owner account
include 'db.php';

$password = 'password123'; // Change this to your desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if owner exists
$check = $conn->query("SELECT employee_id FROM employees WHERE employee_type = 'owner'");
if ($check && $check->num_rows > 0) {
    echo "Owner account already exists!<br>";
    echo "<a href='admin_login.php'>Go to Login</a>";
    exit();
}

// Create owner account
$stmt = $conn->prepare("INSERT INTO employees (employee_type, email, salary, employee_password, hire_date) VALUES (?, ?, ?, ?, CURDATE())");
$type = 'owner';
$email = 'owner@flowershop.com';
$salary = 100000.00;

$stmt->bind_param("ssds", $type, $email, $salary, $hashed_password);

if ($stmt->execute()) {
    echo "✓ Owner account created successfully!<br>";
    echo "<strong>Email:</strong> owner@flowershop.com<br>";
    echo "<strong>Password:</strong> password123<br>";
    echo "<strong>Role:</strong> Owner<br><br>";
    echo "<a href='admin_login.php'>Go to Employee Login</a>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>

