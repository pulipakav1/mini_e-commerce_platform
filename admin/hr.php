<?php
session_start();
include '../db.php'; // correct db path

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Logged-in admin role
$admin_role = $_SESSION['admin_role']; // owner, business_manager, inventory_manager

// Allow only owner and business_manager
if ($admin_role != 'owner' && $admin_role != 'business_manager') {
    echo "<h2 style='color:red; text-align:center;'>Access Denied! HR Section is only for Owner and Business Manager.</h2>";
    exit();
}

// Fetch employees
$sql = "SELECT id, admin_userid, role, email FROM admins";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Section</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; margin:0; padding:0; }

        .back-btn {
            position:absolute;
            top:20px;
            left:20px;
            font-size:18px;
            text-decoration:none;
            color:#1d4ed8;
            font-weight:bold;
        }
        .back-btn:hover {  
        text-decoration: underline;
    } 

        .container {
            max-width:900px;
            margin:80px auto;
            background:#fff;
            padding:25px;
            border-radius:12px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
        }

        h2 { text-align:center; margin-bottom:20px; }

        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:10px; text-align:center; }
        th { background:#1d4ed8; color:white; }

        tr:nth-child(even) { background:#f9f9f9; }

        .action-btn {
            padding:5px 10px;
            background:#1d4ed8;
            color:white;
            border-radius:5px;
            text-decoration:none;
            font-size:14px;
        }
        .action-btn:hover { background:#0d62d2; }

        .add-btn {
            display:inline-block;
            padding:8px 15px;
            background:#10b981;
            color:white;
            border-radius:6px;
            text-decoration:none;
            margin-bottom:10px;
        }
        .add-btn:hover { background:#059669; }

    </style>
</head>
<body>

<a href="dashboard.php" class="back-btn">Back to Dashboard</a>

<div class="container">
    <h2>HR Section</h2>

    <!-- Add Employee Only for Owner -->
    <?php if ($admin_role == 'owner') { ?>
        <a href="add_employee.php" class="add-btn">Add New Employee</a>
    <?php } ?>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['admin_userid']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                 <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <a href="view_employee.php?id=<?php echo $row['id']; ?>" class="action-btn">View</a>
                    <?php if ($admin_role == 'owner') { ?>
                        <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="action-btn">Edit</a>
                        <a href="delete_employee.php?id=<?php echo $row['id']; ?>" class="action-btn"
                           onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>

    </table>
</div>

</body>
</html>
