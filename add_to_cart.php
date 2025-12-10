<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    $stmt = $conn->prepare("SELECT product_id, product_name, cost, quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $message = "Product not found";
    } else {
        $product = $result->fetch_assoc();
        
        if ($product['quantity'] < $quantity) {
            $message = "Only " . $product['quantity'] . " available";
        } else {
            $check_stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $cart_item = $check_result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + $quantity;
                
                if ($new_quantity > $product['quantity']) {
                    $message = "Only " . $product['quantity'] . " in stock. You have " . $cart_item['quantity'] . " in cart";
                } else {
                    $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
                    $update_stmt->bind_param("ii", $new_quantity, $cart_item['cart_id']);
                    if ($update_stmt->execute()) {
                        $update_stmt->close();
                        $message = "Updated";
                    } else {
                        $message = "Error updating cart: " . $update_stmt->error;
                        $update_stmt->close();
                    }
                }
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    $message = "Added to cart";
                } else {
                    $message = "Error adding to cart: " . $insert_stmt->error;
                    $insert_stmt->close();
                }
            }
        }
    }
}

$redirect = $_POST['redirect'] ?? 'home.php';
// Validate redirect to prevent open redirect vulnerability
$allowed_redirects = ['home.php', 'cart.php', 'home_living.php', 'cups_bottles.php', 'style_accessories.php', 'tulip_collection.php', 'indoor_plants.php'];
$redirect = in_array($redirect, $allowed_redirects) ? $redirect : 'home.php';
header("Location: " . $redirect . "?message=" . urlencode($message));
exit();

