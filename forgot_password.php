<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    // Note: Schema doesn't have email field - if DB has it, use this
    // If no email field, need alternative authentication method
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['reset_email'] = $email;  
        header("Location: reset_password.php");
        exit();
    } else {
        $message = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>

<!-- SIMPLE MODERN CSS FRAMEWORK -->
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
        background: #1877f2;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover { background: #1258b0; }
    .message {
        margin-top: 10px;
        color: red;
        font-weight: bold;
    }
    a {
        display: block;
        margin-top: 15px;
        text-decoration: none;
        color: #1877f2;
        font-size: 14px;
    }
</style>

</head>
<body>

<div class="container">
    <h2>Forgot Password</h2>
    <p>Enter your registered Email</p>

    <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Next</button>
    </form>

    <a href="login.php">Back to Login</a>
</div>

</body>
</html>

