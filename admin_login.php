<?php
session_start();
include 'db.php';

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Check if employees table exists
$table_check = $conn->query("SHOW TABLES LIKE 'employees'");
$table_exists = $table_check && $table_check->num_rows > 0;

// If table doesn't exist, show setup message
if (!$table_exists) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Setup Required</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial;
                background: #f0f2f5;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .setup-container {
                background: #fff;
                padding: 40px;
                width: 500px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
                text-align: center;
            }
            h2 {
                color: #1d4ed8;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                margin-bottom: 25px;
                line-height: 1.6;
            }
            .setup-btn {
                display: inline-block;
                padding: 12px 30px;
                background: #1d4ed8;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-size: 15px;
            }
            .setup-btn:hover {
                background: #0d62d2;
            }
            .link {
                margin-top: 20px;
                font-size: 14px;
            }
            .link a {
                color: #1d4ed8;
                text-decoration: none;
            }
            .link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="setup-container">
            <h2>Setup Required</h2>
            <p>The employees table has not been set up yet. Please set up the owner account first before logging in.</p>
            <a href="hr.php" class="setup-btn">Go to Setup Page</a>
            <div class="link">
                <a href="auth.php">Back to Customer Login</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$error = "";

// Handle employee login (no password required, login by employee ID)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $employee_id = intval($_POST['employee_id']);

    $sql = "SELECT * FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Failed: " . $conn->error);
    }
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Login without password - just employee ID
        $_SESSION['admin_id'] = $row['employee_id'];
        $_SESSION['admin_role'] = $row['employee_type'];
        if (isset($row['email'])) {
            $_SESSION['admin_email'] = $row['email'];
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Employee not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
        }
        button:hover {
            background: #0d62d2;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .link a {
            color: #1d4ed8;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Employee Login</h2>
    <p style="text-align:center; color:#666; margin-bottom:20px; font-size:14px;">Login for Owner, Business Manager, or Inventory Manager</p>

    <?php if ($error != "") { echo '<div class="error">'.$error.'</div>'; } ?>

    <form method="POST">
        <input type="number" name="employee_id" placeholder="Enter Employee ID" required>
        <button type="submit" name="admin_login">Login</button>
    </form>

    <div class="link">
        <a href="auth.php">Back to Customer Login</a>
    </div>
</div>

</body>
</html>
