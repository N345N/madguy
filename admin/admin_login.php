<?php
session_start();
include("../php/database.php");

// Establish database connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Initialize error variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email and password are provided
    if(isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Fetch admin details from the database
        $stmt = $conn->prepare("SELECT id, admin_email, admin_password FROM admins WHERE admin_email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $dbEmail, $dbPassword);

            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                if (password_verify($password, $dbPassword)) {
                    // Password is correct, set session variables for admin
                    $_SESSION['admin_id'] = $id;
                    $_SESSION['admin_email'] = $dbEmail;
                    $stmt->close();
                    $conn->close();
                    header("Location: dashboard.php");
                    exit(); // Exit the script after redirection
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No admin found with that email.";
            }

            $stmt->close();
        } else {
            $error = "Error in SQL statement: " . $conn->error;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../admin/css/admin_login.css">
</head>
<body>
    <form class="box" action="admin_login.php" method="post">
        <h1>Login</h1>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
        <?php
        if (!empty($error)) {
            echo '<p style="color:red;">' . $error . '</p>';
        }
        ?>
    </form>
</body>
</html>