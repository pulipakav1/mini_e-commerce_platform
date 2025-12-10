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
        $sql = "INSERT INTO payment_methods (user_id, method) 
                VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $method);
    } else {
        $card = $_POST['card_number'];
        $expiry = $_POST['expiry'];
        $cvv = $_POST['cvv'];

        $sql = "INSERT INTO payment_methods (user_id, method, card_number, expiry, cvv)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $method, $card, $expiry, $cvv);
    }

    if ($stmt->execute()) {
        $message = "Payment method saved successfully!";
    } else {
        $message = "Error saving payment method.";
    }
}

/* --------------------------
   FETCH LAST PAYMENT METHOD
---------------------------*/
$pm = $conn->prepare("
    SELECT method, card_number, expiry 
    FROM payment_methods 
    WHERE user_id = ? ORDER BY id DESC LIMIT 1
");
$pm->bind_param("i", $user_id);
$pm->execute();
$last = $pm->get_result()->fetch_assoc();
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

<script>
function toggleCardFields() {
    let method = document.getElementById("method").value;
    document.querySelector(".card-fields").style.display = 
        (method === "Card") ? "block" : "none";
}
</script>
</head>
<body>

<div class="container">
    <h2>Payment Methods</h2>

    <?php if ($message): ?>
        <p class="success"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Select Payment Method:</label>
        <select name="method" id="method" onchange="toggleCardFields()">
            <option value="Cash">Cash on Delivery</option>
            <option value="Card">Card Payment</option>
        </select>

        <!-- CARD DETAILS -->
        <div class="card-fields">
            <input type="text" name="card_number" placeholder="Card Number">
            <input type="text" name="expiry" placeholder="MM/YY">
            <input type="text" name="cvv" placeholder="CVV">
        </div>

        <button type="submit">Save Method</button>
    </form>

    <hr>

    <h3>Last Used Method</h3>
    <?php if ($last): ?>
        <p><strong>Method:</strong> <?= $last['method'] ?></p>
        <?php if ($last['method'] == "Card"): ?>
            <p><strong>Card:</strong> <?= "**** **** **** " . substr($last['card_number'], -4) ?></p>
            <p><strong>Expiry:</strong> <?= $last['expiry'] ?></p>
        <?php endif; ?>
    <?php else: ?>
        <p>No payment method saved yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
