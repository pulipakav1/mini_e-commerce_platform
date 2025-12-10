<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user addresses (stored directly in users table)
$stmt = $conn->prepare("SELECT shipping_address, billing_address, phone_number FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    $billing_address = trim($_POST['billing_address']);
    $phone_number = trim($_POST['phone_number']);

    if (empty($shipping_address) || empty($billing_address) || empty($phone_number)) {
        $message = "All fields are required!";
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET shipping_address=?, billing_address=?, phone_number=? WHERE user_id=?");
        $update_stmt->bind_param("sssi", $shipping_address, $billing_address, $phone_number, $user_id);
        
        if ($update_stmt->execute()) {
            $message = "Addresses updated successfully!";
            // Refresh user data
            $stmt = $conn->prepare("SELECT shipping_address, billing_address, phone_number FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $message = "Failed to update addresses!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Addresses</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: Arial; background:#f5f5f5; margin:0; padding:20px; }
.header { display:flex; align-items:center; margin-bottom:20px; }
.back-btn { font-size:18px; padding:6px 12px; background:#1d4ed8; color:#fff; text-decoration:none; border-radius:6px; margin-right:12px; }
.back-btn:hover { background:#2563eb; }
h2 { color:#1d4ed8; }
.container { background:#fff; padding:25px; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.1); max-width:600px; margin:auto; }
textarea, input { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; }
textarea { min-height:80px; }
button { padding:12px 20px; background:#1d4ed8; color:#fff; border:none; border-radius:8px; cursor:pointer; margin-top:10px; }
button:hover { background:#2563eb; }
.message { margin-bottom:15px; padding:10px; border-radius:6px; text-align:center; }
.success { background:#d4edda; color:#155724; }
.error { background:#f8d7da; color:#721c24; }
</style>
</head>
<body>

<div class="header">
    <a href="profile.php" class="back-btn">Back to Profile</a>
    <h2>Edit Addresses</h2>
</div>

<div class="container">
    <?php if ($message != ""): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Phone Number:</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>

        <label>Shipping Address:</label>
        <textarea name="shipping_address" required><?php echo htmlspecialchars($user['shipping_address'] ?? ''); ?></textarea>

        <label>Billing Address:</label>
        <textarea name="billing_address" required><?php echo htmlspecialchars($user['billing_address'] ?? ''); ?></textarea>

        <button type="submit">Update Addresses</button>
    </form>
</div>

</body>
</html>

