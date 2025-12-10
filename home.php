<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user info (using user_id and name as per schema)
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
    // User not found - session might be invalid, redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}

$name = htmlspecialchars($user['name']);

// Fetch categories with images (use DISTINCT to avoid duplicates)
$category_query = $conn->query("SELECT DISTINCT category_id, category_name FROM category ORDER BY category_id");
if (!$category_query) {
    die("Database error: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home - Flower Shop</title>
<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    /* Top Bar */
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

    /* Search Box */
    .search-box {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 2px solid #e0e7ff;
        border-radius: 12px;
        padding: 8px 15px;
        width: 400px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .search-box:focus-within {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
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

    .search-icon:hover {
        color: #764ba2;
    }

    /* Top-right items */
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
        color: #667eea;
        padding: 8px 14px;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
        position: relative;
    }

    .order-icon:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .user-name {
        font-size: 13px;
        font-weight: bold;
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

    /* Content Box */
    .home-box {
        text-align: center;
        padding: 60px 20px 100px 20px;
    }

    /* Modern Floating Bottom Menu */
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

    .bottom-menu span {
        font-size: 11px;
        margin-top: 3px;
    }
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="logo-text">Team Toronto</div>

    <!-- Small Search Field -->
    <div class="search-box">
        <form method="GET" action="search.php" style="display: flex; width: 100%; align-items: center;">
            <input type="text" name="q" placeholder="Search products..." style="border: none; background: transparent; outline: none; font-size: 13px; width: 100%; padding: 4px 6px;">
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
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- CATEGORIES SECTION -->
<div class="categories-container" style="padding: 20px;">
    <div style="text-align:center; margin-bottom: 40px; padding: 30px 20px;">
        <a href="education.php" style="display:inline-block; padding:14px 32px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; text-decoration:none; border-radius:12px; margin-bottom:25px; font-size:16px; font-weight:600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)'">Learn About Tulips</a>
        <h2 style="margin-top: 30px; margin-bottom: 20px; color: #1f2937; font-size: 32px; font-weight: 700; letter-spacing: -1px;">Shop by Category</h2>
        <p style="color: #6b7280; font-size: 16px; margin-bottom: 10px;">Discover our curated collection</p>
    </div>
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
        <?php 
        if ($category_query->num_rows > 0) {
            while($category = $category_query->fetch_assoc()) {

            // Convert category name to valid image file name
            $img_name = str_replace([' ', '&'], ['_', 'and'], $category['category_name']) . ".jpg";
            $img_path = "images/categories/" . $img_name;

            // If image not found, use a placeholder
            if (!file_exists($img_path)) {
                $img_path = "images/category_placeholder.jpg";
            }

            // Map category names to dedicated pages
            $category_links = [
                'Home & Living' => 'home_living.php',
                'Cups & Bottles' => 'cups_bottles.php',
                'Style Accessories' => 'style_accessories.php',
                'Tulip Collection' => 'tulip_collection.php',
                'Indoor Plants' => 'indoor_plants.php'
            ];

            $link = $category_links[$category['category_name']] ?? '#';

            echo '<a href="'.htmlspecialchars($link).'" style="text-decoration:none; display:block; transition: all 0.3s ease;" onmouseover="this.style.transform=\'translateY(-8px)\'" onmouseout="this.style.transform=\'translateY(0)\'">';
                echo '<div style="width: 180px; text-align:center; background:white; padding:20px; border-radius:16px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;">';
                    echo '<img src="'.htmlspecialchars($img_path).'" alt="'.htmlspecialchars($category['category_name']).'" style="width:100%; height:140px; object-fit:cover; border-radius:12px; margin-bottom:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
                    echo '<div style="margin-top:8px; font-weight:600; color:#667eea; font-size:15px;">'.htmlspecialchars($category['category_name']).'</div>';
                echo '</div>';
            echo '</a>';
            }
        } else {
            echo '<p style="text-align:center; width:100%;">No categories found. Please add categories to the database.</p>';
        }
        ?>
    </div>
</div>


<!-- MAIN HOME CONTENT -->
<!--<div class="home-box">-->
<!--    <h2>Welcome to the Flower Shop 🌸</h2>-->
<!--    <p>Explore beautiful flowers!</p>-->
<!--</div>-->

<!-- BOTTOM NAVIGATION -->
<div class="bottom-menu">
    <a href="home.php" class="active"><span>Home</span></a>
    <a href="profile.php"><span>Profile</span></a>
</div>

</body>
</html>