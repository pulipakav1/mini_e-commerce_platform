<?php
// Connect to your database
$host = "localhost:3306";
$user = "amudalj1_Jithu"; 
$pass = "Jithu@123";  
$dbname = "amudalj1_Jithu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin credentials
$admin_userid = 'admin';  // Admin username
$admin_password = 'password123';  // Admin plain password
$role = 'owner';  // Admin role

// Hash the password before storing it
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Insert the admin into the database
$sql = "INSERT INTO admins (admin_userid, admin_password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $admin_userid, $hashed_password, $role);

if ($stmt->execute()) {
    echo "Admin user added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
