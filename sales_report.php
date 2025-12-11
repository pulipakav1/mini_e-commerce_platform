<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Only owner can access sales report
if ($_SESSION['admin_role'] != 'owner') {
    header("Location: dashboard.php");
    exit();
}

// Get date range filters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

// Fetch sales statistics
$total_sales = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['count'];
$avg_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;

// Fetch daily sales
$daily_sales_query = "SELECT DATE(order_date) as sale_date, 
                      COUNT(*) as order_count, 
                      SUM(total_amount) as daily_total 
                      FROM orders 
                      WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' 
                      GROUP BY DATE(order_date) 
                      ORDER BY sale_date DESC";
$daily_sales_result = $conn->query($daily_sales_query);

// Fetch top products
$top_products_query = "SELECT p.product_name, 
                       SUM(oi.quantity) as total_quantity, 
                       SUM(oi.quantity * oi.unit_price) as total_revenue,
                       COUNT(DISTINCT oi.order_id) as order_count
                       FROM order_items oi
                       JOIN products p ON oi.product_id = p.product_id
                       JOIN orders o ON oi.order_id = o.order_id
                       WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'
                       GROUP BY p.product_id, p.product_name
                       ORDER BY total_revenue DESC
                       LIMIT 10";
$top_products_result = $conn->query($top_products_query);

// Fetch sales by payment method
$payment_methods_query = "SELECT payment_method, 
                          COUNT(*) as count, 
                          SUM(total_amount) as total 
                          FROM payment 
                          JOIN orders ON payment.order_id = orders.order_id
                          WHERE DATE(orders.order_date) BETWEEN '$start_date' AND '$end_date'
                          GROUP BY payment_method";
$payment_methods_result = $conn->query($payment_methods_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
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

        .filter-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .filter-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-btn {
            padding: 8px 20px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .filter-btn:hover {
            background: #0d62d2;
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

        .stat-card.orders {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .section {
            margin-bottom: 40px;
        }

        .section h3 {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<a href="reports.php" class="back-btn">← Back to Reports</a>

<div class="container">
    <h2>Sales Report</h2>

    <!-- Date Filter -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Start Date:</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
            </div>
            <div class="filter-group">
                <label>End Date:</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
            </div>
            <div class="filter-group">
                <button type="submit" class="filter-btn">Filter</button>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-section">
        <div class="stat-card revenue">
            <h3>Total Sales</h3>
            <p>$<?php echo number_format($total_sales, 2); ?></p>
        </div>
        <div class="stat-card orders">
            <h3>Total Orders</h3>
            <p><?php echo number_format($total_orders); ?></p>
        </div>
        <div class="stat-card">
            <h3>Average Order Value</h3>
            <p>$<?php echo number_format($avg_order_value, 2); ?></p>
        </div>
    </div>

    <!-- Daily Sales -->
    <div class="section">
        <h3>Daily Sales Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daily_sales_result && $daily_sales_result->num_rows > 0): ?>
                    <?php while ($row = $daily_sales_result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?php echo date('M d, Y', strtotime($row['sale_date'])); ?></td>
                            <td class="text-center"><?php echo $row['order_count']; ?></td>
                            <td class="text-right">$<?php echo number_format($row['daily_total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No sales data for this period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Products -->
    <div class="section">
        <h3>Top 10 Products by Revenue</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity Sold</th>
                    <th>Orders</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($top_products_result && $top_products_result->num_rows > 0): ?>
                    <?php while ($row = $top_products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td class="text-center"><?php echo $row['total_quantity']; ?></td>
                            <td class="text-center"><?php echo $row['order_count']; ?></td>
                            <td class="text-right">$<?php echo number_format($row['total_revenue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No product sales data for this period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Payment Methods -->
    <div class="section">
        <h3>Sales by Payment Method</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Number of Orders</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payment_methods_result && $payment_methods_result->num_rows > 0): ?>
                    <?php while ($row = $payment_methods_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(ucfirst($row['payment_method'])); ?></td>
                            <td class="text-center"><?php echo $row['count']; ?></td>
                            <td class="text-right">$<?php echo number_format($row['total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No payment data for this period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

