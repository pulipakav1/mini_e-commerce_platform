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

// Get search query
$search_term = isset($_GET['q']) ? trim($_GET['q']) : "";

// Search products if query provided
$search_results = [];
if (!empty($search_term)) {
    $search_param = "%" . $search_term . "%";
    $search_stmt = $conn->prepare("SELECT DISTINCT p.* FROM products p WHERE p.product_name LIKE ? OR p.product_description LIKE ?");
    $search_stmt->bind_param("ss", $search_param, $search_param);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
    while ($row = $search_result->fetch_assoc()) {
        $search_results[] = $row;
    }
    $search_stmt->close();
}

// Fetch categories for navigation
$category_query = $conn->query("SELECT DISTINCT category_id, category_name FROM category ORDER BY category_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Results - Flower Shop</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #ffffff; }

.top-bar {
    width: 100%;
    background: #fff;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 999;
}

.logo-text { font-size: 16px; font-weight: bold; color: #1d4ed8; }

.search-box {
    display: flex;
    align-items: center;
    background: #f1f3f5;
    border-radius: 8px;
    padding: 4px 8px;
    width: 400px;
}

.search-box form {
    display: flex;
    width: 100%;
    align-items: center;
}

.search-box input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 13px;
    width: 100%;
    padding: 4px 6px;
}

.search-icon {
    font-size: 14px;
    margin-left: 4px;
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    padding: 4px 8px;
}

.top-right {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-right: 50px;
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

.order-icon {
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    color: #1d4ed8;
    padding: 6px 8px;
    border-radius: 6px;
    transition: 0.3s;
}

.order-icon:hover {
    background: #e8efff;
}

.user-name {
    font-size: 13px;
    font-weight: bold;
    color: #333;
    position: relative;
    cursor: pointer;
}

.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}

.search-header {
    margin-bottom: 30px;
}

.search-header h2 {
    color: #333;
    margin-bottom: 10px;
}

.search-header p {
    color: #666;
}

.products-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.product-card {
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    transition: 0.3s;
}

.product-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.product-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 10px;
}

.product-name {
    font-size: 14px;
    font-weight: bold;
    margin: 10px 0 5px 0;
}

.product-price {
    color: #1d4ed8;
    font-weight: bold;
    margin-bottom: 10px;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #666;
}

.bottom-menu {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    display: flex;
    justify-content: space-around;
    width: 90%;
    max-width: 500px;
    padding: 12px 0;
    border-radius: 30px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    z-index: 1000;
}

.bottom-menu a {
    text-decoration: none;
    color: #888;
    font-size: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: 0.3s;
}

.bottom-menu a.active {
    color: #1d4ed8;
}

.category-link {
    display: inline-block;
    padding: 12px 24px;
    background: #1d4ed8;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin: 10px 5px;
}
</style>
</head>
<body>

<div class="top-bar">
    <div class="logo-text">Team Toronto</div>

    <div class="search-box">
        <form method="GET" action="search.php">
            <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit" class="search-icon">Search</button>
        </form>
    </div>

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

