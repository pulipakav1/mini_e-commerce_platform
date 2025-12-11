<?php
session_start();
include 'db.php';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
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
    <meta http-equiv="refresh" content="3;url=auth.php">
    </head>
    <body>
    <div class="message-box">
        <h2>You have logged out successfully</h2>
        <p>Redirecting to login page...</p>
        <a href="auth.php">Login Again</a>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// If user is already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$action = $_GET['action'] ?? 'login'; // Default to login
$error = "";
$success = "";

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    ob_start();
    
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $billing_address = trim($_POST['billing_address']);
    $shipping_address = trim($_POST['shipping_address']);

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    }

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

    if ($error == "") {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'client';

        $stmt = $conn->prepare(
            "INSERT INTO users (user_name, name, role, phone_number, shipping_address, billing_address, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssssss", $username, $fullname, $role, $phone, $shipping_address, $billing_address, $email, $hashed);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            header("Location: auth.php?signup=success");
            exit();
        } else {
            $error = "Registration failed: " . $stmt->error;
            $stmt->close();
        }
    }
    
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE user_name=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (isset($row['password']) && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['user_name'];

            header("Location: home.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}

// Check for signup success message
if (isset($_GET['signup']) && $_GET['signup'] == 'success') {
    $success = "Account created successfully! Please login with your credentials.";
    $action = 'login'; // Switch to login view after successful signup
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action == 'signup' ? 'Sign Up' : 'Login'; ?> - Flower Shop</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .auth-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 50px 45px;
            width: 450px;
            max-width: 90%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.3);
        }
        h2 {
            font-size: 32px;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            letter-spacing: -1px;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            width: 100%;
        }
        .tab {
            flex: 1;
            padding: 12px;
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s ease;
        }
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        input[type="text"], input[type="password"], input[type="email"], textarea {
            width: 100%;
            padding: 16px 18px;
            margin-bottom: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f9fafb;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        input:focus, textarea:focus {
            border-color: #667eea;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        .link {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
        }
        .link a {
            text-decoration: none;
            color: #667eea;
            font-weight: 600;
            transition: color 0.2s;
        }
        .link a:hover {
            color: #764ba2;
        }
        .error {
            color: #ef4444;
            margin-bottom: 20px;
            text-align: center;
            padding: 12px;
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 8px;
            width: 100%;
        }
        .success {
            color: #155724;
            margin-bottom: 20px;
            text-align: center;
            padding: 12px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            width: 100%;
        }
        .employee-section {
            margin-top: 20px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            width: 100%;
        }
        .employee-section p {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .employee-section a {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        .employee-section a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
    </style>
</head>
<body>

<div class="auth-container">

    <h2><?php echo $action == 'signup' ? 'Sign Up' : 'Login'; ?></h2>

    <!-- Tabs for switching between login and signup -->
    <div class="tabs">
        <button class="tab <?php echo $action == 'login' ? 'active' : ''; ?>" onclick="window.location.href='auth.php'">Login</button>
        <button class="tab <?php echo $action == 'signup' ? 'active' : ''; ?>" onclick="window.location.href='auth.php?action=signup'">Sign Up</button>
    </div>

    <?php if ($success != "") { ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if ($action == 'signup'): ?>
        <!-- Signup Form -->
        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <textarea name="billing_address" placeholder="Billing Address" rows="3" required></textarea>
            <textarea name="shipping_address" placeholder="Shipping Address" rows="3" required></textarea>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        
        <div class="link">
            Already have an account?
            <a href="auth.php">Login</a>
        </div>
    <?php else: ?>
        <!-- Login Form -->
        <form method="POST">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" name="user_login">Login</button>
        </form>

        <div class="link">
            Don't have an account?
            <a href="auth.php?action=signup">Create one</a>
        </div>
    <?php endif; ?>

    <!-- Employee Login Option -->
    <div class="employee-section">
        <p>Employee Login</p>
        <p style="color: #999; margin-bottom: 15px; font-size: 12px;">For Owner, Business Manager, or Inventory Manager</p>
        <a href="admin_login.php">Login as Employee</a>
    </div>

</div>

</body>
</html>

