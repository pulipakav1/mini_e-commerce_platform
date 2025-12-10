<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

/* --------------------------
   SAVE PAYMENT METHOD
---------------------------*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $method = $_POST['method'];

    if ($method == "Cash") {
        // Schema has 'payment' table not 'payment_methods', and it requires order_id
        // Payment is linked to orders, not users directly
        // For now, just save method preference (would need order_id for actual payment)
        // Remove card storage completely as per requirements
        $message = "Payment method 'Cash on Delivery' will be used for your orders.";

    // Note: Payment table in schema requires order_id, not user_id
    // This is just for preference display
}

// No need to fetch payment method as it's per order
$last = null;
?>
<!DOCTYPE html>
<html>
<head>
<title>Payment Methods</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: Arial; background:#f5f5f5; padding:20px; }

.container {
    background:#fff;
    padding:20px;
    border-radius:14px;
    box-shadow:0 3px 8px rgba(0,0,0,0.1);
    max-width:450px;
    margin:auto;
}

h2 { text-align:center; }

input, select {
    width:100%;
    padding:10px;
    margin:8px 0;
    border:1px solid #ccc;
    border-radius:8px;
}

button {
    width:100%;
    padding:12px;
    background:#1d4ed8;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.card-fields { display:none; }
.success { color:green; font-weight:bold; text-align:center; }
</style>

</head>
<body>

<div class="container">
    <h2>Payment Methods</h2>

    <?php if ($message): ?>
        <p class="success"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Select Payment Method:</label>
        <select name="method" id="method">
            <option value="Cash">Cash on Delivery</option>
        </select>

        <button type="submit">Save Method</button>
    </form>

    <hr>

    <h3>Payment Information</h3>
    <p>All orders will use <strong>Cash on Delivery</strong> payment method.</p>
    <p>Payment details will be recorded when you place an order.</p>
</div>

</body>
</html>
