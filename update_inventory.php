<?php
session_start();
include 'db.php';

// Check if employee is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Only inventory_manager can access this page
$admin_role = $_SESSION['admin_role'];
if ($admin_role != 'inventory_manager') {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

// Handle inventory update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $product_id = intval($_POST['update_inventory']);
    $quantity = isset($_POST['quantities'][$product_id]) ? intval($_POST['quantities'][$product_id]) : 0;
    
    if ($quantity < 0) {
        $error = "Quantity cannot be negative!";
    } else {
        // Update products table quantity
        $update_product = $conn->prepare("UPDATE products SET quantity = ? WHERE product_id = ?");
        $update_product->bind_param("ii", $quantity, $product_id);
        
        if ($update_product->execute()) {
            // Update or create inventory record
            $inv_check = $conn->prepare("SELECT inventory_id FROM inventory WHERE product_id = ?");
            $inv_check->bind_param("i", $product_id);
            $inv_check->execute();
            $inv_result = $inv_check->get_result();
            
            if ($inv_result->num_rows > 0) {
                $update_inv = $conn->prepare("UPDATE inventory SET quantity = ?, last_updated = CURRENT_TIMESTAMP WHERE product_id = ?");
                $update_inv->bind_param("ii", $quantity, $product_id);
                $update_inv->execute();
                $update_inv->close();
            } else {
                $insert_inv = $conn->prepare("INSERT INTO inventory (product_id, quantity, last_updated) VALUES (?, ?, CURRENT_TIMESTAMP)");
                $insert_inv->bind_param("ii", $product_id, $quantity);
                $insert_inv->execute();
                $insert_inv->close();
            }
            $inv_check->close();
            $update_product->close();
            
            $success = "Inventory updated successfully for Product ID: " . $product_id . "!";
            // Refresh the page to show updated quantities
            header("Location: update_inventory.php");
            exit();
        } else {
            $error = "Failed to update inventory: " . $update_product->error;
        }
    }
}

// Fetch all products with their current quantities
$products_query = $conn->query("SELECT p.product_id, p.product_name, p.quantity FROM products p ORDER BY p.product_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Inventory</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
}

.back-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 18px;
    color: #1d4ed8;
    text-decoration: none;
    font-weight: bold;
}

.back-btn:hover {
    text-decoration: underline;
}

.container {
    max-width: 900px;
    margin: 60px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #1d4ed8;
}

.error {
    color: #ef4444;
    background: #fee;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
}

.success {
    color: #10b981;
    background: #ecfdf5;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background: #1d4ed8;
    color: white;
    font-weight: 600;
}

tr:hover {
    background: #f9fafb;
}

input[type="number"] {
    width: 100px;
    padding: 8px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
}

input[type="number"]:focus {
    border-color: #1d4ed8;
    outline: none;
    box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
}

.btn {
    padding: 8px 16px;
    background: #1d4ed8;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #0d62d2;
    transform: translateY(-1px);
}

.empty {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<div class="container">
    <h2>Update Inventory</h2>
    <p style="text-align:center; color:#666; margin-bottom:20px;">Update product quantities only. You cannot create, edit, or delete products.</p>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($products_query && $products_query->num_rows > 0): ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Current Quantity</th>
                        <th>New Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products_query->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td style="font-weight:bold; color:#1d4ed8;"><?php echo $product['quantity']; ?></td>
                            <td>
                                <input type="hidden" name="product_ids[]" value="<?php echo $product['product_id']; ?>">
                                <input type="number" name="quantities[<?php echo $product['product_id']; ?>]" 
                                       value="<?php echo $product['quantity']; ?>" 
                                       min="0" required>
                            </td>
                            <td>
                                <button type="submit" name="update_inventory" value="<?php echo $product['product_id']; ?>" class="btn">
                                    Update
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>
    <?php else: ?>
        <div class="empty">No products found.</div>
    <?php endif; ?>
</div>

</body>
</html>

