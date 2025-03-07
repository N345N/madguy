<?php
session_start();

// Unset user session variables
unset($_SESSION['id']);
unset($_SESSION['email']);

// Destroy the user session
session_destroy();

// Redirect to the user login page
header("Location: login.php");
exit();
?>