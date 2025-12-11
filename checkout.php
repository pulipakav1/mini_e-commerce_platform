<?php
ob_start(); // Start output buffering
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

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
    $cart_result->data_seek(0);
    $valid = true;
    $errors = [];
    
    while ($item = $cart_result->fetch_assoc()) {
        if ($item['quantity'] > $item['stock_quantity']) {
            $valid = false;
            $errors[] = $item['product_name'] . " - Only " . $item['stock_quantity'] . " available";
        }
    }
    
    if (!$valid) {
        $message = implode(" ", $errors);
    } else {
        // Use transaction to ensure all operations succeed or fail together
        mysqli_begin_transaction($conn);
        
        try {
            $cart_result->data_seek(0);
            $total_amount = 0;
            while ($item = $cart_result->fetch_assoc()) {
                $total_amount += $item['cost'] * $item['quantity'];
            }
            
            // Create order
            $order_stmt = $conn->prepare("INSERT INTO orders (user_id, address, total_amount) VALUES (?, ?, ?)");
            $order_stmt->bind_param("isd", $user_id, $user_address, $total_amount);
            
            if (!$order_stmt->execute()) {
                throw new Exception("Failed to create order: " . $order_stmt->error);
            }
            
            $order_id = $order_stmt->insert_id;
            $order_stmt->close();
            
            // Create order items and update inventory
            $cart_result->data_seek(0);
            while ($item = $cart_result->fetch_assoc()) {
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
            
            // Clean any output before redirect
            ob_end_clean();
            
            // Redirect to order confirmation
            $redirect_url = "order_confirmation.php?order_id=" . intval($order_id) . "&receipt=" . urlencode($receipt_number);
            header("Location: " . $redirect_url);
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on any error
            mysqli_rollback($conn);
            $message = "Error processing order: " . $e->getMessage();
        }
    }
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
body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 0; }
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

<div class="container">
    <h2>Checkout</h2>
    
    <?php if ($message != ""): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
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
    
    <div class="checkout-section">
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
    
    <div class="total-section">
        <h3>Total: $<?php echo number_format($total_amount, 2); ?></h3>
        <form method="POST">
            <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
        </form>
        <div style="margin-top: 15px; text-align: center;">
            <a href="cart.php" class="btn btn-primary">Back to Cart</a>
            <button onclick="history.back();" class="btn btn-primary" style="margin-left: 10px;">← Back</button>
        </div>
    </div>
</div>

</body>
</html>

