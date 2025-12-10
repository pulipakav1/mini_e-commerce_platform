<?php
session_start(); // Start the session
include 'db.php'; // Include the database connection

// If user is already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// If admin is already logged in, redirect to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

$error = ""; // Variable to store login error message

// Check if the form is submitted for normal user login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_login'])) {
    $username = $_POST['username']; // Get the username from form
    $password = $_POST['password']; // Get the password from form

    // SQL query to find user by user_name
    $sql = "SELECT * FROM users WHERE user_name=?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("s", $username); // Bind the username safely
    $stmt->execute(); // Execute the query
    $result = $stmt->get_result(); // Get the result of the query

    if ($result->num_rows == 1) { // Check if exactly one user is found
        $row = $result->fetch_assoc(); // Fetch user data

        // Verify password hash (assuming password field exists - if not, schema needs update)
        if (isset($row['password']) && password_verify($password, $row['password'])) {
            // Set session variables for the logged-in user
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['user_name'];

            // Redirect to the user home page (dashboard.php)
            header("Location: home.php");
            exit();
        } else {
            $error = "Incorrect password!"; // Set error if password is wrong
        }
    } else {
        $error = "User not found!"; // Set error if user does not exist
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 50px 40px;
            width: 450px;
            border-radius: 12px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            font-size: 28px;
            margin-bottom: 35px;
            color: #333;
        }
        form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        input:focus {
            border-color: #1877f2;
            outline: none;
        }
        .forgot {
            text-align: right;
            margin-bottom: 25px;
        }
        .forgot a {
            font-size: 14px;
            color: #555;
            text-decoration: none;
        }
        button {
            width: 100%;
            padding: 16px;
            background-color: #1877f2;
            color: #fff;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0d62d2;
        }
        .link {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
        }
        .link a {
            text-decoration: none;
            color: #1877f2;
            font-weight: bold;
        }
        @media (max-width: 500px) {
            .login-container {
                width: 90%;
                padding: 40px 20px;
            }
        }
        .error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">

    <h2>Login</h2>

    <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success') { ?>
        <div style="color: #155724; margin-bottom: 20px; text-align: center; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; font-weight: bold;">
            Account created successfully! Please login with your credentials.
        </div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <!-- Normal User Login Form -->
    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="user_login">Login</button>
    </form>

    <div class="link">
        Don't have an account?
        <a href="signup.php">Create one</a>
    </div>

    <!-- Admin Login Option -->
    <div style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="color: #666; margin-bottom: 10px; font-size: 14px;">Employee Login</p>
        <a href="admin/admin_login.php" style="
            width: 100%;
            padding: 12px;
            background-color: #34a853;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
        ">Login as Admin</a>
    </div>

</div>

</body>
</html>
