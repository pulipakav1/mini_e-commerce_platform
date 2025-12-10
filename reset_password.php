<?php
session_start();
include 'db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "Passwords do not match!";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        // Note: If email field doesn't exist, need to use user_id or user_name instead
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();
        unset($_SESSION['reset_email']);

        // Redirect with success message
        header("Location: login.php?reset_success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>

<!-- SIMPLE MODERN UI STYLE -->
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef3ff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        width: 350px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
        text-align: center;
    }
    h2 {
        color: #333;
        margin-bottom: 10px;
    }
    input {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
        border-radius: 5px;
        border: 1px solid #999;
        font-size: 15px;
    }
    button {
        margin-top: 15px;
        width: 100%;
        padding: 12px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover { background: #1e7d33; }
    a {
        display: block;
        margin-top: 15px;
        text-decoration: none;
        color: #1877f2;
        font-size: 14px;
    }
    .message {
        margin-top: 10px;
        color: red;
        font-weight: bold;
    }
</style>

</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <p>Create a new password for your account.</p>

    <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>

    <form method="POST">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Reset Password</button>
    </form>

    <a href="login.php">Back to Login</a>
</div>

</body>
</html>
