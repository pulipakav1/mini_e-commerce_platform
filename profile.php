<?php
session_start();
include "db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* ------------------------------------
   FETCH USER DATA (matching schema: user_id, name)
-------------------------------------*/
$sql = "SELECT name FROM users WHERE user_id = ?";
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
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'No email';

// Use default profile picture (profile upload not implemented)
$profile_pic = "default.png";

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
    .profile-info img { width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #1d4ed8; margin-right:12px; cursor:pointer; }
    .logout-btn { background:#e63946; padding:8px 14px; color:#fff; border:none; border-radius:8px; cursor:pointer; }
    .profile-section { margin:20px; }
    .profile-card { background:#fff; padding:18px 20px; border-radius:14px; box-shadow:0 3px 8px rgba(0,0,0,0.08); margin-bottom:12px; display:flex; justify-content:space-between; cursor:pointer; }
    .bottom-menu { position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:90%; background:#fff; display:flex; justify-content:space-around; padding:12px 0; border-radius:30px; box-shadow:0 8px 20px rgba(0,0,0,0.1);}
    .bottom-menu a { color:#777; text-decoration:none; text-align:center; font-size:18px;}
    .active { color:#1d4ed8 !important; }
</style>
</head>
<body>

<!-- TOP SECTION -->
<div class="profile-top">
    <div class="profile-info">

        <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile Picture">

        <div>
            <div style="font-weight:bold;"><?php echo $name; ?></div>
            <div style="font-size:13px; color:#555;"><?php echo $email; ?></div>
        </div>
    </div>

    <button class="logout-btn" onclick="location.href='logout.php'">Logout</button>
</div>

<!-- MENU OPTIONS -->
<div class="profile-section">
    <div class="profile-card" onclick="location.href='my_orders.php'">
        <span>My Orders</span>
    </div>
</div>

<!-- BOTTOM MENU -->
<div class="bottom-menu">
    <a href="home.php"><br>Home</a>
    <a href="profile.php" class="active"><br>Profile</a>
</div>

</body>
</html>
