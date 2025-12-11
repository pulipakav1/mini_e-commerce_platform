<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Only owner can access orders page
if ($_SESSION['admin_role'] != 'owner') {
    header("Location: dashboard.php");
    exit();
}

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders")->fetch_assoc()['total'];

// Fetch all orders with user information
$orders_query = "SELECT o.*, u.name as user_name, u.email as user_email, 
                  r.receipt_number 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  LEFT JOIN receipts r ON o.order_id = r.order_id 
                  ORDER BY o.order_date DESC";
$orders_result = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 18px;
            color: #1d4ed8;
            text-decoration: none;
            font-weight: bold;
        }

        .back-btn:hover {
            color: #0d62d2;
        }

        .container {
            max-width: 1200px;
            margin: 80px auto 50px;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .stats-section {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: linear-gradient(135deg, #1d4ed8 0%, #0d62d2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            min-width: 200px;
            margin: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .stat-card p {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }

        .stat-card.revenue {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #1d4ed8;
            color: white;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .view-btn {
            display: inline-block;
            padding: 6px 12px;
            background-color: #1d4ed8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .view-btn:hover {
            background-color: #0d62d2;
        }

        .no-orders {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<div class="container">
    <h2>Orders Management</h2>

    <!-- Statistics Section -->
    <div class="stats-section">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?php echo number_format($total_users); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?php echo number_format($total_orders); ?></p>
        </div>
        <div class="stat-card revenue">
            <h3>Total Revenue</h3>
            <p>$<?php echo number_format($total_revenue, 2); ?></p>
        </div>
    </div>

    <!-- Orders Table -->
    <h3 style="margin-top: 30px; margin-bottom: 15px;">Order History</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Order Date</th>
                <th>Receipt Number</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['user_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($order['user_email'] ?? 'N/A'); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo htmlspecialchars($order['receipt_number'] ?? 'N/A'); ?></td>
                        <td style="text-align: right;">$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td style="text-align: center;">
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="view-btn">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-orders">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
