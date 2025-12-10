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

// Fetch employee details
$sql = "SELECT id, admin_userid, role, email, salary FROM admins WHERE id = ?";
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
    <p><strong>User ID:</strong> <?= htmlspecialchars($employee['admin_userid']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($employee['role']) ?></p>
    <p><strong>Salary:</strong> <span class="salary">$<?= number_format($employee['salary'], 2) ?></span></p>
</div>

</body>
</html>
