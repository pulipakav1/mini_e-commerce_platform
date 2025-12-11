<?php
session_start();
include 'db.php';

// If the user is not logged in → redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
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
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Receipt Number</th>
                <th>Product</th>
                <th>Price ($)</th>
                <th>Quantity</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
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
                
                $order_date_formatted = $order['order_date'] ? date('F j, Y g:i A', strtotime($order['order_date'])) : 'N/A';
                $item_count = 0;
                $total_items = $items_result->num_rows;
                
                // Display each item as a separate row, with order info in first row
                while ($item = $items_result->fetch_assoc()) {
                    $item_count++;
                    echo "<tr>";
                    
                    // First row shows order details, subsequent rows are empty for those columns
                    if ($item_count == 1) {
                        echo "<td rowspan='" . $total_items . "'>" . htmlspecialchars($order['order_id']) . "</td>";
                        echo "<td rowspan='" . $total_items . "'>" . htmlspecialchars($order_date_formatted) . "</td>";
                        echo "<td rowspan='" . $total_items . "'>" . htmlspecialchars($receipt_number) . "</td>";
                        echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                        echo "<td>$" . number_format($item['unit_price'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($item['quantity']) . "</td>";
                        echo "<td rowspan='" . $total_items . "' style='font-weight:bold;'>$" . number_format($order['total_amount'], 2) . "</td>";
                        echo "<td rowspan='" . $total_items . "'>Completed</td>";
                    } else {
                        echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                        echo "<td>$" . number_format($item['unit_price'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($item['quantity']) . "</td>";
                    }
                    
                    echo "</tr>";
                }
                
                // If no items, still show order row
                if ($total_items == 0) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['order_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($order_date_formatted) . "</td>";
                    echo "<td>" . htmlspecialchars($receipt_number) . "</td>";
                    echo "<td colspan='3'>No items</td>";
                    echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
                    echo "<td>Completed</td>";
                    echo "</tr>";
                }
                
                // Add spacing row between orders
                echo "<tr><td colspan='8' style='height:10px; border:none; background:#f9f9f9;'></td></tr>";
                
                $items_stmt->close();
            }
        } else {
            echo "<tr><td colspan='8' style='text-align:center; padding:20px;'>No orders found</td></tr>";
        }
        ?>
        </tbody>
    </table>

</div>

</body>
</html>
