<?php
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "sexybodyfitnessgym";

// Create connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>