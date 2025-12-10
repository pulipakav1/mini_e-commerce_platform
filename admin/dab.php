<?php
session_start();

// Protect this file - only allow if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'owner') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

$admin_userid = 'admin';
$admin_password = 'password123';
$role = 'owner';
$email = 'admin@example.com';
$salary = 50000;

$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$sql = "INSERT INTO employees (employee_userid, employee_password, employee_type, email, salary, hire_date) VALUES (?, ?, ?, ?, ?, CURDATE())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssd", $admin_userid, $hashed_password, $role, $email, $salary);

if ($stmt->execute()) {
    echo "Admin account created. Username: admin, Password: password123";
    echo "<br>DELETE THIS FILE (admin/dab.php) FOR SECURITY!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>
