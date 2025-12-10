<?php
session_start();
include 'db.php';

// If the user is not logged in → redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for this user
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: auto;
            margin-top: 50px;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #aaa;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: #333;
            color: white;
        }

        .back-btn {
            background: #333;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Order History</h2>

    <a href="home.php" class="back-btn">Back to Home</a>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Price ($)</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Order Date</th>
        </tr>

        <?php
        if ($result_orders->num_rows > 0) {
            // Orders table has order_id, user_id, order_date, shipping_address, billing_address, total_amount
            // Need to join with order_items to get product details
            while ($order = $result_orders->fetch_assoc()) {
                // Fetch order items for this order
                $items_stmt = $conn->prepare("SELECT oi.quantity, oi.unit_price, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                $items_stmt->bind_param("i", $order['order_id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                if ($items_result->num_rows > 0) {
                    while ($item = $items_result->fetch_assoc()) {
                        echo "<tr>
                                <td>".htmlspecialchars($order['order_id'])."</td>
                                <td>".htmlspecialchars($item['product_name'])."</td>
                                <td>$".number_format($item['unit_price'], 2)."</td>
                                <td>".htmlspecialchars($item['quantity'])."</td>
                                <td>Completed</td>
                                <td>".htmlspecialchars($order['order_date'])."</td>
                              </tr>";
                    }
                } else {
                    // If no items, show order header
                    echo "<tr>
                            <td>".htmlspecialchars($order['order_id'])."</td>
                            <td colspan='4'>No items in this order</td>
                            <td>".htmlspecialchars($order['order_date'])."</td>
                          </tr>";
                }
            }
        } else {
            echo "<tr><td colspan='6'>No orders found</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>
