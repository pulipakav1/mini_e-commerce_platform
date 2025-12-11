<?php
session_start(); // Start the session
include 'db.php'; // Ensure the path to db.php is correct

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// If user is logged in, redirect to login (they need to logout first)
if (isset($_SESSION['user_id'])) {
        header("Location: auth.php");
    exit();
}

$error = ""; // Variable to store login error message

// Handle admin login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $admin_userid = trim($_POST['admin_userid']); // Get the admin_userid from the form
    $password = $_POST['password']; // Get the password from the form

    // Prepare SQL statement to check if the employee exists (using employees table)
    $sql = "SELECT * FROM employees WHERE employee_userid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Failed: " . $conn->error);
    }
    $stmt->bind_param("s", $admin_userid); // Bind employee_userid to the query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) { // Check if the employee exists
        $row = $result->fetch_assoc(); // Fetch employee data

        // Verify password
        if (isset($row['employee_password']) && password_verify($password, $row['employee_password'])) {
            // Set session variables for employee info
            $_SESSION['admin_id'] = $row['employee_id'];
            $_SESSION['admin_userid'] = $row['employee_userid'];
            $_SESSION['admin_role'] = $row['employee_type']; // Sets role: owner, business_manager, or inventory_manager
            if (isset($row['email'])) {
                $_SESSION['admin_email'] = $row['email']; // Store the employee's email
            }

            // All employees redirect to dashboard, but dashboard shows different views based on role
            // - owner: Full access (Products, Orders, HR, Reports)
            // - business_manager: Products, Orders, HR
            // - inventory_manager: Products only
            header("Location: dashboard.php");
            exit(); // Ensure the script stops after the redirect
        } else {
            // Debug: Check if password hash exists and is valid format
            if (empty($row['employee_password'])) {
                $error = "Employee account has no password set. Please contact administrator.";
            } elseif (strpos($row['employee_password'], '$2y$') !== 0) {
                $error = "Password format error. Please run fix_employee_passwords.php to update passwords.";
            } else {
                $error = "Incorrect password!";
            }
        }
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
        .forgot {
            text-align: right;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .forgot a {
            color: #1877f2;
            text-decoration: none;
        }
        .forgot a:hover {
            text-decoration: underline;
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
        <input type="text" name="admin_userid" placeholder="Employee User ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="admin_login">Login</button>
    </form>

    <div class="link">
        <a href="auth.php">Back to Customer Login</a>
    </div>
</div>

</body>
</html>