<div class="container">
    <div class="search-header">
        <h2>Search Results</h2>
        <?php if (!empty($search_term)): ?>
            <p>Searching for: "<strong><?php echo htmlspecialchars($search_term); ?></strong>" - Found <?php echo count($search_results); ?> result(s)</p>
        <?php else: ?>
            <p>Enter a search term above to find products</p>
        <?php endif; ?>
    </div>

    <?php if (empty($search_term)): ?>
        <div class="no-results">
            <p>Please enter a search term to find products.</p>
            <div style="margin-top: 20px;">
                <a href="home.php" class="category-link">Back to Home</a>
            </div>
        </div>
    <?php elseif (count($search_results) == 0): ?>
        <div class="no-results">
            <p>No products found matching "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</p>
            <p>Try searching with different keywords or browse our categories:</p>
            <div style="margin-top: 20px;">
                <?php
                if ($category_query && $category_query->num_rows > 0) {
                    while($category = $category_query->fetch_assoc()) {
                        $category_links = [
                            'Home & Living' => 'home_living.php',
                            'Cups & Bottles' => 'cups_bottles.php',
                            'Style Accessories' => 'style_accessories.php',
                            'Tulip Collection' => 'tulip_collection.php',
                            'Indoor Plants' => 'indoor_plants.php'
                        ];
                        $link = $category_links[$category['category_name']] ?? '#';
                        echo '<a href="'.htmlspecialchars($link).'" class="category-link">'.htmlspecialchars($category['category_name']).'</a>';
                    }
                }
                ?>
            </div>
        </div>
    <?php else: ?>
        <div class="products-container">
            <?php
            foreach ($search_results as $product) {
                // Get product image
                $img_stmt = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
                $img_stmt->bind_param("i", $product['product_id']);
                $img_stmt->execute();
                $img_result = $img_stmt->get_result();
                $img_data = $img_result->fetch_assoc();
                $img = $img_data['file_path'] ?? 'images/placeholder.png';
                $img_stmt->close();

                // Get category link
                $category_links = [
                    1 => 'home_living.php',
                    2 => 'cups_bottles.php',
                    3 => 'style_accessories.php',
                    4 => 'tulip_collection.php',
                    5 => 'indoor_plants.php'
                ];
                $category_link = $category_links[$product['category_id']] ?? 'home.php';

                echo '<div class="product-card">';
                echo '<img src="'.htmlspecialchars($img).'" alt="'.htmlspecialchars($product['product_name']).'">';
                echo '<div class="product-name">'.htmlspecialchars($product['product_name']).'</div>';
                echo '<div class="product-price">$'.number_format($product['cost'], 2).'</div>';
                
                if ($product['quantity'] > 0) {
                    echo '<div style="margin-top:10px;">';
                    echo '<label style="font-size:12px; color:#666; display:block; margin-bottom:5px;">Quantity:</label>';
                    echo '<input type="number" name="qty_'.$product['product_id'].'" id="qty_'.$product['product_id'].'" value="1" min="1" max="'.$product['quantity'].'" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; margin-bottom:8px;">';
                    echo '</div>';
                    
                    echo '<form method="POST" action="add_to_cart.php" style="margin-bottom:5px;">';
                    echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';
                    echo '<input type="hidden" name="quantity" id="cart_qty_'.$product['product_id'].'" value="1">';
                    echo '<input type="hidden" name="redirect" value="search.php?q='.urlencode($search_term).'">';
                    echo '<button type="submit" onclick="document.getElementById(\'cart_qty_'.$product['product_id'].'\').value = document.getElementById(\'qty_'.$product['product_id'].'\').value;" style="width:100%; padding:8px; background:#1d4ed8; color:white; border:none; border-radius:6px; cursor:pointer; margin-bottom:5px;">Add to Cart</button>';
                    echo '</form>';
                    
                    echo '<form method="POST" action="buy_now.php">';
                    echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';
                    echo '<input type="hidden" name="quantity" id="buy_qty_'.$product['product_id'].'" value="1">';
                    echo '<button type="submit" onclick="document.getElementById(\'buy_qty_'.$product['product_id'].'\').value = document.getElementById(\'qty_'.$product['product_id'].'\').value;" style="width:100%; padding:8px; background:#10b981; color:white; border:none; border-radius:6px; cursor:pointer;">Buy Now</button>';
                    echo '</form>';
                } else {
                    echo '<div style="margin-top:10px; padding:8px; background:#ccc; color:#666; text-align:center; border-radius:6px;">Out of Stock</div>';
                }
                
                echo '</div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>

<div class="bottom-menu">
    <a href="home.php"><span>Home</span></a>
    <a href="profile.php"><span>Profile</span></a>
</div>

</body>
</html>

