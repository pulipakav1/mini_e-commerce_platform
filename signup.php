
<?php
include 'db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $billing_address = trim($_POST['billing_address']);
    $shipping_address = trim($_POST['shipping_address']);

    // Password match check
    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    }

    // Check if username exists (using user_name as per schema)
    if ($error == "") {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $error = "Username already exists!";
        }
        $stmt->close();
    }

    // Insert if no error (matching tables.sql schema: user_id, user_name, name, role, phone_number, shipping_address, billing_address)
    // Note: Schema shows no email/password fields - if these exist in actual DB, add them
    if ($error == "") {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'client'; // Default role as per schema

        // Schema has: user_id (auto), user_name, name, role, phone_number, shipping_address, billing_address
        // If actual DB has email/password fields, uncomment and add to INSERT
        $stmt = $conn->prepare(
            "INSERT INTO users (user_name, name, role, phone_number, shipping_address, billing_address, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssssss", $username, $fullname, $role, $phone, $shipping_address, $billing_address, $email, $hashed);

        if ($stmt->execute()) {
            // Redirect to login page after successful signup
            header("Location: login.php?signup=success");
            exit();
        } else {
            $error = "Something went wrong!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Signup Page</title>
<style>
/* minimal styling kept clean */
body {
    font-family: Arial, sans-serif;
    background: #e9f0fc;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: white;
    padding: 30px;
    width: 400px;
    max-width: 90%;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
}
input, textarea, button {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial, sans-serif;
}
button {
    background: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
}
.msg { text-align: center; font-weight: bold; }
.error { color: red; }
.success { color: green; }
</style>
</head>

<body>
<div class="container">
    <h2>Create Account</h2>

    <?php if($error != "") { echo "<div class='msg error'>$error</div>"; } ?>

    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <textarea name="billing_address" placeholder="Billing Address" rows="3" required></textarea>
        <textarea name="shipping_address" placeholder="Shipping Address" rows="3" required></textarea>

        <button type="submit">Sign Up</button>
    </form>

    <p style="text-align:center;">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>
</body>
</html>

