<?php
session_start();
include 'db.php';

$error = "";
$success = "";
$action = $_GET['action'] ?? 'list'; // Default to 'list'

// Check if employees table exists
$table_check = $conn->query("SHOW TABLES LIKE 'employees'");
$table_exists = $table_check && $table_check->num_rows > 0;

// If table doesn't exist, allow setup without login
if (!$table_exists) {
    // Handle setup
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setup'])) {
        try {
            // Step 1: Create employees table (no password, no userid)
            $create_table = "CREATE TABLE employees (
                employee_id INT AUTO_INCREMENT PRIMARY KEY,
                employee_type ENUM('inventory_manager', 'business_manager', 'owner') NOT NULL,
                salary DECIMAL(10,2),
                hire_date DATE,
                email VARCHAR(255)
            )";
            
            if (!$conn->query($create_table)) {
                throw new Exception("Error creating table: " . $conn->error);
            }
            $success .= "✓ Employees table created.<br>";
            
            // Step 2: Check if owner exists, if not create it
            $owner_check = $conn->prepare("SELECT employee_id FROM employees WHERE employee_type = 'owner'");
            $owner_check->execute();
            $owner_result = $owner_check->get_result();
            
            if ($owner_result->num_rows == 0) {
                // Create owner account (no password, no userid)
                $insert_owner = $conn->prepare("INSERT INTO employees (employee_type, email, salary, hire_date) VALUES (?, ?, ?, CURDATE())");
                $type = 'owner';
                $email = 'owner@flowershop.com';
                $salary = 100000.00;
                
                $insert_owner->bind_param("ssd", $type, $email, $salary);
                
                if ($insert_owner->execute()) {
                    $success .= "✓ Owner account created successfully!<br>";
                    $table_exists = true; // Refresh check
                } else {
                    throw new Exception("Error creating owner: " . $insert_owner->error);
                }
                $insert_owner->close();
            } else {
                $success .= "✓ Owner account already exists.<br>";
                $table_exists = true;
            }
            $owner_check->close();
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    // Show setup page if table doesn't exist
    if (!$table_exists) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Setup Employees</title>
            <style>
                body { font-family: Arial; padding: 20px; max-width: 600px; margin: 0 auto; background: #f5f5f5; }
                .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h2 { color: #1d4ed8; }
                button { padding: 12px 25px; background: #1d4ed8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; width: 100%; }
                button:hover { background: #0d62d2; }
                .message { padding: 15px; border-radius: 6px; margin: 20px 0; }
                .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
                .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
                .info { background: #e3f2fd; padding: 15px; border-radius: 6px; margin: 20px 0; }
                .credentials { background: #fff3cd; padding: 15px; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Setup Owner Account</h2>
                
                <?php if ($error): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="message success"><?php echo htmlspecialchars($success); ?></div>
                    <div class="credentials">
                        <h3>Owner Login:</h3>
                        <p><strong>Employee ID:</strong> Check the employee list to see the owner's ID</p>
                        <p><strong>Note:</strong> Login using Employee ID (number) - no password required</p>
                        <p style="margin-top: 15px;">
                            <a href="admin_login.php" style="background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;">Go to Employee Login</a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="info">
                        <p><strong>This will:</strong></p>
                        <ul>
                            <li>Create the employees table (if it doesn't exist)</li>
                            <li>Create the owner account</li>
                            <li>No password required - login with username only</li>
                        </ul>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="setup">Setup Owner Account</button>
                    </form>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// After table exists, require login
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth.php");
    exit();
}

$admin_role = $_SESSION['admin_role'];

// Allow only owner - Business managers cannot access HR
if ($admin_role != 'owner') {
    echo "<h2 style='color:red; text-align:center; padding:20px;'>Access Denied! HR Section is only for Owner.</h2>";
    echo "<p style='text-align:center;'><a href='dashboard.php'>Back to Dashboard</a></p>";
    exit();
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $employee_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    if ($stmt->execute()) {
        header("Location: hr.php?action=list");
        exit();
    } else {
        $error = "Error deleting employee: " . $stmt->error;
    }
}

// Handle add employee form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee'])) {
    $employee_role = trim($_POST['employee_role']);
    $employee_email = trim($_POST['employee_email']);
    $employee_salary = floatval($_POST['employee_salary']);

    // Insert employee data (no userid, no password)
    $sql = "INSERT INTO employees (employee_type, email, salary, hire_date) VALUES (?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssd", $employee_role, $employee_email, $employee_salary);

    if ($stmt->execute()) {
        $success = "Employee added successfully!";
        $action = 'list';
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle edit employee form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    $employee_id = intval($_POST['employee_id']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $salary = floatval($_POST['salary']);

    $update = $conn->prepare("UPDATE employees SET email=?, employee_type=?, salary=? WHERE employee_id=?");
    $update->bind_param("ssdi", $email, $role, $salary, $employee_id);
    
    if ($update->execute()) {
        $success = "Employee updated successfully!";
        $action = 'list';
    } else {
        $error = "Update failed: " . $update->error;
    }
    $update->close();
}

// Fetch employee for edit/view
$employee = null;
if (($action == 'edit' || $action == 'view') && isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    $sql = "SELECT * FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    if (!$employee) {
        $action = 'list';
        $error = "Employee not found.";
    }
}

// Fetch all employees for list
$employees_result = null;
if ($action == 'list') {
    $employees_result = $conn->query("SELECT employee_id, employee_type, email, salary FROM employees ORDER BY employee_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Section</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; margin:0; padding:0; }
        .nav-tabs { text-align:center; padding:20px; background:#fff; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        .nav-tabs a { display:inline-block; padding:10px 20px; margin:0 5px; background:#1d4ed8; color:white; text-decoration:none; border-radius:5px; }
        .nav-tabs a.active { background:#0d62d2; }
        .back-btn { position:absolute; top:20px; left:20px; font-size:18px; color:#1d4ed8; text-decoration:none; font-weight:bold; }
        .back-btn:hover { text-decoration:underline; }
        .container { max-width:900px; margin:80px auto 20px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align:center; margin-bottom:20px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:10px; text-align:center; }
        th { background:#1d4ed8; color:white; }
        tr:nth-child(even) { background:#f9f9f9; }
        .action-btn { padding:5px 10px; background:#1d4ed8; color:white; border-radius:5px; text-decoration:none; font-size:14px; margin:2px; display:inline-block; }
        .action-btn:hover { background:#0d62d2; }
        .add-btn { display:inline-block; padding:8px 15px; background:#10b981; color:white; border-radius:6px; text-decoration:none; margin-bottom:10px; }
        .add-btn:hover { background:#059669; }
        .delete-btn { background:#ef4444; color:white; padding:5px 10px; border:none; border-radius:5px; cursor:pointer; font-size:14px; }
        .delete-btn:hover { background:#dc2626; }
        input, select { width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; }
        button { padding:10px 15px; background:#1d4ed8; color:white; border:none; cursor:pointer; border-radius:6px; font-size:16px; width:100%; margin-top:10px; }
        button:hover { background:#0d62d2; }
        .error { color:red; margin-bottom:10px; text-align:center; padding:10px; background:#fee; border-radius:6px; }
        .success { color:green; margin-bottom:10px; text-align:center; padding:10px; background:#efe; border-radius:6px; }
        .detail-view { max-width:500px; margin:0 auto; }
        .detail-row { margin:15px 0; padding:10px; background:#f9f9f9; border-radius:6px; }
        .detail-label { font-weight:bold; color:#1d4ed8; }
        .salary { color: #10b981; font-weight: bold; }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<div class="nav-tabs">
    <a href="hr.php?action=list" class="<?php echo $action == 'list' ? 'active' : ''; ?>">Employee List</a>
    <a href="hr.php?action=add" class="<?php echo $action == 'add' ? 'active' : ''; ?>">Add Employee</a>
</div>

<div class="container">
    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success != ""): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h2>Add New Employee</h2>
        <form method="POST">
            <select name="employee_role" required>
                <option value="">Select Role</option>
                <option value="owner">Owner</option>
                <option value="inventory_manager">Inventory Manager</option>
                <option value="business_manager">Business Manager</option>
            </select>
            <input type="email" name="employee_email" placeholder="Email" required>
            <input type="number" step="0.01" name="employee_salary" placeholder="Salary (USD)" required>
            <button type="submit" name="add_employee">Add Employee</button>
        </form>

    <?php elseif ($action == 'edit' && $employee): ?>
        <h2>Edit Employee</h2>
        <form method="POST">
            <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
            <select name="role" required>
                <option value="owner" <?php echo ($employee['employee_type'] ?? '') == 'owner' ? 'selected' : ''; ?>>Owner</option>
                <option value="inventory_manager" <?php echo ($employee['employee_type'] ?? '') == 'inventory_manager' ? 'selected' : ''; ?>>Inventory Manager</option>
                <option value="business_manager" <?php echo ($employee['employee_type'] ?? '') == 'business_manager' ? 'selected' : ''; ?>>Business Manager</option>
            </select>
            <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>" placeholder="Email" required>
            <input type="number" step="0.01" name="salary" value="<?php echo htmlspecialchars($employee['salary'] ?? '0'); ?>" placeholder="Salary (USD)" required>
            <button type="submit" name="update_employee">Update Employee</button>
        </form>

    <?php elseif ($action == 'view' && $employee): ?>
        <div class="detail-view">
            <h2>Employee Details</h2>
            <div class="detail-row">
                <span class="detail-label">Employee ID:</span> <?php echo htmlspecialchars($employee['employee_id']); ?>
            </div>
            <div class="detail-row">
                <span class="detail-label">Role:</span> <?php echo htmlspecialchars($employee['employee_type']); ?>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span> <?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?>
            </div>
            <div class="detail-row">
                <span class="detail-label">Salary:</span> <span class="salary">$<?php echo number_format($employee['salary'] ?? 0, 2); ?></span>
            </div>
            <?php if (isset($employee['hire_date'])): ?>
            <div class="detail-row">
                <span class="detail-label">Hire Date:</span> <?php echo htmlspecialchars($employee['hire_date']); ?>
            </div>
            <?php endif; ?>
            <div style="text-align:center; margin-top:20px;">
                <a href="hr.php?action=edit&id=<?php echo $employee['employee_id']; ?>" class="action-btn">Edit</a>
                <a href="hr.php?action=list" class="action-btn">Back to List</a>
            </div>
        </div>

    <?php else: // action == 'list' ?>
        <h2>HR Section - Employee List</h2>
        <?php if ($employees_result && $employees_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Salary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $employees_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['employee_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['employee_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($row['salary'] ?? 0, 2); ?></td>
                            <td>
                                <a href="hr.php?action=view&id=<?php echo $row['employee_id']; ?>" class="action-btn">View</a>
                                <a href="hr.php?action=edit&id=<?php echo $row['employee_id']; ?>" class="action-btn">Edit</a>
                                <a href="hr.php?delete_id=<?php echo $row['employee_id']; ?>" onclick="return confirm('Are you sure you want to delete this employee?');">
                                    <button class="delete-btn">Delete</button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding:40px;">No employees found. <a href="hr.php?action=add">Add an employee</a></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>

