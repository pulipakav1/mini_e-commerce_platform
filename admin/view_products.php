<?php
session_start();
include '../db.php'; // Ensure this path is correct

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Delete image first
    $img_query = $conn->prepare("SELECT product_image FROM products WHERE product_id=?");
    $img_query->bind_param("i", $delete_id);
    $img_query->execute();
    $img_result = $img_query->get_result();
    if ($img_row = $img_result->fetch_assoc()) {
        if (file_exists('../' . $img_row['product_image'])) {
            unlink('../' . $img_row['product_image']);
        }
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: view_products.php");
    exit();
}

// Fetch all products
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Existing Products</title>
<style>
body { font-family: Arial; background: #f5f5f5; margin:0; padding:0; text-align:center; }
.container { max-width: 900px; margin: 60px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
h2 { margin-bottom: 25px; }
table { width: 100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; }
th { background:#1d4ed8; color:white; }
img { max-width:100px; max-height:80px; }
.delete-btn { background:#ef4444; color:white; padding:6px 12px; border:none; border-radius:6px; cursor:pointer; }
.delete-btn:hover { background:#dc2626; }
.back-btn { position:absolute; top:20px; left:20px; font-size:20px; color:#1d4ed8; text-decoration:none; }
</style>
</head>
<body>

<a href="products.php" class="back-btn">Back to Add Product</a>

<div class="container">
    <h2>Existing Products</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Image</th>
            <th>Category ID</th>
            <th>Cost</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['product_description']); ?></td>
                <td><img src="../<?php echo $row['product_image']; ?>" alt="Product Image"></td>
                <td><?php echo $row['category_id']; ?></td>
                <td><?php echo $row['cost']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>
                    <a href="view_products.php?delete_id=<?php echo $row['product_id']; ?>" onclick="return confirm('Are you sure to delete this product?')">
                        <button class="delete-btn">Delete</button>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
