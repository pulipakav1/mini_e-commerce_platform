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
    $address = trim($_POST['address']);

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
            "INSERT INTO users (user_name, name, role, phone_number, address, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $username, $fullname, $role, $phone, $address, $email, $hashed);

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
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .auth-container {
            background: #ffffff;
            padding: 30px;
            width: 420px;
            max-width: 90%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            max-height: 95vh;
            overflow-y: auto;
        }
        h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #1f2937;
            font-weight: 600;
        }
        form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        input[type="text"], input[type="password"], input[type="email"], textarea {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #ffffff;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 60px;
            max-height: 80px;
        }
        input:focus, textarea:focus {
            border-color: #1d4ed8;
            outline: none;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
        }
        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #1d4ed8;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 5px;
        }
        button[type="submit"]:hover {
            background: #1e40af;
        }
        .link {
            margin-top: 15px;
            text-align: center;
            font-size: 13px;
        }
        .link a {
            text-decoration: none;
            color: #1d4ed8;
            font-weight: 500;
            transition: color 0.2s;
        }
        .link a:hover {
            color: #1e40af;
        }
        .error {
            color: #ef4444;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 6px;
            width: 100%;
            font-size: 13px;
        }
        .success {
            color: #155724;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            width: 100%;
            font-size: 13px;
        }
        .employee-section {
            margin-top: 20px;
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            width: 100%;
        }
        .employee-section p {
            color: #6b7280;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .employee-section a {
            width: 100%;
            padding: 10px;
            background: #10b981;
            color: white;
            border: none;
            font-size: 14px;
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
            background: #059669;
        }
    </style>
</head>
<body>

<div class="auth-container">

    <h2><?php echo $action == 'signup' ? 'Create Account' : 'Login'; ?></h2>

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
            <textarea name="address" placeholder="Address" rows="3" required></textarea>
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
        <p style="color: #999; margin-bottom: 10px; font-size: 11px;">For Owner, Business Manager, or Inventory Manager</p>
        <a href="admin_login.php">Login as Employee</a>
    </div>

</div>

</body>
</html>

