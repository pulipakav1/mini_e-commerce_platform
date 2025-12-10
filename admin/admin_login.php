<?php
session_start(); // Start the session
include '../db.php'; // Ensure the path to db.php is correct

$error = ""; // Variable to store login error message

// Handle admin login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $admin_userid = trim($_POST['admin_userid']); // Get the admin_userid from the form

    // Prepare SQL statement to check if the admin exists
    $sql = "SELECT * FROM admins WHERE admin_userid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Failed: " . $conn->error);
    }
    $stmt->bind_param("s", $admin_userid); // Bind admin_userid to the query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) { // Check if the admin exists
        $row = $result->fetch_assoc(); // Fetch admin data

        // Set session variables for admin info
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_userid'] = $row['admin_userid'];
        $_SESSION['admin_role'] = $row['role']; // e.g., employee, manager, owner
        if (isset($row['admin_name'])) {
            $_SESSION['admin_name'] = $row['admin_name']; // Store the admin's name
        }
        if (isset($row['admin_email'])) {
            $_SESSION['admin_email'] = $row['admin_email']; // Store the admin's email
        }


        // Redirect to the dashboard page
        header("Location: dashboard.php");
        exit(); // Ensure the script stops after the redirect
    } else {
        $error = "Admin not found!"; // Set error if admin does not exist
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
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
        input[type="text"] {
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
    <h2>Admin Login</h2>

    <?php if ($error != "") { echo '<div class="error">'.$error.'</div>'; } ?>

    <form method="POST">
        <input type="text" name="admin_userid" placeholder="Admin User ID" required>
        <div class="forgot">
            <a href="admin_forgot_password.php">Forgot Password?</a>
        </div>
        <button type="submit" name="admin_login">Login</button>
    </form>

    <div class="link">
        <a href="../login.php">Back to Customer Login</a>
    </div>
</div>

</body>
</html>
