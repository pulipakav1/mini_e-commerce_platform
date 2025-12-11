<?php
session_start();
include "db.php";

// Start output buffering only if not already started
if (!ob_get_level()) {
    ob_start();
}

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = isset($_GET['message']) ? $_GET['message'] : "";

$user_stmt = $conn->prepare("SELECT address FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_address = $user['address'] ?? 'Not provided';

$cart_stmt = $conn->prepare("
    SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.cost, p.quantity as stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

if ($cart_result->num_rows == 0) {
    ob_end_clean();
    header("Location: cart.php?message=" . urlencode("Your cart is empty!"));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    // Refetch cart items to ensure we have fresh data
    $cart_check_stmt = $conn->prepare("
        SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.cost, p.quantity as stock_quantity
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?
    ");
    $cart_check_stmt->bind_param("i", $user_id);
    $cart_check_stmt->execute();
    $cart_check_result = $cart_check_stmt->get_result();
    
    if ($cart_check_result->num_rows == 0) {
        $message = "Your cart is empty!";
        $cart_check_stmt->close();
    } else {
        $valid = true;
        $errors = [];
        
        while ($item = $cart_check_result->fetch_assoc()) {
            if ($item['quantity'] > $item['stock_quantity']) {
                $valid = false;
                $errors[] = $item['product_name'] . " - Only " . $item['stock_quantity'] . " available";
            }
        }
        
        if (!$valid) {
            $message = implode(" ", $errors);
            $cart_check_stmt->close();
        } else {
            // Use transaction to ensure all operations succeed or fail together
            mysqli_begin_transaction($conn);
            
            try {
                $cart_check_result->data_seek(0);
                $total_amount = 0;
                while ($item = $cart_check_result->fetch_assoc()) {
                    $total_amount += $item['cost'] * $item['quantity'];
                }
            
            // Create order
            $order_stmt = $conn->prepare("INSERT INTO orders (user_id, address, total_amount) VALUES (?, ?, ?)");
            if (!$order_stmt) {
                throw new Exception("Failed to prepare order statement: " . $conn->error);
            }
            
            $order_stmt->bind_param("isd", $user_id, $user_address, $total_amount);
            
            if (!$order_stmt->execute()) {
                $error_msg = "Failed to create order: " . $order_stmt->error;
                $order_stmt->close();
                throw new Exception($error_msg);
            }
            
            $order_id = $order_stmt->insert_id;
            $order_stmt->close();
            
            // Verify order_id was set - CRITICAL CHECK
            if (empty($order_id) || $order_id == 0) {
                error_log("Checkout ERROR: insert_id is 0 or empty. User: $user_id, Total: $total_amount");
                
                // Fallback: Try to get the last inserted order for this user
                $fallback_stmt = $conn->prepare("SELECT order_id FROM orders WHERE user_id = ? ORDER BY order_id DESC LIMIT 1");
                if ($fallback_stmt) {
                    $fallback_stmt->bind_param("i", $user_id);
                    if ($fallback_stmt->execute()) {
                        $fallback_result = $fallback_stmt->get_result();
                        if ($fallback_result->num_rows > 0) {
                            $fallback_order = $fallback_result->fetch_assoc();
                            $order_id = $fallback_order['order_id'];
                            error_log("Checkout: Retrieved order_id from fallback query: $order_id");
                        }
                    }
                    $fallback_stmt->close();
                }
                
                // If still 0, throw exception
                if (empty($order_id) || $order_id == 0) {
                    throw new Exception("Order was created but insert_id is invalid (0). This usually means the INSERT failed or the table doesn't have AUTO_INCREMENT.");
                }
            }
            
            // Debug log
            error_log("Checkout: Order created successfully with ID: $order_id for user: $user_id");
            
            // Create order items and update inventory
            $cart_check_result->data_seek(0);
            while ($item = $cart_check_result->fetch_assoc()) {
                // Insert order item
                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['cost']);
                
                if (!$item_stmt->execute()) {
                    throw new Exception("Failed to create order item: " . $item_stmt->error);
                }
                $item_stmt->close();
                
                // Update product quantity
                $new_quantity = $item['stock_quantity'] - $item['quantity'];
                $update_stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE product_id = ?");
                $update_stmt->bind_param("ii", $new_quantity, $item['product_id']);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update product quantity: " . $update_stmt->error);
                }
                $update_stmt->close();
                
                // Update inventory table if record exists
                $inv_check = $conn->prepare("SELECT inventory_id FROM inventory WHERE product_id = ?");
                $inv_check->bind_param("i", $item['product_id']);
                $inv_check->execute();
                $inv_result = $inv_check->get_result();
                $inv_check->close();
                
                if ($inv_result->num_rows > 0) {
                    $update_inv = $conn->prepare("UPDATE inventory SET quantity = ?, last_updated = CURRENT_TIMESTAMP WHERE product_id = ?");
                    $update_inv->bind_param("ii", $new_quantity, $item['product_id']);
                    
                    if (!$update_inv->execute()) {
                        throw new Exception("Failed to update inventory: " . $update_inv->error);
                    }
                    $update_inv->close();
                } else {
                    // Create inventory record if it doesn't exist
                    $insert_inv = $conn->prepare("INSERT INTO inventory (product_id, quantity, last_updated) VALUES (?, ?, CURRENT_TIMESTAMP)");
                    $insert_inv->bind_param("ii", $item['product_id'], $new_quantity);
                    
                    if (!$insert_inv->execute()) {
                        throw new Exception("Failed to create inventory record: " . $insert_inv->error);
                    }
                    $insert_inv->close();
                }
            }
            
            // Create receipt
            $receipt_number = "REC-" . str_pad($order_id, 6, "0", STR_PAD_LEFT);
            $receipt_stmt = $conn->prepare("INSERT INTO receipts (order_id, receipt_number) VALUES (?, ?)");
            $receipt_stmt->bind_param("is", $order_id, $receipt_number);
            
            if (!$receipt_stmt->execute()) {
                throw new Exception("Failed to create receipt: " . $receipt_stmt->error);
            }
            $receipt_stmt->close();
            
            // Get payment method from form (default to Cash on Delivery)
            $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Cash on Delivery';
            if (empty($payment_method)) {
                $payment_method = 'Cash on Delivery';
            }
            
            // Create payment record
            $payment_stmt = $conn->prepare("INSERT INTO payment (order_id, payment_method, total_amount) VALUES (?, ?, ?)");
            $payment_stmt->bind_param("isd", $order_id, $payment_method, $total_amount);
            
            if (!$payment_stmt->execute()) {
                throw new Exception("Failed to create payment record: " . $payment_stmt->error);
            }
            $payment_stmt->close();
            
            // Clear cart
            $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart->bind_param("i", $user_id);
            
            if (!$clear_cart->execute()) {
                throw new Exception("Failed to clear cart: " . $clear_cart->error);
            }
            $clear_cart->close();
            
            // Commit transaction - all operations successful
            mysqli_commit($conn);
            
            $cart_check_stmt->close();
            
            // Verify order_id one more time before redirect
            if (empty($order_id) || $order_id == 0) {
                throw new Exception("Invalid order_id before redirect: $order_id. Order may not have been created.");
            }
            
            // Clean any output before redirect
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Debug: Log the redirect
            error_log("Checkout: Redirecting to order_confirmation.php with order_id=$order_id and receipt=$receipt_number");
            
            // Redirect to order confirmation - use absolute URL to ensure it works
            $redirect_url = "order_confirmation.php?order_id=" . intval($order_id) . "&receipt=" . urlencode($receipt_number);
            
            // Verify redirect URL is valid
            if (empty($redirect_url)) {
                throw new Exception("Redirect URL is empty");
            }
            
            // Check if headers already sent
            if (headers_sent($file, $line)) {
                error_log("ERROR: Headers already sent in $file on line $line");
                // If headers already sent, output JavaScript redirect instead
                echo "<script>window.location.href='" . htmlspecialchars($redirect_url, ENT_QUOTES) . "';</script>";
                echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($redirect_url, ENT_QUOTES) . "'></noscript>";
                exit();
            }
            
            // Perform redirect
            header("Location: " . $redirect_url);
            exit();
            
            } catch (Exception $e) {
                // Rollback transaction on any error
                mysqli_rollback($conn);
                $message = "Error processing order: " . $e->getMessage();
                error_log("Checkout Error: " . $e->getMessage()); // Log for debugging
                if (isset($cart_check_stmt)) {
                    $cart_check_stmt->close();
                }
            }
        }
    }
}

// Ensure cart result is available for display
if (!isset($cart_result) || $cart_result->num_rows == 0) {
    // This should not happen as we check earlier, but just in case
    ob_end_clean();
    header("Location: cart.php?message=" . urlencode("Your cart is empty!"));
    exit();
}

$cart_result->data_seek(0);
$total_amount = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
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
.container { max-width: 900px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #1d4ed8; margin-bottom: 20px; }
.message { padding: 10px; margin-bottom: 15px; border-radius: 6px; background: #f8d7da; color: #721c24; }
.checkout-section { margin-bottom: 30px; }
.checkout-section h3 { color: #1d4ed8; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #1d4ed8; color: white; }
.total-section { text-align: right; padding: 20px; background: #f9f9f9; border-radius: 8px; margin-top: 20px; }
.total-section h3 { margin: 10px 0; color: #1d4ed8; }
.btn { padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 16px; }
.btn-success { background: #10b981; color: white; }
.btn-primary { background: #1d4ed8; color: white; }
.address-box { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 10px 0; }
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
    <h2>Checkout</h2>
    
    <?php if ($message != ""): ?>
        <div class="message" style="background: <?php echo (strpos($message, 'Error') !== false || strpos($message, 'empty') !== false || strpos($message, 'Only') !== false) ? '#f8d7da' : '#d4edda'; ?>; color: <?php echo (strpos($message, 'Error') !== false || strpos($message, 'empty') !== false || strpos($message, 'Only') !== false) ? '#721c24' : '#155724'; ?>; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-section">
        <h3>Order Summary</h3>
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
                <?php while ($item = $cart_result->fetch_assoc()): 
                    $subtotal = $item['cost'] * $item['quantity'];
                    $total_amount += $subtotal;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td>$<?php echo number_format($item['cost'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="checkout-section">
        <h3>Address</h3>
        <div class="address-box">
            <?php echo nl2br(htmlspecialchars($user_address)); ?>
        </div>
    </div>
    
    <div class="total-section">
        <h3>Total: $<?php echo number_format($total_amount, 2); ?></h3>
        <form method="POST">
            <div class="checkout-section" style="margin-top: 20px; margin-bottom: 20px;">
                <h3>Payment Method</h3>
                <div class="payment-methods" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 15px; padding: 12px; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="payment_method" value="Cash on Delivery" checked style="margin-right: 8px; cursor: pointer;">
                        <strong>Cash on Delivery</strong>
                        <span style="color: #666; font-size: 14px; display: block; margin-top: 5px;">Pay when the order is delivered</span>
                    </label>
                    <label style="display: block; margin-bottom: 15px; padding: 12px; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="payment_method" value="Credit Card" style="margin-right: 8px; cursor: pointer;">
                        <strong>Credit Card</strong>
                        <span style="color: #666; font-size: 14px; display: block; margin-top: 5px;">Card payment processed on delivery</span>
                    </label>
                </div>
                <style>
                    .payment-methods label:hover {
                        border-color: #1d4ed8;
                        background-color: #f0f4ff;
                    }
                    .payment-methods input[type="radio"]:checked + strong {
                        color: #1d4ed8;
                    }
                </style>
            </div>
            <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
        </form>
        <div style="margin-top: 15px; text-align: center;">
            <a href="cart.php" class="btn btn-primary">Back to Cart</a>
            <button onclick="history.back();" class="btn btn-primary" style="margin-left: 10px;">← Back</button>
        </div>
    </div>
</div>
<!-- End Container -->

</div>
<!-- End Content Wrapper -->

</body>
</html>

