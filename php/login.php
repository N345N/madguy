<?php
session_start();
include("database.php");

// Check if the admin is already logged in
if (isset($_SESSION['admin_id'])) {
    // Redirect to dashboard.php if the admin is logged in
    header("Location: dashboard.php");
    exit(); // Exit the script after redirection
}

// Check if a user is already logged in (optional, depending on your requirements)
if (isset($_SESSION['id'])) {
    // Redirect to index.php if a user is logged in
    header("Location: index.php");
    exit(); // Exit the script after redirection
}

// Establish database connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Initialize error variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email and password are provided
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check if the login is for an admin
        $stmt = $conn->prepare("SELECT id, admin_email, admin_password FROM admins WHERE admin_email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $dbEmail, $dbPassword);

            if ($stmt->num_rows > 0) {
                // Admin email exists
                $stmt->fetch();
                if (password_verify($password, $dbPassword)) {
                    // Password is correct, set session variables for admin
                    $_SESSION['admin_id'] = $id;
                    $_SESSION['admin_email'] = $dbEmail;
                    $stmt->close();
                    $conn->close();
                    // Redirect to admin dashboard
                    header("Location: dashboard.php");
                    exit(); // Exit the script after redirection
                } else {
                    $error = "Invalid password for admin!";
                }
            } else {
                // Admin email does not exist, check for user
                $stmt->close(); // Close the current statement
                $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($id, $dbEmail, $dbPassword);

                    if ($stmt->num_rows > 0) {
                        // User email exists
                        $stmt->fetch();
                        if (password_verify($password, $dbPassword)) {
                            // Password is correct, set session variables for user
                            $_SESSION['id'] = $id; // Use a distinct session key for users
                            $_SESSION['email'] = $dbEmail;
                            $stmt->close();
                            $conn->close();
                            // Redirect to index.php for users
                            header("Location: index.php");
                            exit(); // Exit the script after redirection
                        } else {
                            // Password is incorrect for user
                            $error = "Invalid password!";
                        }
                    } else {
                        // Email not found in users
                        $error = "Invalid email!";
                    }

                    $stmt->close();
                } else {
                    $error = "Error in SQL statement: " . $conn->error;
                }
            }
        } else {
            $error = "Error in SQL statement: " . $conn->error;
        }
    } else {
        $error = "Email and password are required.";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../admin/css/admin_login.css">
</head>
<body>
    <form class="box" action="login.php" method="post">
        <h1>Login</h1>
        <input type="email" name="email" placeholder="Email" required>
        
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span id="togglePassword" class="toggle-password-icon">👀</span>
        </div>

        <input type="submit" value="Login">
        <a href="../forgot_password.php" class="forgot-password">Forgot password?</a>
        <a class="href" href="../php/reg.php">Sign Up</a>
        <?php
        if (!empty($error)) {
            echo '<p style="color:red;">' . $error . '</p>';
        }
        ?>
    </form>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the icon (optional)
            this.textContent = type === 'password' ? '🙉' : '🙈';
        });
    </script>
</body>
</html>