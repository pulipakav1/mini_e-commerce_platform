<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Fetch user info
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
    header("Location: auth.php");
    exit();
}

$name = htmlspecialchars($user['name']);

// Determine action: home (default), education, or search
$action = $_GET['action'] ?? 'home';
$search_term = isset($_GET['q']) ? trim($_GET['q']) : "";

// Fetch categories (needed for home and search)
$category_query = $conn->query("SELECT DISTINCT category_id, category_name FROM category ORDER BY category_id");
if (!$category_query) {
    die("Database error: " . $conn->error);
}

// Fetch education content (for education action)
$education_content = null;
if ($action == 'education') {
    $edu_stmt = $conn->prepare("SELECT education_id, education_section, descriptions FROM flower_education ORDER BY education_id");
    $edu_stmt->execute();
    $edu_result = $edu_stmt->get_result();
    $education_content = [];
    while ($row = $edu_result->fetch_assoc()) {
        $education_content[] = $row;
    }
}

// Search products (for search action)
$search_results = [];
if ($action == 'search' && !empty($search_term)) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php 
if ($action == 'education') echo 'Tulip Education';
elseif ($action == 'search') echo 'Search Results';
else echo 'Home';
?> - Flower Shop</title>
<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: #f5f5f5;
        min-height: 100vh;
    }

    <?php if ($action == 'home'): ?>
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

    .categories-container {
        position: relative;
        z-index: 10;
    }
    <?php else: ?>
    .content-wrapper {
        position: relative;
    }
    <?php endif; ?>

    .top-bar {
        width: 100%;
        background: #ffffff;
        padding: 12px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1001;
        border-bottom: 1px solid #e5e7eb;
    }

    .logo-text {
        font-size: 24px;
        font-weight: 600;
        color: #1d4ed8;
        letter-spacing: -0.5px;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 15px;
        width: 400px;
        transition: all 0.2s ease;
    }

    .search-box:focus-within {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
    }

    .search-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 14px;
        width: 100%;
        padding: 4px 8px;
        color: #333;
    }

    .search-box input::placeholder {
        color: #9ca3af;
    }

    .search-icon {
        font-size: 14px;
        margin-left: 4px;
        cursor: pointer;
        color: #667eea;
        font-weight: 600;
        transition: color 0.2s;
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
        color: #374151;
        padding: 8px 16px;
        border-radius: 4px;
        transition: all 0.2s ease;
        font-weight: 500;
        position: relative;
    }

    .order-icon:hover {
        background: #f3f4f6;
        color: #1d4ed8;
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
        margin: 40px auto;
        padding: 30px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 40px;
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

    .education-card {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    .education-card h2 {
        color: #1d4ed8;
        margin-top: 0;
        border-bottom: 2px solid #1d4ed8;
        padding-bottom: 10px;
    }

    .education-card p {
        line-height: 1.6;
        color: #555;
        margin-bottom: 15px;
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

    .category-card {
        width: 200px;
        text-align: center;
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        text-decoration: none;
        display: block;
    }

    .category-card:hover {
        border-color: #1d4ed8;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .category-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .category-name {
        margin-top: 8px;
        font-weight: 500;
        color: #374151;
        font-size: 15px;
    }
    
    .category-name:hover {
        color: #1d4ed8;
    }

    .search-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .no-results {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .category-link {
        display: inline-block;
        margin: 5px;
        padding: 8px 15px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.3s;
    }

    .category-link:hover {
        background: #764ba2;
        transform: translateY(-2px);
    }

    h1 {
        text-align: center;
        color: #1d4ed8;
        margin-bottom: 30px;
    }

    h2 {
        margin-top: 30px;
        margin-bottom: 20px;
        color: #1f2937;
        font-size: 32px;
        font-weight: 700;
        letter-spacing: -1px;
    }
</style>
</head>
<body>

<?php if ($action == 'home'): ?>
<!-- Full Page Hero Image -->
<?php
$tulip_image = "images/tulip-field.jpg";
if (!file_exists($tulip_image)) {
    // Fallback to placeholder or gradient if image doesn't exist
    $tulip_image = "images/placeholder.jpg";
}
?>
<div class="hero-image-container">
    <?php if (file_exists($tulip_image) || file_exists("images/tulip-field.jpg")): ?>
        <img src="images/tulip-field.jpg" alt="Tulip Field" class="hero-image" onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)';">
    <?php else: ?>
        <div style="width:100%; height:100%; background:linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
</div>
<?php endif; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">

<!-- TOP BAR -->
<div class="top-bar">
    <div class="logo-text">Team Toronto</div>

    <!-- Search Box -->
    <div class="search-box">
        <form method="GET" action="home.php" style="display: flex; width: 100%; align-items: center;">
            <input type="hidden" name="action" value="search">
            <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>" style="border: none; background: transparent; outline: none; font-size: 13px; width: 100%; padding: 4px 6px;">
            <button type="submit" style="background: none; border: none; font-size: 14px; margin-left: 4px; cursor: pointer; color: #666; padding: 4px 8px;">Search</button>
        </form>
    </div>

    <!-- Orders icon + username -->
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
                <a href="auth.php?action=logout">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- CONTENT AREA -->
<?php if ($action == 'education'): ?>
    <!-- Education Content -->
    <div class="container">
        <div class="back-link">
            <a href="home.php">← Back to Home</a>
        </div>
        
        <h1>Tulip Education</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Learn about tulips</p>
        
        <?php 
        if ($education_content && count($education_content) > 0): 
            foreach ($education_content as $edu): 
        ?>
            <div class="education-card">
                <h2><?php echo htmlspecialchars($edu['education_section']); ?></h2>
                <div class="info-section">
                    <p><?php echo nl2br(htmlspecialchars($edu['descriptions'])); ?></p>
                </div>
            </div>
        <?php 
            endforeach;
        else: 
        ?>
            <div class="education-card">
                <p style="text-align: center; color: #666;">No education content available yet.</p>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($action == 'search'): ?>
    <!-- Search Results -->
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
                            $link = 'category.php?id=' . $category['category_id'];
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

                    $category_link = 'category.php?id=' . $product['category_id'];

                    echo '<div class="product-card">';
                    echo '<img src="'.htmlspecialchars($img).'" alt="'.htmlspecialchars($product['product_name']).'">';
                    echo '<div class="product-name">'.htmlspecialchars($product['product_name']).'</div>';
                    echo '<div class="product-price">$'.number_format($product['cost'], 2).'</div>';
                    
                    if ($product['quantity'] > 0) {
                        echo '<div style="margin-top:10px;">';
                        echo '<label style="font-size:12px; color:#666; display:block; margin-bottom:5px;">Quantity:</label>';
                        echo '<input type="number" name="qty_'.$product['product_id'].'" id="qty_'.$product['product_id'].'" value="1" min="1" max="'.$product['quantity'].'" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; margin-bottom:8px;">';
                        echo '</div>';
                        
                        echo '<form method="POST" action="cart.php?action=add" style="margin-bottom:5px;">';
                        echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';
                        echo '<input type="hidden" name="quantity" id="cart_qty_'.$product['product_id'].'" value="1">';
                        echo '<input type="hidden" name="redirect" value="home.php?action=search&q='.urlencode($search_term).'">';
                        echo '<button type="submit" onclick="document.getElementById(\'cart_qty_'.$product['product_id'].'\').value = document.getElementById(\'qty_'.$product['product_id'].'\').value;" style="width:100%; padding:8px; background:#1d4ed8; color:white; border:none; border-radius:6px; cursor:pointer; margin-bottom:5px;">Add to Cart</button>';
                        echo '</form>';
                        
                        echo '<form method="POST" action="cart.php?action=buy_now">';
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

<?php else: ?>
    <!-- Home - Categories -->
    <div class="categories-container" style="padding: 20px; min-height: calc(100vh - 80px);">
        <div style="text-align:center; margin-bottom: 40px; padding: 30px 20px; position: relative; z-index: 10;">
            <a href="home.php?action=education" style="display:inline-block; padding:12px 28px; background:#1d4ed8; color:white; text-decoration:none; border-radius:6px; margin-bottom:30px; font-size:15px; font-weight:500; transition: all 0.2s ease; position: relative; z-index: 10;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Learn About Tulips</a>
            <h2 style="margin-top: 30px; margin-bottom: 15px; color: #1f2937; font-size: 28px; font-weight: 600; position: relative; z-index: 10;">Shop by Category</h2>
            <p style="color: #6b7280; font-size: 15px; margin-bottom: 10px; position: relative; z-index: 10;">Browse our collection</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; position: relative; z-index: 10;">
            <?php 
            if ($category_query->num_rows > 0) {
                while($category = $category_query->fetch_assoc()) {
                    $img_name = str_replace([' ', '&'], ['_', 'and'], $category['category_name']) . ".jpg";
                    $img_path = "images/categories/" . $img_name;

                    if (!file_exists($img_path)) {
                        $img_path = "images/category_placeholder.jpg";
                    }

                    $link = 'category.php?id=' . $category['category_id'];

                    echo '<a href="'.htmlspecialchars($link).'" class="category-card">';
                    echo '<img src="'.htmlspecialchars($img_path).'" alt="'.htmlspecialchars($category['category_name']).'">';
                    echo '<div class="category-name">'.htmlspecialchars($category['category_name']).'</div>';
                    echo '</a>';
                }
            } else {
                echo '<p style="text-align:center; width:100%; color: #6b7280; position: relative; z-index: 10;">No categories found. Please add categories to the database.</p>';
            }
            ?>
        </div>
    </div>
<?php endif; ?>


</div>
<!-- End Content Wrapper -->

</body>
</html>

