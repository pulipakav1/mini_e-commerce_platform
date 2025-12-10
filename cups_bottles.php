<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$name = $user['name'] ?? 'User';

$product_query = $conn->query("SELECT * FROM products WHERE category_id=2");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cups & Bottles - Flower Shop</title>
<style>
/* SAME STYLES AS home_living.php */
body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fff; }
.top-bar { width: 100%; background: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; }
.logo-text { font-size: 20px; font-weight: bold; color: #1d4ed8; }
.top-right { display: flex; align-items: center; gap: 10px; }
.order-icon { font-size: 22px; cursor: pointer; text-decoration: none; color: #1d4ed8; padding: 8px; border-radius: 50%; transition: 0.3s; }
.order-icon:hover { background: #e8efff; }
.user-name { font-size: 14px; font-weight: bold; color: #333; }
.products-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; padding: 20px; }
.product-card { border: 1px solid #eee; border-radius: 12px; padding: 10px; text-align: center; transition: 0.3s; }
.product-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.product-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 12px; }
.product-name { font-size: 14px; font-weight: bold; margin: 10px 0 5px 0; }
.product-price { color: #1d4ed8; font-weight: bold; }
.bottom-menu { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #fff; display: flex; justify-content: space-around; width: 90%; max-width: 500px; padding: 12px 0; border-radius: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); z-index: 1000; }
.bottom-menu a { text-decoration: none; color: #888; font-size: 18px; display: flex; flex-direction: column; align-items: center; transition: 0.3s; }
.bottom-menu a.active { color: #1d4ed8; }
.bottom-menu span { font-size: 11px; margin-top: 3px; }
</style>
</head>
<body>

<div class="top-bar">
    <div class="logo-text">Team Toronto</div>
    <div class="top-right">
        <a href="my_orders.php" class="order-icon" title="Order History">Orders</a>
        <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
    </div>
</div>

<div style="text-align:center; padding:20px;">
    <h2>Cups & Bottles</h2>
</div>

<div class="products-container">
<?php
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
        echo '<form method="POST" action="add_to_cart.php" style="margin-top:10px;">';
        echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';
        echo '<input type="hidden" name="quantity" value="1">';
        echo '<input type="hidden" name="redirect" value="cups_bottles.php">';
        echo '<button type="submit" style="width:100%; padding:8px; background:#1d4ed8; color:white; border:none; border-radius:6px; cursor:pointer;">Add to Cart</button>';
        echo '</form>';
    } else {
        echo '<div style="margin-top:10px; padding:8px; background:#ccc; color:#666; text-align:center; border-radius:6px;">Out of Stock</div>';
    }
    echo '</div>';
}
?>
</div>

<div class="bottom-menu">
    <a href="home.php"><span>Home</span></a>
    <a href="wishlist.php"><span>Wishlist</span></a>
    <a href="profile.php"><span>Profile</span></a>
</div>

</body>
</html>
