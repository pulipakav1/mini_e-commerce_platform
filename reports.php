<?php
session_start();
include 'db.php'; // Ensure the path to db.php is correct

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Only owner can access reports
if ($_SESSION['admin_role'] != 'owner') {
    header("Location: dashboard.php");
    exit();
}

// Fetch report data
$productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orderCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$employeeCount = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports / Analytics</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; }
        .container { max-width: 900px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; margin-bottom: 30px; }
        .stats { display: flex; justify-content: space-around; flex-wrap: wrap; }
        .stat { background: #1d4ed8; color: white; padding: 20px; border-radius: 12px; font-size: 18px; text-align: center; width: 200px; margin: 10px; transition: background 0.3s; text-decoration: none; }
        .stat:hover { background: #0d62d2; }
        .stat h3 { margin-bottom: 10px; }
        .stat p { font-size: 28px; margin: 0; }
    </style>
</head>
<body>

<div class="container">
    <h2>Reports / Analytics</h2>

    <div class="stats">
        <a href="products.php" class="stat">
            <h3>Products</h3>
            <p><?php echo $productCount; ?></p>
        </a>
        <a href="orders.php" class="stat">
            <h3>Orders</h3>
            <p><?php echo $orderCount; ?></p>
        </a>
        <a href="sales_report.php" class="stat" style="background: #10b981;">
            <h3>Sales Report</h3>
            <p>View</p>
        </a>
        <a href="hr.php" class="stat">
            <h3>Employees</h3>
            <p><?php echo $employeeCount; ?></p>
        </a>
    </div>
</div>

</body>
</html>
