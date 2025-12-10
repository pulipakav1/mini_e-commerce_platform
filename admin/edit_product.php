<?php
session_start();
include '../db.php'; // Ensure the path to db.php is correct

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get product ID from URL
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];
$error = "";
$success = "";

// Fetch product/order details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    header("Location: products.php");
    exit();
}
$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $category_id = trim($_POST['category_id']);
    $cost = trim($_POST['cost']);
    $quantity = trim($_POST['quantity']);
    // Get product image from images table if exists
    $img_stmt = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
    $img_stmt->bind_param("i", $product_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $img_row = $img_result->fetch_assoc();
    $product_image_path = $img_row['file_path'] ?? '';

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $img_name = $_FILES['product_image']['name'];
        $img_tmp = $_FILES['product_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (in_array($img_ext, $allowed)) {
            // Delete old image
            if (!empty($product_image_path) && file_exists('../'.$product_image_path)) {
                unlink('../'.$product_image_path);
            }

            $new_name = uniqid('prod_', true) . '.' . $img_ext;
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $upload_path = $upload_dir . $new_name;

            if (move_uploaded_file($img_tmp, $upload_path)) {
                $product_image_path = 'uploads/' . $new_name;
                // Delete old image from images table
                $del_img = $conn->prepare("DELETE FROM images WHERE product_id=?");
                $del_img->bind_param("i", $product_id);
                $del_img->execute();
                // Insert new image
                $ins_img = $conn->prepare("INSERT INTO images (product_id, file_path) VALUES (?, ?)");
                $ins_img->bind_param("is", $product_id, $product_image_path);
                $ins_img->execute();
            } else {
                $error = "Failed to upload image!";
            }
        } else {
            $error = "Invalid image format! Only jpg, jpeg, png, gif allowed.";
        }
    }

    // Update database (products table doesn't have product_image field)
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, product_description=?, category_id=?, cost=?, quantity=? WHERE product_id=?");
        $stmt->bind_param("ssiddi", $product_name, $product_description, $category_id, $cost, $quantity, $product_id);

        if ($stmt->execute()) {
            $success = "Product updated successfully!";
            // Refresh product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Failed to update product!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
    text-align: center;
}

.container {
    max-width: 600px;
    margin: 60px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

h2 {
    margin-bottom: 25px;
}

input, textarea {
    width: 90%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
}

button {
    padding: 12px 25px;
    background: #1d4ed8;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

button:hover {
    background: #0d62d2;
}

.back-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 20px;
    color: #1d4ed8;
    text-decoration: none;
}

.error { color:red; margin-bottom:10px; }
.success { color:green; margin-bottom:10px; }

img.product-img {
    max-width: 120px;
    max-height: 100px;
    margin-top: 10px;
    border-radius: 6px;
}
</style>
</head>
<body>

<a href="products.php" class="back-btn">Back to Products</a>

<div class="container">
    <h2>Edit Product</h2>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="product_name" placeholder="Product Name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        <textarea name="product_description" placeholder="Product Description" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
        <input type="number" name="category_id" placeholder="Category ID" value="<?php echo $product['category_id']; ?>" required>
        <input type="number" step="0.01" name="cost" placeholder="Cost" value="<?php echo $product['cost']; ?>" required>
        <input type="number" name="quantity" placeholder="Quantity" value="<?php echo $product['quantity']; ?>" required>
        <div>
            <label>Current Image:</label><br>
            <?php 
            $current_img = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
            $current_img->bind_param("i", $product_id);
            $current_img->execute();
            $current_img_result = $current_img->get_result();
            $current_img_data = $current_img_result->fetch_assoc();
            if($current_img_data && !empty($current_img_data['file_path'])): 
            ?>
                <img src="../<?php echo htmlspecialchars($current_img_data['file_path']); ?>" alt="Product Image" class="product-img">
            <?php else: ?>
                N/A
            <?php endif; ?>
        </div>
        <input type="file" name="product_image" accept="image/*">
        <button type="submit" name="update_product">Update Product</button>
    </form>
</div>

</body>
</html>
