<?php
session_start();
include 'db.php';

// If the user is not logged in → redirect to login
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
            <th>Order Date</th>
            <th>Receipt Number</th>
            <th>Total Amount</th>
            <th>Products</th>
            <th>Status</th>
        </tr>

        <?php
        if ($result_orders->num_rows > 0) {
            while ($order = $result_orders->fetch_assoc()) {
                // Get receipt number
                $receipt_stmt = $conn->prepare("SELECT receipt_number FROM receipts WHERE order_id = ?");
                $receipt_stmt->bind_param("i", $order['order_id']);
                $receipt_stmt->execute();
                $receipt_result = $receipt_stmt->get_result();
                $receipt = $receipt_result->fetch_assoc();
                $receipt_number = $receipt ? $receipt['receipt_number'] : 'N/A';
                $receipt_stmt->close();
                
                // Get order items
                $items_stmt = $conn->prepare("SELECT oi.quantity, oi.unit_price, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                $items_stmt->bind_param("i", $order['order_id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                // Build products list
                $products_list = array();
                while ($item = $items_result->fetch_assoc()) {
                    $products_list[] = htmlspecialchars($item['product_name']) . " (Qty: " . $item['quantity'] . ")";
                }
                $items_stmt->close();
                
                $products_display = !empty($products_list) ? implode(", ", $products_list) : "No products";
                $order_date_formatted = $order['order_date'] ? date('F j, Y g:i A', strtotime($order['order_date'])) : 'N/A';
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($order['order_id']) . "</td>";
                echo "<td>" . htmlspecialchars($order_date_formatted) . "</td>";
                echo "<td>" . htmlspecialchars($receipt_number) . "</td>";
                echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($products_display) . "</td>";
                echo "<td>Completed</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No orders found</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>
