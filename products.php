<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

// Restrict inventory_manager - they can only update inventory, not manage products
$admin_role = $_SESSION['admin_role'];
if ($admin_role == 'inventory_manager') {
    header("Location: update_inventory.php");
    exit();
}

$error = "";
$success = "";
$action = $_GET['action'] ?? 'view'; // Default to 'view' (list products)

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $img_query = $conn->prepare("SELECT file_path FROM images WHERE product_id=?");
    $img_query->bind_param("i", $delete_id);
    $img_query->execute();
    $img_result = $img_query->get_result();
    while ($img_row = $img_result->fetch_assoc()) {
        if (!empty($img_row['file_path']) && file_exists($img_row['file_path'])) {
            unlink($img_row['file_path']);
        }
    }
    $del_img = $conn->prepare("DELETE FROM images WHERE product_id=?");
    $del_img->bind_param("i", $delete_id);
    $del_img->execute();
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: products.php?action=view");
    exit();
}

// Handle add product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $category_id = intval($_POST['category_id']);
    $cost = floatval($_POST['cost']);
    $quantity = intval($_POST['quantity']);

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $img_name = $_FILES['product_image']['name'];
        $img_tmp = $_FILES['product_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed)) {
            $new_name = uniqid('prod_', true) . '.' . $img_ext;
            $upload_dir = 'uploads/';
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

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, product_description, category_id, cost, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidi", $product_name, $product_description, $category_id, $cost, $quantity);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            if (isset($product_image_path)) {
                $img_stmt = $conn->prepare("INSERT INTO images (product_id, file_path) VALUES (?, ?)");
                $img_stmt->bind_param("is", $product_id, $product_image_path);
                $img_stmt->execute();
                $img_stmt->close();
            }
            
            // Create or update inventory record
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
            
            $success = "Product added successfully!";
            $action = 'view'; // Switch to view after successful add
        } else {
            $error = "Failed to add product: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle edit product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = intval($_POST['product_id']);
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $category_id = intval($_POST['category_id']);
    $cost = floatval($_POST['cost']);
    $quantity = intval($_POST['quantity']);
    
    $img_stmt = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
    $img_stmt->bind_param("i", $product_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $img_row = $img_result->fetch_assoc();
    $product_image_path = $img_row['file_path'] ?? '';

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $img_name = $_FILES['product_image']['name'];
        $img_tmp = $_FILES['product_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (in_array($img_ext, $allowed)) {
            if (!empty($product_image_path) && file_exists($product_image_path)) {
                unlink($product_image_path);
            }

            $new_name = uniqid('prod_', true) . '.' . $img_ext;
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $upload_path = $upload_dir . $new_name;

            if (move_uploaded_file($img_tmp, $upload_path)) {
                $product_image_path = 'uploads/' . $new_name;
                $del_img = $conn->prepare("DELETE FROM images WHERE product_id=?");
                $del_img->bind_param("i", $product_id);
                $del_img->execute();
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

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, product_description=?, category_id=?, cost=?, quantity=? WHERE product_id=?");
        $stmt->bind_param("ssidii", $product_name, $product_description, $category_id, $cost, $quantity, $product_id);

        if ($stmt->execute()) {
            // Update inventory table
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
            
            $success = "Product updated successfully!";
            $action = 'view';
        } else {
            $error = "Failed to update product: " . $stmt->error;
        }
    }
}

// Fetch categories for dropdowns
$categories_query = $conn->query("SELECT category_id, category_name FROM category ORDER BY category_name");

// Fetch product for edit
$product = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $action = 'view';
    }
}

