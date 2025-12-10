<?php
session_start();
include '../db.php'; // Ensure path to db.php is correct

// Only owner can access
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'owner') {
    header("Location: hr.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee'])) {
    $employee_name   = trim($_POST['employee_name']);   // admin_userid
    $employee_role   = trim($_POST['employee_role']);   // role
    $employee_email  = trim($_POST['employee_email']);  // email
    $employee_salary = trim($_POST['employee_salary']); // salary

    // Default password
    $default_password = password_hash('ChangeMe123', PASSWORD_DEFAULT);

    // INSERT without id (auto-increment)
    $sql = "INSERT INTO admins (admin_userid, role, email, salary, admin_password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Failed: " . $conn->error);
    }

    $stmt->bind_param("sssds", $employee_name, $employee_role, $employee_email, $employee_salary, $default_password);

    if ($stmt->execute()) {
        header("Location: hr.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Employee</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #eef2f7;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 600px;
        margin: 70px auto;
        padding: 30px 40px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #1d4ed8;
        margin-bottom: 35px;
        font-size: 26px;
    }
    form input {
        width: 100%;
        padding: 14px 12px;
        margin-bottom: 20px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
    }
    form input:focus {
        border-color: #1d4ed8;
        outline: none;
        box-shadow: 0 0 5px rgba(29,78,216,0.3);
    }
    button {
        width: 100%;
        padding: 14px;
        background-color: #1d4ed8;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 17px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background-color: #0d62d2;
    }
    .back-btn {
        display: inline-block;
        margin-bottom: 25px;
        position:absolute;
            top:30px;
            left:20px;
            font-size:18px;
            text-decoration:none;
            color:#1d4ed8;
            font-weight:bold;
    }
    .back-btn:hover {
        text-decoration: underline;
    }
    .error {
        color: red;
        text-align: center;
        margin-bottom: 20px;
        font-weight: 500;
    }
    @media (max-width: 640px) {
        .container {
            padding: 25px 20px;
            margin: 40px 20px;
        }
        h2 { font-size: 22px; }
        button { font-size: 16px; }
    }
</style>
</head>
<body>
<a href="hr.php" class="back-btn">Back to HR Section</a>
<div class="container">
    <h2>Add New Employee</h2>

    <?php if(isset($error)) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>

    <form method="POST">
        <input type="text" name="employee_name" placeholder="Employee Name" required>
        <select name="employee_role" required style="width: 100%; padding: 14px 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #ccc; font-size: 15px;">
            <option value="">Select Role</option>
            <option value="inventory_manager">Inventory Manager</option>
            <option value="business_manager">Business Manager</option>
            <option value="owner">Owner</option>
        </select>
        <input type="email" name="employee_email" placeholder="Email" required>
        <input type="number" step="0.01" name="employee_salary" placeholder="Salary (USD)" required>
        <button type="submit" name="add_employee">Add Employee</button>
    </form>
</div>

</body>
</html>
