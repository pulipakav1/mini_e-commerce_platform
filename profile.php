<?php
session_start();
include "db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* ------------------------------------
   FETCH USER DATA (matching schema: user_id, name, email, phone_number, address)
-------------------------------------*/
$sql = "SELECT name, email, phone_number, address, user_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare Failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$name  = htmlspecialchars($user['name']);
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Not provided';
$phone = isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : 'Not provided';
$username = isset($user['user_name']) ? htmlspecialchars($user['user_name']) : 'Not provided';
$address = isset($user['address']) ? htmlspecialchars($user['address']) : 'Not provided';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: Arial; background:#f5f5f5; margin:0; }
    .profile-top { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
    .profile-info { display:flex; align-items:center; }
    .logout-btn { background:#e63946; padding:8px 14px; color:#fff; border:none; border-radius:8px; cursor:pointer; }
    .profile-section { margin:20px; }
    .profile-card { background:#fff; padding:18px 20px; border-radius:14px; box-shadow:0 3px 8px rgba(0,0,0,0.08); margin-bottom:12px; }
    .profile-card.clickable { display:flex; justify-content:space-between; cursor:pointer; }
    .user-details { background:#fff; padding:20px; border-radius:14px; box-shadow:0 3px 8px rgba(0,0,0,0.08); margin-bottom:20px; }
    .detail-row { display:flex; padding:12px 0; border-bottom:1px solid #eee; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { font-weight:bold; color:#666; width:150px; }
    .detail-value { color:#333; flex:1; }
</style>
</head>
<body>

<!-- TOP SECTION -->
<div class="profile-top">
    <div class="profile-info">
        <div>
            <div style="font-weight:bold; font-size:18px;"><?php echo $name; ?></div>
            <div style="font-size:13px; color:#555;"><?php echo $email; ?></div>
        </div>
    </div>

    <button class="logout-btn" onclick="location.href='auth.php?action=logout'">Logout</button>
</div>

<!-- USER DETAILS SECTION -->
<div class="profile-section">
    <div class="user-details">
        <h3 style="margin-top:0; margin-bottom:20px; color:#1d4ed8;">User Information</h3>
        
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value"><?php echo $name; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Username:</div>
            <div class="detail-value"><?php echo $username; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Email:</div>
            <div class="detail-value"><?php echo $email; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Phone:</div>
            <div class="detail-value"><?php echo $phone; ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Address:</div>
            <div class="detail-value"><?php echo nl2br($address); ?></div>
        </div>
    </div>
    
    <!-- MENU OPTIONS -->
    <div class="profile-card clickable" onclick="location.href='my_orders.php'">
        <span>My Orders</span>
    </div>
</div>

<div style="text-align: center; margin: 30px 0;">
    <button onclick="history.back();" style="padding: 10px 20px; background: #1d4ed8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">← Back</button>
</div>

</body>
</html>
