<?php
session_start(); // Start the session

// Destroy the session
session_unset();  // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to the login page
header("Location: auth.php");  // Redirect to login.php (or adjust path if needed)
exit();
?>
