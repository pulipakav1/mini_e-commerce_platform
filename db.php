<?php
// Database connection file
// This file is only meant to be included by other PHP files

// If accessed directly (not included), redirect to login
// When included, $_SERVER['PHP_SELF'] will be the including file, not db.php
if (basename($_SERVER['PHP_SELF']) == 'db.php' || 
    (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) == 'db.php')) {
    session_start();
    // Always redirect to login if accessed directly
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "jampav1_rohit"; 
$pass = "Rohith@7890";  
$dbname = "jampav1_toronto";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
