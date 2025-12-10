<?php
session_start();
include '../db.php'; // Ensure correct path

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Only owner can delete employees
$admin_role = $_SESSION['admin_role'];
if ($admin_role != 'owner') {
    header("Location: hr.php");
    exit();
}

// Check if id is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $employee_id = $_GET['id'];

    // Prepare delete query
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        // Redirect back to HR page after deletion
        header("Location: hr.php");
        exit();
    } else {
        echo "Error deleting employee: " . $stmt->error;
    }
} else {
    echo "Invalid employee ID.";
}
?>
