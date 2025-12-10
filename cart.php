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
    // Handle multiple cart items update using arrays
    if (isset($_POST['cart_id']) && is_array($_POST['cart_id']) && isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        $updated = 0;
        $errors = [];
        
        for ($i = 0; $i < count($_POST['cart_id']); $i++) {
            $cart_id = intval($_POST['cart_id'][$i]);
            $new_quantity = intval($_POST['quantity'][$i]);
            
            if ($new_quantity <= 0) {
                // Remove item if quantity is 0 or less
                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                $delete_stmt->bind_param("ii", $cart_id, $user_id);
                if ($delete_stmt->execute()) {
                    $delete_stmt->close();
                    $updated++;
                } else {
                    $errors[] = "Error removing item from cart";
                    $delete_stmt->close();
                }
            } else {
                // Verify cart item belongs to user
                $cart_stmt = $conn->prepare("SELECT product_id FROM cart WHERE cart_id = ? AND user_id = ?");
                $cart_stmt->bind_param("ii", $cart_id, $user_id);
                $cart_stmt->execute();
                $cart_result = $cart_stmt->get_result();
                
                if ($cart_result->num_rows > 0) {
                    $cart_item = $cart_result->fetch_assoc();
                    $product_stmt = $conn->prepare("SELECT quantity, product_name FROM products WHERE product_id = ?");
                    $product_stmt->bind_param("i", $cart_item['product_id']);
                    $product_stmt->execute();
                    $product_result = $product_stmt->get_result();
                    $product = $product_result->fetch_assoc();
                    
                    if ($new_quantity > $product['quantity']) {
                        $errors[] = $product['product_name'] . ": Only " . $product['quantity'] . " in stock";
                    } else {
                        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                        $update_stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
                        if ($update_stmt->execute()) {
                            $update_stmt->close();
                            $updated++;
                        } else {
                            $errors[] = "Error updating " . $product['product_name'];
                            $update_stmt->close();
                        }
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            $message = implode("; ", $errors);
        } else if ($updated > 0) {
            $message = "Cart updated successfully";
        }
    }
}

if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        $message = "Item removed";
    } else {
        $message = "Error removing item: " . $delete_stmt->error;
        $delete_stmt->close();
    }
}

$cart_stmt = $conn->prepare("
    SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.cost, p.quantity as stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
    ORDER BY c.cart_id DESC
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
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
    margin: 0; 
    padding: 0; 
    min-height: 100vh;
}
.container { 
    max-width: 1000px; 
    margin: 30px auto; 
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 30px; 
    border-radius: 20px; 
    box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
    border: 1px solid rgba(255,255,255,0.8);
}
h2 { 
    text-align: center; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px; 
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -1px;
}
.message { 
    padding: 14px 18px; 
    margin-bottom: 20px; 
    border-radius: 10px; 
    text-align: center; 
    font-weight: 500;
}
.success { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; border: 1px solid #c3e6cb; }
.error { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; border: 1px solid #f5c6cb; }
table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 25px; 
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
th, td { 
    padding: 16px; 
    text-align: left; 
    border-bottom: 1px solid #e5e7eb; 
    vertical-align: middle; 
}
th { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
    color: white; 
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
tr:hover {
    background: #f9fafb;
}
img { 
    max-width: 100px; 
    max-height: 100px; 
    object-fit: cover; 
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
input[type="number"] { 
    width: 70px; 
    padding: 8px 10px; 
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}
input[type="number"]:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.btn { 
    padding: 10px 18px; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer; 
    text-decoration: none; 
    display: inline-block; 
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 14px;
}
.btn-danger { 
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); 
    color: white; 
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}
.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}
.btn-primary { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
    color: white; 
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}
.btn-success { 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
    color: white; 
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}
.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}
.total-section { 
    text-align: right; 
    padding: 25px 30px; 
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); 
    border-radius: 12px; 
    margin-top: 25px; 
    border: 2px solid #e5e7eb;
}
.total-section h3 { 
    margin: 10px 0; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 28px;
    font-weight: 700;
}
.empty-cart { 
    text-align: center; 
    padding: 60px 40px; 
    color: #6b7280; 
    font-size: 18px;
}
.back-link { 
    margin-bottom: 20px; 
}
.back-link a { 
    color: #667eea; 
    text-decoration: none; 
    font-weight: 600;
    transition: color 0.2s;
}
.back-link a:hover {
    color: #764ba2;
}
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
                        <th>Image</th>
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
                        
                        // Get product image
                        $img_stmt = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
                        $img_stmt->bind_param("i", $item['product_id']);
                        $img_stmt->execute();
                        $img_result = $img_stmt->get_result();
                        $img_data = $img_result->fetch_assoc();
                        $product_img = $img_data['file_path'] ?? 'images/placeholder.png';
                        $img_stmt->close();
                    ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($product_img); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width:80px; height:80px; object-fit:cover; border-radius:8px;"></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>$<?php echo number_format($item['cost'], 2); ?></td>
                            <td>
                                <input type="hidden" name="cart_id[]" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity[]" value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['stock_quantity']; ?>" required>
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

