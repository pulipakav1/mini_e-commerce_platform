<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE user_id=?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$name = htmlspecialchars($user['name']);

$product_query = $conn->query("SELECT * FROM products WHERE category_id=2");
if (!$product_query) {
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cups & Bottles - Flower Shop</title>
<style>
/* SAME STYLES AS home_living.php */
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
    margin: 0; 
    padding: 0; 
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}
.top-bar { 
    width: 100%; 
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 15px 25px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
    position: sticky; 
    top: 0; 
    z-index: 999; 
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.logo-text { 
    font-size: 20px; 
    font-weight: 700; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.5px;
}
.top-right { display: flex; align-items: center; gap: 10px; }
.order-icon { 
    font-size: 14px; 
    cursor: pointer; 
    text-decoration: none; 
    color: #667eea; 
    padding: 8px 14px; 
    border-radius: 8px; 
    transition: all 0.3s ease; 
    font-weight: 500;
}
.order-icon:hover { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}
.user-name { 
    font-size: 13px; 
    font-weight: 600; 
    color: #333; 
    position: relative; 
    cursor: pointer; 
}

.user-dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #fff;
    min-width: 150px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1000;
    border-radius: 8px;
    margin-top: 5px;
    overflow: hidden;
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    font-size: 14px;
    transition: background 0.2s;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.user-dropdown:hover .dropdown-content {
    display: block;
}
.products-container { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
    gap: 25px; 
    padding: 30px 20px; 
}
.product-card { 
    background: white;
    border: 1px solid #e5e7eb; 
    border-radius: 16px; 
    padding: 15px; 
    text-align: center; 
    transition: all 0.3s ease; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.product-card:hover { 
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15); 
    border-color: #667eea;
}
.product-card img { 
    width: 100%; 
    height: 180px; 
    object-fit: cover; 
    border-radius: 12px; 
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.product-name { 
    font-size: 15px; 
    font-weight: 600; 
    margin: 12px 0 8px 0; 
    color: #1f2937;
}
.product-price { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700; 
    font-size: 18px;
}
.bottom-menu { 
    position: fixed; 
    bottom: 25px; 
    left: 50%; 
    transform: translateX(-50%); 
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    display: flex; 
    justify-content: space-around; 
    width: 90%; 
    max-width: 500px; 
    padding: 14px 0; 
    border-radius: 25px; 
    box-shadow: 0 8px 32px rgba(0,0,0,0.12); 
    z-index: 1000; 
    border: 1px solid rgba(255,255,255,0.8);
}
.bottom-menu a { 
    text-decoration: none; 
    color: #9ca3af; 
    font-size: 18px; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    transition: all 0.3s ease; 
    padding: 8px 20px;
    border-radius: 15px;
}
.bottom-menu a:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    transform: translateY(-3px);
}
.bottom-menu a.active { 
    color: #667eea; 
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
    font-weight: 600;
}
.bottom-menu span { font-size: 11px; margin-top: 3px; }
</style>
</head>
<body>

<div class="top-bar">
    <div class="logo-text">Team Toronto</div>
    <div class="top-right">
        <a href="cart.php" class="order-icon" title="Cart" style="position:relative;">
            Cart
            <?php
            $cart_count = 0;
            $cart_count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
            if ($cart_count_stmt) {
                $cart_count_stmt->bind_param("i", $user_id);
                $cart_count_stmt->execute();
                $cart_count_result = $cart_count_stmt->get_result();
                if ($cart_count_result) {
                    $cart_row = $cart_count_result->fetch_assoc();
                    $cart_count = $cart_row['total'] ?? 0;
                }
            }
            if ($cart_count > 0) {
                echo '<span style="position:absolute; top:-5px; right:-5px; background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:11px; display:flex; align-items:center; justify-content:center;">'.htmlspecialchars($cart_count).'</span>';
            }
            ?>
        </a>
        <a href="my_orders.php" class="order-icon" title="Order History">Orders</a>
        <div class="user-dropdown">
            <div class="user-name"><?php echo $name; ?> ▼</div>
            <div class="dropdown-content">
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<div style="text-align:center; padding:30px 20px;">
    <h2 style="font-size: 36px; font-weight: 700; color: #1f2937; margin-bottom: 10px; letter-spacing: -1px;">Cups & Bottles</h2>
    <p style="color: #6b7280; font-size: 16px;">Discover our collection</p>
</div>

<div class="products-container">
<?php
if ($product_query->num_rows > 0) {
    while($product = $product_query->fetch_assoc()) {
    $img_stmt = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
    $img_stmt->bind_param("i", $product['product_id']);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $img_data = $img_result->fetch_assoc();
    $img = $img_data['file_path'] ?? 'images/placeholder.png';

    echo '<div class="product-card">';
    echo '<img src="'.htmlspecialchars($img).'" alt="'.htmlspecialchars($product['product_name']).'">';
    echo '<div class="product-name">'.htmlspecialchars($product['product_name']).'</div>';
    echo '<div class="product-price">$'.number_format($product['cost'],2).'</div>';
    if ($product['quantity'] > 0) {
        echo '<div style="margin-top:10px;">';
        echo '<label style="font-size:12px; color:#6b7280; display:block; margin-bottom:6px; font-weight:500;">Quantity:</label>';
        echo '<input type="number" name="qty_'.$product['product_id'].'" id="qty_'.$product['product_id'].'" value="1" min="1" max="'.$product['quantity'].'" style="width:100%; padding:8px; border:2px solid #e5e7eb; border-radius:8px; margin-bottom:10px; font-size:14px; transition:all 0.2s;" onfocus="this.style.borderColor=\'#667eea\'; this.style.boxShadow=\'0 0 0 3px rgba(102, 126, 234, 0.1)\'" onblur="this.style.borderColor=\'#e5e7eb\'; this.style.boxShadow=\'none\'">';
        echo '</div>';
        
        echo '<form method="POST" action="add_to_cart.php" style="margin-bottom:5px;">';
        echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';
        echo '<input type="hidden" name="quantity" id="cart_qty_'.$product['product_id'].'" value="1">';
        echo '<input type="hidden" name="redirect" value="cups_bottles.php">';
        echo '<button type="submit" onclick="document.getElementById(\'cart_qty_'.$product['product_id'].'\').value = document.getElementById(\'qty_'.$product['product_id'].'\').value;" style="width:100%; padding:10px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; border:none; border-radius:8px; cursor:pointer; margin-bottom:8px; font-weight:600; font-size:14px; transition:all 0.3s ease; box-shadow:0 2px 8px rgba(102, 126, 234, 0.3);" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(102, 126, 234, 0.4)\'" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(102, 126, 234, 0.3)\'">Add to Cart</button>';
        echo '</form>';
        
        echo '<form method="POST" action="buy_now.php">';
        echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';
        echo '<input type="hidden" name="quantity" id="buy_qty_'.$product['product_id'].'" value="1">';
        echo '<button type="submit" onclick="document.getElementById(\'buy_qty_'.$product['product_id'].'\').value = document.getElementById(\'qty_'.$product['product_id'].'\').value;" style="width:100%; padding:10px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px; transition:all 0.3s ease; box-shadow:0 2px 8px rgba(16, 185, 129, 0.3);" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(16, 185, 129, 0.4)\'" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(16, 185, 129, 0.3)\'">Buy Now</button>';
        echo '</form>';
    } else {
        echo '<div style="margin-top:10px; padding:8px; background:#ccc; color:#666; text-align:center; border-radius:6px;">Out of Stock</div>';
    }
    echo '</div>';
    }
} else {
    echo '<p style="text-align:center; width:100%; grid-column:1/-1;">No products in this category.</p>';
}
?>
</div>

<div class="bottom-menu">
    <a href="home.php"><span>Home</span></a>
    <a href="profile.php"><span>Profile</span></a>
</div>

</body>
</html>
