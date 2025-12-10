<?php
$host = "localhost";
$user = "jampav1_rohit"; 
$pass = "Rohith@7890";  
$dbname = "jampav1_toronto";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
