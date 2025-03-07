<?php
session_start();
include("database.php");

// Redirect if user is already logged in
if (isset($_SESSION['email'])) {
    header("Location: ../php/index.php");
    exit();
}

$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    // Prepare and execute the statement to check user
    $stmt = mysqli_prepare($conn, "SELECT email, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $identifier);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $email, $hashedPassword);

    if (mysqli_stmt_fetch($stmt)) {
        // Compare the hashed password from the database with the user input
        if (password_verify($password, $hashedPassword)) {
            $status = "Login successful!";
            $_SESSION['email'] = $email;
            // Perform any actions you want after successful login
            header("Location: ../php/index.php");
            exit();
        } else {
            $status = "Invalid email or password";
        }
    } else {
        $status = "Invalid email or password";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// If the account was deleted, redirect to login.php
if (isset($_GET['deleted']) && $_GET['deleted'] == true) {
    header("Location: ../php/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/reg.css">
    <title>Log In</title>
</head>
<body>
    <form id="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post">
        <h1>Sign In</h1>
        
        <label for="email">Email</label><br>
        <input type="text" name="email" placeholder="Enter Email" required><br>
      
        <label for="password">Password</label><br>
        <input type="password" name="password" placeholder="Enter Password" required><br>
        
        <button type="submit" name="submit">Login</button>
        <span style="color:red;"><?php echo $status ?></span>
        <p>Don't have an account yet? <a href="../php/reg.php">Sign Up</a></p>
        <a href="../forgot_password.php">Forgot Password?</a>
    </form>
</body>
</html>