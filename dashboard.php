<?php
session_start(); 
include 'db.php';  // Ensure the path is correct to connect to db

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php"); // Redirect if admin is not logged in
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_role = $_SESSION['admin_role']; // inventory_manager, business_manager, owner
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
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
        Logged in as <strong>Employee ID: <?php echo htmlspecialchars($admin_id); ?></strong> |
        <a href="logout.php">Logout</a>
    </div>

    <h2>Employee Dashboard</h2>
    
    <div style="text-align:center; margin-bottom:20px; padding:15px; background:#e8efff; border-radius:8px;">
        <strong>Your Role:</strong> 
        <?php 
        $role_names = [
            'owner' => 'Owner',
            'business_manager' => 'Business Manager',
            'inventory_manager' => 'Inventory Manager'
        ];
        $role_display = isset($role_names[$admin_role]) ? $role_names[$admin_role] : ucfirst(str_replace('_', ' ', $admin_role));
        echo '<span style="color:#1d4ed8; font-size:18px; font-weight:bold;">' . $role_display . '</span>';
        ?>
        <p style="margin:10px 0 0 0; color:#666; font-size:13px;">
            <?php
            if ($admin_role == 'inventory_manager') {
                echo 'You can only update product inventory quantities.';
            } elseif ($admin_role == 'business_manager') {
                echo 'You can manage products and orders.';
            } else {
                echo 'You have full access: products, orders, employees, and reports.';
            }
            ?>
        </p>
    </div>

    <div class="cards">
        <?php if ($admin_role == 'inventory_manager'): ?>
            <!-- Inventory Manager: Only update inventory quantities -->
            <a class="card" href="update_inventory.php">Update Inventory</a>
        <?php elseif ($admin_role == 'business_manager'): ?>
            <!-- Business Manager: Products only -->
            <a class="card" href="products.php">Manage Products</a>
        <?php else: ?>
            <!-- Owner: Full access including HR and Reports (Administrative) -->
            <a class="card" href="products.php">Manage Products</a>
            <a class="card" href="hr.php">HR Section</a>
            <a class="card" href="reports.php">Reports / Analytics</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
