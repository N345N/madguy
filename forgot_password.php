<?php
session_start(); // Ensure session is started

// Include database connection
include("php/database.php");

// Check if database connection is successful
if (!isset($conn)) {
    die("Database connection failed. Please check the database configuration.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Prepare statement failed: " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if email exists
    if ($stmt->num_rows == 0) {
        // No email found in database
        $_SESSION['error'] = 'no_email';
    } else {
        // Email exists, store it in session and redirect to verification
        $_SESSION['email'] = $email;
        header("Location: verification.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>
<body>
    <div class="container p-3 border border-5 rounded-3" style="width: 35%;">
        <h1 class="display-6 text-center p-2 bg-light">Password Reset</h1>
        <?php
        // Display error message directly below the heading if redirected from forgot_password_process.php
        if (isset($_GET['message']) && $_GET['message'] == 'no_email') {
            echo '<div class="alert alert-danger text-center mt-2">No Email Exist.</div>';
        }   
        ?>
        <form action="forgot_password_process.php" method="post">
            <div class="row mb-3 justify-content-md-center">
                <div class="col-auto">
                    <input type="email" name="email" placeholder="Email address" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary" name="reset">Reset</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>