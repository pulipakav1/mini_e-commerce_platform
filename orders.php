<?php
session_start();
include 'db.php'; // Ensure the path to db.php is correct

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Restrict inventory_manager - they should only view products, not manage orders
$admin_role = $_SESSION['admin_role'];
if ($admin_role == 'inventory_manager') {
    // Redirect to products page for inventory managers
    header("Location: products.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // First, delete the image files from images table if exists
    $img_query = $conn->prepare("SELECT file_path FROM images WHERE product_id = ?");
    $img_query->bind_param("i", $delete_id);
    $img_query->execute();
    $img_result = $img_query->get_result();
    while ($img_row = $img_result->fetch_assoc()) {
        if (!empty($img_row['file_path']) && file_exists($img_row['file_path'])) {
            unlink($img_row['file_path']);
        }
    }
    // Delete from images table
    $del_img = $conn->prepare("DELETE FROM images WHERE product_id = ?");
    $del_img->bind_param("i", $delete_id);
    $del_img->execute();

    // Delete the product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: products.php"); // reload page
    exit();
}

// Handle search/filter
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_param = "%".$search."%";
    // Only search product_name with LIKE, category_id should be exact match
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_name LIKE ?");
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM products");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Products</title>
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
        color: #0d62d2;
    }

    .container {
        max-width: 1000px;
        margin: 80px auto 50px;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .search-form {
        text-align: right;
        margin-bottom: 15px;
    }

    .search-form input[type="text"] {
        padding: 6px 10px;
        font-size: 14px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .search-form button {
        padding: 6px 12px;
        background-color: #1d4ed8;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .search-form button:hover {
        background-color: #0d62d2;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
        vertical-align: middle;
    }

    th {
        background-color: #1d4ed8;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    img.product-img {
        max-width: 100px;
        max-height: 80px;
        border-radius: 6px;
    }

    .action-btn {
        display: inline-block;
        padding: 6px 12px;
        margin: 2px;
        background-color: #1d4ed8;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        width: 60px;
        text-align: center;
    }

    .action-btn:hover {
        background-color: #0d62d2;
    }

</style>
</head>
<body>

<a href="dashboard.php" class="back-btn">Back to Dashboard</a>

<div class="container">
    <h2>Manage Products</h2>

    <!-- Search Form -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Products Table -->
    <table>
        <thead>
            <tr>
                <th>Category ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Image</th>
                <th>Cost</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_description']); ?></td>
                        <td>
                            <?php 
                            // Get image from images table
                            $prod_img = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
                            $prod_img->bind_param("i", $row['product_id']);
                            $prod_img->execute();
                            $prod_img_result = $prod_img->get_result();
                            $prod_img_data = $prod_img_result->fetch_assoc();
                            if($prod_img_data && !empty($prod_img_data['file_path'])): 
                            ?>
                                <img src="<?php echo htmlspecialchars($prod_img_data['file_path']); ?>" alt="Product Image" class="product-img">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['cost']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>
                            <a href="products.php?action=edit&id=<?php echo $row['product_id']; ?>" class="action-btn">Edit</a>
                            <a href="products.php?delete_id=<?php echo $row['product_id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