// Fetch all products for view
$products_result = null;
if ($action == 'view') {
    $products_result = $conn->query("SELECT * FROM products ORDER BY product_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php 
if ($action == 'add') echo 'Add Product';
elseif ($action == 'edit') echo 'Edit Product';
else echo 'Manage Products';
?></title>
<style>
body { font-family: Arial; background: #f5f5f5; margin:0; padding:0; }
.nav-tabs { text-align:center; padding:20px; background:#fff; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.nav-tabs a { display:inline-block; padding:10px 20px; margin:0 5px; background:#1d4ed8; color:white; text-decoration:none; border-radius:5px; }
.nav-tabs a.active { background:#0d62d2; }
.container { max-width: 900px; margin: 20px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
.back-btn { position:absolute; top:20px; left:20px; font-size:18px; color:#1d4ed8; text-decoration:none; font-weight:bold; }
.back-btn:hover { text-decoration:underline; }
h2 { margin-bottom: 25px; text-align:center; }
input, textarea, select { width: 95%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc; }
button { padding: 12px 25px; background: #1d4ed8; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-top: 10px; }
button:hover { background: #0d62d2; }
.error { color:red; margin-bottom:10px; text-align:center; padding:10px; background:#fee; border-radius:6px; }
.success { color:green; margin-bottom:10px; text-align:center; padding:10px; background:#efe; border-radius:6px; }
table { width: 100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; }
th { background:#1d4ed8; color:white; }
img { max-width:100px; max-height:80px; }
.delete-btn { background:#ef4444; color:white; padding:6px 12px; border:none; border-radius:6px; cursor:pointer; }
.delete-btn:hover { background:#dc2626; }
.edit-btn { background:#10b981; color:white; padding:6px 12px; border:none; border-radius:6px; cursor:pointer; text-decoration:none; display:inline-block; }
.edit-btn:hover { background:#059669; }
img.product-img { max-width: 120px; max-height: 100px; margin-top: 10px; border-radius: 6px; }
</style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<div class="nav-tabs">
    <a href="products.php?action=view" class="<?php echo $action == 'view' ? 'active' : ''; ?>">View Products</a>
    <a href="products.php?action=add" class="<?php echo $action == 'add' ? 'active' : ''; ?>">Add Product</a>
</div>

<div class="container">
    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success != ""): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h2>Add New Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <textarea name="product_description" placeholder="Product Description" required style="width:95%; padding:12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; min-height:100px;"></textarea>
            <select name="category_id" required style="width: 95%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc;">
                <option value="">Select Category</option>
                <?php
                if ($categories_query && $categories_query->num_rows > 0) {
                    while($category = $categories_query->fetch_assoc()) {
                        echo '<option value="'.$category['category_id'].'">'.htmlspecialchars($category['category_name']).'</option>';
                    }
                    $categories_query->data_seek(0);
                }
                ?>
            </select>
            <input type="number" step="0.01" name="cost" placeholder="Cost" required>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <input type="file" name="product_image" accept="image/*" required>
            <button type="submit" name="add_product">Add Product</button>
        </form>

    <?php elseif ($action == 'edit' && $product): ?>
        <h2>Edit Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <input type="text" name="product_name" placeholder="Product Name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            <textarea name="product_description" placeholder="Product Description" required style="width:95%; padding:12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; min-height:100px;"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
            <select name="category_id" required style="width: 95%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc;">
                <option value="">Select Category</option>
                <?php
                if ($categories_query && $categories_query->num_rows > 0) {
                    while($category = $categories_query->fetch_assoc()) {
                        $selected = ($category['category_id'] == $product['category_id']) ? 'selected' : '';
                        echo '<option value="'.$category['category_id'].'" '.$selected.'>'.htmlspecialchars($category['category_name']).'</option>';
                    }
                }
                ?>
            </select>
            <input type="number" step="0.01" name="cost" placeholder="Cost" value="<?php echo $product['cost']; ?>" required>
            <input type="number" name="quantity" placeholder="Quantity" value="<?php echo $product['quantity']; ?>" required>
            <div>
                <label>Current Image:</label><br>
                <?php 
                $current_img = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
                $current_img->bind_param("i", $product['product_id']);
                $current_img->execute();
                $current_img_result = $current_img->get_result();
                $current_img_data = $current_img_result->fetch_assoc();
                if($current_img_data && !empty($current_img_data['file_path'])): 
                ?>
                    <img src="<?php echo htmlspecialchars($current_img_data['file_path']); ?>" alt="Product Image" class="product-img">
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
            <input type="file" name="product_image" accept="image/*">
            <button type="submit" name="update_product">Update Product</button>
        </form>

    <?php else: // action == 'view' ?>
        <h2>All Products</h2>
        <?php if ($products_result && $products_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Cost</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['product_description'], 0, 50)) . '...'; ?></td>
                        <td>
                            <?php 
                            $prod_img = $conn->prepare("SELECT file_path FROM images WHERE product_id=? LIMIT 1");
                            $prod_img->bind_param("i", $row['product_id']);
                            $prod_img->execute();
                            $prod_img_result = $prod_img->get_result();
                            $prod_img_data = $prod_img_result->fetch_assoc();
                            if($prod_img_data && !empty($prod_img_data['file_path'])): 
                            ?>
                                <img src="<?php echo htmlspecialchars($prod_img_data['file_path']); ?>" alt="Product Image">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['category_id']; ?></td>
                        <td>$<?php echo number_format($row['cost'], 2); ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>
                            <a href="products.php?action=edit&id=<?php echo $row['product_id']; ?>" class="edit-btn">Edit</a>
                            <a href="products.php?delete_id=<?php echo $row['product_id']; ?>" onclick="return confirm('Are you sure to delete this product?')">
                                <button class="delete-btn">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding:40px;">No products found. <a href="products.php?action=add">Add a product</a></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>

