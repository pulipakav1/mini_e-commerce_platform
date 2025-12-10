<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logged Out</title>
<style>
body { font-family: Arial; background:#f5f5f5; text-align:center; padding:50px; }
.message-box { background:#fff; display:inline-block; padding:20px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.1);}
a { display:inline-block; margin-top:15px; text-decoration:none; color:#fff; background:#1d4ed8; padding:8px 16px; border-radius:6px; }
</style>
<!-- Redirect after 3 seconds -->
<meta http-equiv="refresh" content="3;url=login.php">
</head>
<body>
<div class="message-box">
    <h2>You have logged out successfully</h2>
    <p>Redirecting to login page...</p>
    <a href="login.php">Login Again</a>
</div>
</body>
</html>
