<?php
session_start();
include '../db.php';

// Only owner and business_manager can edit
if (!isset($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'], ['owner', 'business_manager'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid employee ID.");
}

$admin_id = intval($_GET['id']);

// Fetch employee
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
$employee = $res->fetch_assoc();

if (!$employee) {
    die("Employee not found.");
}

// Update employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = trim($_POST['userid']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $salary = floatval($_POST['salary']);

    $update = $conn->prepare("UPDATE admins SET admin_userid=?, email=?, role=?, salary=? WHERE id=?");
    $update->bind_param("sssdi", $userid, $email, $role, $salary, $admin_id);
    
    if ($update->execute()) {
        header("Location: hr.php");
        exit();
    } else {
        $error = "Update failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Employee</title>
<style>
body { font-family: Arial; background:#f5f5f5; padding:20px; }
.container { background:white; width:450px; margin:auto; padding:20px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
input, select { width:100%; padding:10px; margin:10px 0; }
button { padding:10px 15px; background:#1d4ed8; color:white; border:none; cursor:pointer; }
.back-btn { color:#1d4ed8; text-decoration:none; }
.error { color:red; margin-bottom:10px; text-align:center; }
</style>
</head>
<body>

<a href="hr.php" class="back-btn">Back to HR section</a>

<div class="container">
<h2>Edit Employee</h2>

<?php if(isset($error)) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>

<form method="POST">
    <label>User ID</label>
    <input type="text" name="userid" value="<?= htmlspecialchars($employee['admin_userid']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>

    <label>Role</label>
    <select name="role" required>
        <option value="inventory_manager" <?= $employee['role']=='inventory_manager'?'selected':'' ?>>Inventory Manager</option>
        <option value="business_manager" <?= $employee['role']=='business_manager'?'selected':'' ?>>Business Manager</option>
        <option value="owner" <?= $employee['role']=='owner'?'selected':'' ?>>Owner</option>
    </select>

    <label>Salary (USD)</label>
    <input type="number" step="0.01" name="salary" value="<?= htmlspecialchars($employee['salary']) ?>" required>

    <button type="submit">Update Employee</button>
</form>
</div>

</body>
</html>
