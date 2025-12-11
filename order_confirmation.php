<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$receipt_number = isset($_GET['receipt']) ? htmlspecialchars($_GET['receipt']) : "";

if ($order_id == 0) {
    header("Location: my_orders.php");
    exit();
}

$order_stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    header("Location: my_orders.php");
    exit();
}

$order = $order_result->fetch_assoc();

$items_stmt = $conn->prepare("
    SELECT oi.*, p.product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation</title>
<style>
body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 0; }
.container { max-width: 800px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
.success-message { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px; }
.success-message h2 { margin: 0 0 10px 0; color: #155724; }
.receipt-box { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.receipt-box h3 { color: #1d4ed8; margin-top: 0; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #1d4ed8; color: white; }
.total-row { font-weight: bold; font-size: 18px; }
.btn { padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; background: #1d4ed8; color: white; margin-top: 20px; }
</style>
</head>
<body>

<div class="container">
    <div class="success-message">
        <h2>Order Placed</h2>
        <p>Your order has been received</p>
    </div>
    
    <div class="receipt-box">
        <h3>Receipt #<?php echo htmlspecialchars($receipt_number); ?></h3>
        <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
        <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
    </div>
    
    <h3>Order Details</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while ($item = $items_result->fetch_assoc()): 
                $subtotal = $item['unit_price'] * $item['quantity'];
                $grand_total += $subtotal;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
            <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Total:</td>
                <td>$<?php echo number_format($grand_total, 2); ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="receipt-box">
        <h3>Shipping Address</h3>
        <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
        
        <h3>Billing Address</h3>
        <p><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
        
        <h3>Payment Method</h3>
        <p>Cash on Delivery</p>
    </div>
    
    <a href="my_orders.php" class="btn">View All Orders</a>
    <a href="home.php" class="btn">Continue Shopping</a>
</div>

</body>
</html>

