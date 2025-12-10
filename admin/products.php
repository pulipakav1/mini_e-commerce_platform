<?php
session_start();
include '../db.php'; // Ensure this path is correct

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $category_id = trim($_POST['category_id']);
    $cost = trim($_POST['cost']);
    $quantity = trim($_POST['quantity']);

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $img_name = $_FILES['product_image']['name'];
        $img_tmp = $_FILES['product_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed)) {
            $new_name = uniqid('prod_', true) . '.' . $img_ext;
            $upload_dir = '../uploads/'; // Ensure this folder exists
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $upload_path = $upload_dir . $new_name;

            if (move_uploaded_file($img_tmp, $upload_path)) {
                $product_image_path = 'uploads/' . $new_name;
            } else {
                $error = "Failed to upload image!";
            }
        } else {
            $error = "Invalid image format! Only jpg, jpeg, png, gif allowed.";
        }
    } else {
        $error = "Product image is required!";
    }

    // Insert into database
    if (empty($error)) {
        // Products table schema: product_id (auto), product_name, product_description, category_id, cost, quantity
        // Images stored in separate images table, not product_image field
        $stmt = $conn->prepare("INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidd", $product_name, $product_description, $category_id, $cost, $quantity);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            // Insert image into images table
            if (isset($product_image_path)) {
                $img_stmt = $conn->prepare("INSERT INTO images (product_id, file_path) VALUES (?, ?)");
                $img_stmt->bind_param("is", $product_id, $product_image_path);
                $img_stmt->execute();
                $img_stmt->close();
            }
            $success = "Product added successfully!";
        } else {
            $error = "Failed to add product. Try again!";
        }
        $stmt->close();

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product</title>
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
        margin: 100px auto 60px;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        position: relative;
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

    .back-btn, .view-btn {
        position: absolute;
        top: 20px;
        font-size: 18px;
        color: #1d4ed8;
        text-decoration: none;
        font-weight: bold;
    }

    .back-btn {
        left: 20px;
    }

    .view-btn {
        right: 20px;
    }

    .back-btn:hover, .view-btn:hover {
        color: #0d62d2;
    }

    .error {
        color: red;
        margin-bottom: 10px;
    }

    .success {
        color: green;
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<!-- Top navigation buttons -->
<a href="dashboard.php" class="back-btn">Back to Dashboard</a>
<a href="view_products.php" class="view-btn">View Existing Products</a>

<div class="container">
    <h2>Add New Product</h2>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="product_name" placeholder="Product Name" required>
        <textarea name="product_description" placeholder="Product Description" required></textarea>
        <input type="file" name="product_image" accept="image/*" required>
        <input type="number" name="category_id" placeholder="Category ID" required>
        <input type="number" step="0.01" name="cost" placeholder="Cost" required>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <button type="submit" name="add_product">Add Product</button>
    </form>
</div>

</body>
</html>
