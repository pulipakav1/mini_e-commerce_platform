<?php
session_start();
include '../db.php';

// Only owner can view
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'owner') {
    die("Access denied! Only owner can view this page.");
}

if (!isset($_GET['id'])) {
    die("Invalid employee ID.");
}

$admin_id = intval($_GET['id']);

// Fetch employee details (using employees table)
$sql = "SELECT employee_id, employee_userid, employee_type, email, salary, hire_date FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    die("Employee not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Employee</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; padding:20px; }
        .container { 
            background:white; 
            width:500px; 
            margin:auto; 
            padding:20px; 
            border-radius:10px; 
            box-shadow:0 3px 10px rgba(0,0,0,0.1); 
        }
        .back-btn { color:#1d4ed8; text-decoration:none; margin-bottom:15px; display:inline-block; }
        h2 { text-align:center; margin-bottom:20px; }
        p { font-size:16px; margin:8px 0; }
        .salary { color: #10b981; font-weight: bold; }
    </style>
</head>
<body>

<a href="hr.php" class="back-btn">Back to HR Section</a>

<div class="container">
    <h2>Employee Details</h2>
    <p><strong>Employee ID:</strong> <?= htmlspecialchars($employee['employee_id']) ?></p>
    <p><strong>User ID:</strong> <?= htmlspecialchars($employee['employee_userid'] ?? 'N/A') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($employee['email'] ?? 'N/A') ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($employee['employee_type']) ?></p>
    <p><strong>Salary:</strong> <span class="salary">$<?= number_format($employee['salary'] ?? 0, 2) ?></span></p>
    <?php if (isset($employee['hire_date'])): ?>
        <p><strong>Hire Date:</strong> <?= htmlspecialchars($employee['hire_date']) ?></p>
    <?php endif; ?>
</div>

</body>
</html>
