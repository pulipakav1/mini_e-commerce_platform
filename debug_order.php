<?php
// Minimal test page
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

echo "<h1>DEBUG PAGE WORKS</h1>";
echo "<p>Session started</p>";
echo "<p>Database connected</p>";

if (!isset($_SESSION['user_id'])) {
    echo "<p>ERROR: Not logged in</p>";
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

echo "<p>User ID: $user_id</p>";
echo "<p>Order ID: $order_id</p>";

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            echo "<p>ORDER FOUND:</p>";
            echo "<pre>";
            print_r($order);
            echo "</pre>";
        } else {
            echo "<p>ORDER NOT FOUND in database</p>";
        }
        $stmt->close();
    } else {
        echo "<p>ERROR: Could not prepare statement: " . $conn->error . "</p>";
    }
}
?>

