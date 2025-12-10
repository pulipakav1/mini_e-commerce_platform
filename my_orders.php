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
            while ($order = $result_orders->fetch_assoc()) {
                $items_stmt = $conn->prepare("SELECT oi.quantity, oi.unit_price, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                $items_stmt->bind_param("i", $order['order_id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                $receipt_stmt = $conn->prepare("SELECT receipt_number FROM receipts WHERE order_id = ?");
                $receipt_stmt->bind_param("i", $order['order_id']);
                $receipt_stmt->execute();
                $receipt_result = $receipt_stmt->get_result();
                $receipt = $receipt_result->fetch_assoc();
                $receipt_number = $receipt ? $receipt['receipt_number'] : 'N/A';
                
                echo "<tr style='background:#f0f0f0; font-weight:bold;'>";
                echo "<td colspan='6' style='padding:15px; border:2px solid #1d4ed8;'>";
                echo "Order #" . htmlspecialchars($order['order_id']) . " | ";
                echo "Receipt: " . htmlspecialchars($receipt_number) . " | ";
                echo "Date: " . date('F j, Y g:i A', strtotime($order['order_date'])) . " | ";
                echo "Total: $" . number_format($order['total_amount'], 2);
                echo "</td>";
                echo "</tr>";
                
                if ($items_result->num_rows > 0) {
                    while ($item = $items_result->fetch_assoc()) {
                        echo "<tr>
                                <td>-</td>
                                <td>".htmlspecialchars($item['product_name'])."</td>
                                <td>$".number_format($item['unit_price'], 2)."</td>
                                <td>".htmlspecialchars($item['quantity'])."</td>
                                <td>Completed</td>
                                <td>-</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No items</td></tr>";
                }
                
                echo "<tr><td colspan='6' style='height:20px; border:none;'></td></tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No orders</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>
