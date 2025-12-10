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
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$name = $user['name'];

// Fetch categories with images
$category_query = $conn->query("SELECT category_id, category_name FROM category");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home - Flower Shop</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: #ffffff;
    }

    /* Top Bar */
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

    .logo-text {
        font-size: 20px;
        font-weight: bold;
        color: #1d4ed8;
    }

    /* Search Box */
    .search-box {
        display: flex;
        align-items: center;
        background: #f1f3f5;
        border-radius: 12px;
        padding: 4px 8px;
        width: 600px; /* slightly bigger */
    }

    .search-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 14px;
        width: 100%;
        padding: 4px 6px;
    }

    .search-icon {
        font-size: 18px;
        margin-left: 4px;
        cursor: pointer;
    }

    /* Top-right items */
    .top-right {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-right: 50px;
    }

    .order-icon {
        font-size: 22px;
        cursor: pointer;
        text-decoration: none;
        color: #1d4ed8;
        padding: 8px;
        border-radius: 50%;
        transition: 0.3s;
    }

    .order-icon:hover {
        background: #e8efff;
    }

    .user-name {
        font-size: 14px;
        font-weight: bold;
        color: #333;
    }

    /* Content Box */
    .home-box {
        text-align: center;
        padding: 60px 20px 100px 20px;
    }

    /* Modern Floating Bottom Menu */
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
        <input type="text" placeholder="Search...">
        <div class="search-icon">Search</div>
    </div>

    <!-- Orders icon + username -->
    <div class="top-right">
        <a href="cart.php" class="order-icon" title="Cart" style="position:relative;">
            Cart
            <?php
            $cart_count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
            $cart_count_stmt->bind_param("i", $user_id);
            $cart_count_stmt->execute();
            $cart_count_result = $cart_count_stmt->get_result();
            $cart_count = $cart_count_result->fetch_assoc()['total'] ?? 0;
            if ($cart_count > 0) {
                echo '<span style="position:absolute; top:-5px; right:-5px; background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:11px; display:flex; align-items:center; justify-content:center;">'.$cart_count.'</span>';
            }
            ?>
        </a>
        <a href="my_orders.php" class="order-icon" title="Order History">Orders</a>
        <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
    </div>
</div>

<!-- CATEGORIES SECTION -->
<div class="categories-container" style="padding: 20px;">
    <div style="text-align:center; margin-bottom: 20px;">
        <h2 style="margin-bottom: 15px;">Shop by Category</h2>
        <a href="education.php" style="display:inline-block; padding:10px 20px; background:#1d4ed8; color:white; text-decoration:none; border-radius:8px; margin-bottom:15px;">Learn About Tulips</a>
    </div>
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
        <?php 
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

            echo '<a href="'.htmlspecialchars($link).'" style="text-decoration:none;">';
                echo '<div style="width: 160px; text-align:center;">';
                    echo '<img src="'.htmlspecialchars($img_path).'" alt="'.htmlspecialchars($category['category_name']).'" style="width:100%; height:120px; object-fit:cover; border-radius:12px;">';
                    echo '<div style="margin-top:8px; font-weight:bold; color:#1d4ed8;">'.htmlspecialchars($category['category_name']).'</div>';
                echo '</div>';
            echo '</a>';
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