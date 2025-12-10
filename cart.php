<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = isset($_GET['message']) ? $_GET['message'] : "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    $cart_id = intval($_POST['cart_id']);
    $new_quantity = intval($_POST['quantity']);
    
    if ($new_quantity <= 0) {
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        $delete_stmt->execute();
        $message = "Item removed";
    } else {
        $cart_stmt = $conn->prepare("SELECT product_id FROM cart WHERE cart_id = ? AND user_id = ?");
        $cart_stmt->bind_param("ii", $cart_id, $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        
        if ($cart_result->num_rows > 0) {
            $cart_item = $cart_result->fetch_assoc();
            $product_stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
            $product_stmt->bind_param("i", $cart_item['product_id']);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $product = $product_result->fetch_assoc();
            
            if ($new_quantity > $product['quantity']) {
                $message = "Only " . $product['quantity'] . " in stock";
            } else {
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                $update_stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
                $update_stmt->execute();
                $message = "Updated";
            }
        }
    }
}

if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    $delete_stmt->execute();
    $message = "Item removed";
}

$cart_stmt = $conn->prepare("
    SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.cost, p.quantity as stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

$total_amount = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart</title>
<style>
body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 0; }
.container { max-width: 900px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #1d4ed8; margin-bottom: 20px; }
.message { padding: 10px; margin-bottom: 15px; border-radius: 6px; text-align: center; }
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #1d4ed8; color: white; }
input[type="number"] { width: 60px; padding: 5px; }
.btn { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
.btn-danger { background: #ef4444; color: white; }
.btn-primary { background: #1d4ed8; color: white; }
.btn-success { background: #10b981; color: white; }
.total-section { text-align: right; padding: 20px; background: #f9f9f9; border-radius: 8px; margin-top: 20px; }
.total-section h3 { margin: 10px 0; color: #1d4ed8; }
.empty-cart { text-align: center; padding: 40px; color: #666; }
.back-link { margin-bottom: 15px; }
.back-link a { color: #1d4ed8; text-decoration: none; }
</style>
</head>
<body>

<div class="container">
    <div class="back-link">
        <a href="home.php">← Back to Shopping</a>
    </div>
    
    <h2>Shopping Cart</h2>
    
            <?php if ($message != ""): ?>
        <div class="message <?php echo (strpos($message, 'Updated') !== false || strpos($message, 'Added') !== false || strpos($message, 'updated') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($cart_result->num_rows > 0): ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Stock Available</th>
                        <th>Subtotal</th>
                        <th>Action</th>
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
                            <td>
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" required>
                            </td>
                            <td><?php echo $item['stock_quantity']; ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger" onclick="return confirm('Remove this item?')">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
        </form>
        
        <div class="total-section">
            <h3>Total: $<?php echo number_format($total_amount, 2); ?></h3>
            <a href="checkout.php" class="btn btn-success" style="margin-top: 10px;">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <a href="home.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

