<?php
session_start(); 
include '../db.php';  // Ensure the path is correct to connect to db

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php"); // Redirect if admin is not logged in
    exit();
}

$admin_userid = $_SESSION['admin_userid'];
$admin_role = $_SESSION['admin_role']; // inventory_manager, business_manager, owner
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; margin:0; padding:0; }
        .dashboard { max-width:900px; margin:50px auto; padding:20px; background:#fff; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align:center; margin-bottom:30px; }
        .cards { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; }
        .card { flex:0 0 200px; background:#1d4ed8; color:#fff; text-align:center; padding:30px 20px; border-radius:12px; cursor:pointer; text-decoration:none; font-size:16px; transition:0.3s; }
        .card:hover { background:#0d62d2; }
        .logout { text-align:right; margin-bottom:20px; }
        .logout a { color:#e63946; text-decoration:none; font-weight:bold; }
        .logout a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="dashboard">

    <div class="logout">
        Logged in as <strong><?php echo htmlspecialchars($admin_userid); ?></strong> |
        <a href="logout.php">Logout</a>
    </div>

    <h2>Admin Dashboard</h2>

    <div class="cards">
        <?php if ($admin_role == 'inventory_manager'): ?>
            <!-- Inventory Manager: Only access to products/inventory -->
            <a class="card" href="products.php">Manage Products</a>
            <a class="card" href="view_products.php">View All Products</a>
        <?php else: ?>
            <!-- Owner and Business Manager: Full access -->
            <a class="card" href="products.php">Manage Products</a>
            <a class="card" href="orders.php">Manage Orders</a>

            <!-- HR Section: only for owner or business_manager -->
            <?php if ($admin_role == 'owner' || $admin_role == 'business_manager') { ?>
                <a class="card" href="hr.php">HR Section</a>
            <?php } ?>

            <!-- Reports/Analytics: only for owner -->
            <?php if ($admin_role == 'owner') { ?>
                <a class="card" href="reports.php">Reports / Analytics</a>
            <?php } ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
