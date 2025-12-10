<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = ""; // To display success/error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "New password and confirm password do not match!";
    } else {
        // Fetch user password (using user_id as per schema)
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row || !isset($row['password']) || !password_verify($current, $row['password'])) {
            $message = "Current password is incorrect!";
        } else {
            // Update password
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $update_stmt->bind_param("si", $new_hash, $user_id);
            $update_stmt->execute();
            $message = "Password updated successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>

<style>
body { font-family: Arial, sans-serif; background:#f5f5f5; text-align:center; padding:50px; }
.change-box { 
    background:#fff; 
    display:inline-block; 
    padding:20px; 
    border-radius:12px; 
    box-shadow:0 5px 15px rgba(0,0,0,0.1); 
    width:300px; 
}
input { 
    width:90%; 
    padding:8px; 
    margin:10px 0; 
    border-radius:5px; 
    border:1px solid #ccc; 
}
button { 
    padding:10px 15px; 
    background:#1d4ed8; 
    color:#fff; 
    border:none; 
    border-radius:6px; 
    cursor:pointer; 
}
button:hover { background:#2563eb; }
.message { 
    margin-bottom:10px; 
    font-weight:bold; 
}
.success { color:green; }
.error { color:red; }
a { color:#1d4ed8; text-decoration:none; font-size:14px; }
</style>
</head>

<body>

<div class="change-box">
    <h3>Change Password</h3>

    <!-- Show PHP Message -->
    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'incorrect') !== false || strpos($message, 'do not match') !== false) ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>

        <?php if (strpos($message, 'successfully') !== false): ?>
            <a href="profile.php">Go Back to Profile</a>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="password" name="current_password" placeholder="Current Password" required><br>
        <input type="password" name="new_password" placeholder="New Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>
