<?php
$host = "localhost:3306";
$user = "amudalj1_Jithu"; 
$pass = "Jithu@123";  
$dbname = "amudalj1_Jithu";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
//  else {
//     echo "Database connected!";
// }
?>
