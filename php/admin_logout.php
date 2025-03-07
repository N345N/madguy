<?php
session_start();

// Unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);

// Redirect to the admin login page
header("Location: login.php");
exit();
?>