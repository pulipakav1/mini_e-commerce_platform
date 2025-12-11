<?php
session_start();
include "db.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

echo "<h1>Test Order Page</h1>";
echo "<p>Order ID from URL: $order_id</p>";
echo "<p>Session User ID: " . ($user_id ?: 'NOT SET') . "</p>";

// Show all recent orders for this user
echo "<h2>Recent Orders for User ID: $user_id</h2>";
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC LIMIT 10");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Order ID</th><th>User ID</th><th>Address</th><th>Total</th><th>Order Date</th></tr>";
        while ($order = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><a href='order_confirmation.php?order_id=" . $order['order_id'] . "&receipt=REC-" . str_pad($order['order_id'], 6, "0", STR_PAD_LEFT) . "'>" . $order['order_id'] . "</a></td>";
            echo "<td>" . $order['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($order['address'], 0, 50)) . "</td>";
            echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
            echo "<td>" . $order['order_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No orders found for this user</p>";
    }
    $stmt->close();
} else {
    echo "<p>Error: " . $conn->error . "</p>";
}

// Test specific order if provided
if ($order_id > 0) {
    echo "<h2>Testing Order ID: $order_id</h2>";
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            echo "<p><strong>ORDER FOUND:</strong></p>";
            echo "<pre>";
            print_r($order);
            echo "</pre>";
        } else {
            echo "<p><strong>ORDER NOT FOUND</strong> - Check if order_id exists or belongs to different user</p>";
            // Check without user_id
            $stmt2 = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
            $stmt2->bind_param("i", $order_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($result2->num_rows > 0) {
                $order2 = $result2->fetch_assoc();
                echo "<p>Order exists but belongs to User ID: " . $order2['user_id'] . " (Current User: $user_id)</p>";
            } else {
                echo "<p>Order ID $order_id does not exist in database at all</p>";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>

