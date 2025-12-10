<?php
// Start session if you need user login validation
// session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wishlist Page</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: white;
    text-align: center;
    margin: 0;
    padding: 0;
}

.box {
    margin-top: 50px;
}

/* Quick Access Top Bar */
.quick-access-top {
    display: flex;
    justify-content: center;
    gap: 15px;
    background: #fff;
    padding: 10px 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.quick-access-top a {
    text-decoration: none;
    color: #1d4ed8;
    font-size: 14px;
    font-weight: bold;
    padding: 6px 12px;
    border: 1px solid #1d4ed8;
    border-radius: 12px;
    transition: 0.3s;
}

.quick-access-top a:hover {
    background: #1d4ed8;
    color: #fff;
}


/* Floating Bottom Menu */
.bottom-menu {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #ffffff;
    display: flex;
    justify-content: space-around;
    width: 90%;
    max-width: 500px;
    padding: 12px 0;
    border-radius: 30px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    backdrop-filter: blur(12px);
    z-index: 1000;
}

.bottom-menu a {
    text-decoration: none;
    color: #888;
    font-size: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: 0.3s ease;
    font-weight: bold;
}

.bottom-menu a span {
    font-size: 12px;
    margin-top: 3px;
}

.bottom-menu a:hover,
.bottom-menu a.active {
    color: #1d4ed8;
    transform: translateY(-3px);
}

.bottom-menu a.active {
    font-weight: bold;
}
</style>
</head>
<body>



<!-- Quick Access Bar -->
<div class="quick-access-top">
    <a href="my_orders.php">Orders</a>
    <a href="saved_orders.php">Saved</a>
</div>


<div class="box">
    <h2>Your Wishlist</h2>
    <p>No saved flowers yet!</p>
</div>

<!-- Bottom Menu -->
<div class="bottom-menu">
    <a href="home.php"><span>Home</span></a>
    <a href="wishlist.php" class="active"><span>Wishlist</span></a>
    <a href="profile.php"><span>Profile</span></a>
</div>

</body>
</html>
