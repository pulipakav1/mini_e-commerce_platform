<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0 || $quantity <= 0) {
    header("Location: home.php");
    exit();
}

// Check product exists and has stock
$stmt = $conn->prepare("SELECT product_id, product_name, cost, quantity FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: home.php?message=" . urlencode("Product not found"));
    exit();
}

$product = $result->fetch_assoc();

if ($product['quantity'] < $quantity) {
    header("Location: home.php?message=" . urlencode("Only " . $product['quantity'] . " available"));
    exit();
}

// Clear cart first for buy now
$clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$clear_cart->bind_param("i", $user_id);
$clear_cart->execute();
$clear_cart->close();

// Add product to cart
$insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
$insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);

if ($insert_stmt->execute()) {
    $insert_stmt->close();
    // Redirect directly to checkout
    header("Location: checkout.php");
    exit();
} else {
    header("Location: home.php?message=" . urlencode("Error adding to cart"));
    exit();
}

