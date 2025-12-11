<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Only owner can access order details
if ($_SESSION['admin_role'] != 'owner') {
    header("Location: dashboard.php");
    exit();
}

$order_id = intval($_GET['id'] ?? 0);

if ($order_id == 0) {
    header("Location: orders.php");
    exit();
}

// Fetch order details
$order_stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.phone_number,
                              r.receipt_number, r.receipt_date,
                              p.payment_method, p.payment_date
                              FROM orders o
                              LEFT JOIN users u ON o.user_id = u.user_id
                              LEFT JOIN receipts r ON o.order_id = r.order_id
                              LEFT JOIN payment p ON o.order_id = p.order_id
                              WHERE o.order_id = ?");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.product_name, img.file_path
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.product_id
                              LEFT JOIN images img ON p.product_id = img.product_id
                              WHERE oi.order_id = ?
                              GROUP BY oi.order_item_id, img.image_id
                              ORDER BY oi.order_item_id");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
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
            max-width: 900px;
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

        .info-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            color: #333;
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

        .product-img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 6px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            padding: 15px;
            background: #e8efff;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<a href="orders.php" class="back-btn">← Back to Orders</a>

<div class="container">
    <h2>Order Details #<?php echo $order['order_id']; ?></h2>

    <!-- Order Information -->
    <div class="info-section">
        <h3 style="margin-top: 0;">Order Information</h3>
        <div class="info-row">
            <span class="info-label">Order ID:</span>
            <span class="info-value">#<?php echo $order['order_id']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Receipt Number:</span>
            <span class="info-value"><?php echo htmlspecialchars($order['receipt_number'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value"><?php echo date('F d, Y H:i', strtotime($order['order_date'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Method:</span>
            <span class="info-value"><?php echo htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')); ?></span>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="info-section">
        <h3 style="margin-top: 0;">Customer Information</h3>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value"><?php echo htmlspecialchars($order['user_name'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value"><?php echo htmlspecialchars($order['user_email'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value"><?php echo htmlspecialchars($order['phone_number'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Shipping Address:</span>
            <span class="info-value"><?php echo nl2br(htmlspecialchars($order['address'])); ?></span>
        </div>
    </div>

    <!-- Order Items -->
    <h3>Order Items</h3>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items_result && $items_result->num_rows > 0): ?>
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center">
                            <?php if (!empty($item['file_path'])): ?>
                                <img src="<?php echo htmlspecialchars($item['file_path']); ?>" alt="Product" class="product-img">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right">$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="text-right">$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-section">
        Total Amount: $<?php echo number_format($order['total_amount'], 2); ?>
    </div>
</div>

</body>
</html>

