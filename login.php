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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 50px 45px;
            width: 450px;
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
        form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 16px 18px;
            margin-bottom: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        input:focus {
            border-color: #667eea;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
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
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        button:active {
            transform: translateY(0);
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
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.3)'">Login as Admin</a>
    </div>

</div>

</body>
</html>
