<?php
// Start output buffering first to prevent any output issues
if (!ob_get_level()) {
    ob_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session first
session_start();

// Include database
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$receipt_number = isset($_GET['receipt']) ? htmlspecialchars($_GET['receipt']) : "";

// Validate order ID
if ($order_id == 0) {
    ob_end_clean();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error - No Order ID</title>
        <style>
            body { font-family: Arial; padding: 20px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 50px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc2626; }
            a { color: #1d4ed8; text-decoration: none; padding: 10px 20px; background: #1d4ed8; color: white; border-radius: 6px; display: inline-block; margin-top: 15px; }
            a:hover { background: #1e40af; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>Error</h1>
            <p>No order ID provided. Please select an order to view.</p>
            <a href='my_orders.php'>View All Orders</a>
            <a href='home.php' style="margin-left: 10px;">Continue Shopping</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Fetch order with better error handling
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
if (!$order_stmt) {
    error_log("Order Confirmation Error: " . $conn->error);
    header("Location: my_orders.php?error=db_prepare");
    exit();
}

$order_stmt->bind_param("ii", $order_id, $user_id);
if (!$order_stmt->execute()) {
    error_log("Order Confirmation Error: " . $order_stmt->error);
    $order_stmt->close();
    header("Location: my_orders.php?error=db_execute");
    exit();
}

$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    $order_stmt->close();
    error_log("Order Confirmation: Order ID $order_id not found for user $user_id");
    // Clean output buffer and show error message instead of blank page
    ob_end_clean();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Order Not Found</title>
        <style>
            body { font-family: Arial; padding: 20px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 50px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc2626; }
            a { color: #1d4ed8; text-decoration: none; padding: 10px 20px; background: #1d4ed8; color: white; border-radius: 6px; display: inline-block; margin-top: 15px; }
            a:hover { background: #1e40af; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>Order Not Found</h1>
            <p>We couldn't find the order you're looking for.</p>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
            <a href='my_orders.php'>View All Orders</a>
            <a href='home.php' style="margin-left: 10px;">Continue Shopping</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// If receipt number not provided in URL, fetch from database
if (empty($receipt_number)) {
    $receipt_fetch = $conn->prepare("SELECT receipt_number FROM receipts WHERE order_id = ?");
    if ($receipt_fetch) {
        $receipt_fetch->bind_param("i", $order_id);
        $receipt_fetch->execute();
        $receipt_fetch_result = $receipt_fetch->get_result();
        if ($receipt_fetch_result->num_rows > 0) {
            $receipt_data = $receipt_fetch_result->fetch_assoc();
            $receipt_number = $receipt_data['receipt_number'];
        } else {
            // Generate receipt number if not found (fallback)
            $receipt_number = "REC-" . str_pad($order_id, 6, "0", STR_PAD_LEFT);
        }
        $receipt_fetch->close();
    } else {
        // Fallback if query fails
        $receipt_number = "REC-" . str_pad($order_id, 6, "0", STR_PAD_LEFT);
    }
}

// Get payment method
$payment_stmt = $conn->prepare("SELECT payment_method FROM payment WHERE order_id = ?");
if (!$payment_stmt) {
    $payment_method = 'Cash on Delivery';
} else {
    $payment_stmt->bind_param("i", $order_id);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payment_data = $payment_result->fetch_assoc();
    $payment_method = $payment_data['payment_method'] ?? 'Cash on Delivery';
    $payment_stmt->close();
}

// Fetch order items with better error handling
$items_stmt = $conn->prepare("
    SELECT oi.*, p.product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
if (!$items_stmt) {
    error_log("Order Confirmation Items Error: " . $conn->error);
    $items_result = null;
} else {
    $items_stmt->bind_param("i", $order_id);
    if (!$items_stmt->execute()) {
        error_log("Order Confirmation Items Execute Error: " . $items_stmt->error);
        $items_result = null;
        $items_stmt->close();
    } else {
        $items_result = $items_stmt->get_result();
        // Don't close here, we'll close after using it
    }
}

// Ensure no output before HTML - clean buffer if needed
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start(); // Start fresh buffer for HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation</title>
<script>
// Debug: Log that page loaded
console.log('Order Confirmation Page Loaded');
console.log('Order ID: <?php echo $order_id; ?>');
console.log('Receipt: <?php echo htmlspecialchars($receipt_number, ENT_QUOTES); ?>');
</script>
<style>
body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 0; min-height: 100vh; }

.hero-image-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    z-index: 0;
    overflow: hidden;
}

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.3) 100%);
    z-index: 1;
}

.content-wrapper {
    position: relative;
    z-index: 10;
}
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

<!-- Full Page Hero Image -->
<?php
$tulip_image = "images/tulip-field.jpg";
?>
<div class="hero-image-container">
    <?php if (file_exists($tulip_image) || file_exists("images/tulip-field.jpg")): ?>
        <img src="images/tulip-field.jpg" alt="Tulip Field" class="hero-image" onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)';">
    <?php else: ?>
        <div style="width:100%; height:100%; background:linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">

<div class="container">
    <!-- DEBUG: Container started -->
    <div class="success-message">
        <h2>Order Placed Successfully!</h2>
        <p>Your order has been received and processed.</p>
    </div>
    
    <div class="receipt-box">
        <h3>Receipt #<?php echo htmlspecialchars($receipt_number); ?></h3>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
        <p><strong>Order Date:</strong> <?php echo isset($order['order_date']) ? date('F j, Y g:i A', strtotime($order['order_date'])) : 'N/A'; ?></p>
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
            if ($items_result && $items_result->num_rows > 0) {
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
                    <td>$<?php echo number_format($grand_total > 0 ? $grand_total : $order['total_amount'], 2); ?></td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">No items found for this order. Order ID: <?php echo htmlspecialchars($order_id); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Total:</td>
                    <td>$<?php echo number_format(isset($order['total_amount']) ? $order['total_amount'] : 0, 2); ?></td>
                </tr>
            <?php } 
            // Close items statement if it was opened
            if (isset($items_stmt) && $items_stmt) {
                $items_stmt->close();
            }
            ?>
        </tbody>
    </table>
    
    <div class="receipt-box">
        <h3>Address</h3>
        <p><?php echo nl2br(htmlspecialchars(isset($order['address']) ? $order['address'] : 'Not provided')); ?></p>
        
        <h3>Payment Method</h3>
        <p><?php echo htmlspecialchars($payment_method); ?></p>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="my_orders.php" class="btn">View All Orders</a>
        <a href="home.php" class="btn">Continue Shopping</a>
        <button onclick="history.back();" class="btn" style="background: #6b7280;">← Back</button>
    </div>
</div>

</div>
<!-- End Content Wrapper -->

</body>
</html>
<?php
// Flush output buffer
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>

