<?php
session_start();
include("database.php");

$nameError = $surnameError = $passError = $emailError = $captchaError = $success = "";

if (isset($_SESSION['email'])) { //checks if the user is already logged in
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //gets the input from the user
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $surname = filter_input(INPUT_POST, "Surname", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    // Validate reCAPTCHA
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $recaptchaSecretKey = '6LcJrfQpAAAAACSYXiDEyA_-I-H-yMmobvSC6__z'; // Replace with your reCAPTCHA secret key
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecretKey . '&response=' . $recaptchaResponse;
        $recaptchaResponseData = json_decode(file_get_contents($recaptchaUrl));
        
        if (!$recaptchaResponseData->success) {
            $captchaError = "Please complete the reCAPTCHA";
        }
    } else {
        $captchaError = "Please complete the reCAPTCHA";
    }

    if (empty($name)) {
        $nameError = "Please enter a First name <br>";
    } elseif (!preg_match("/^[a-zA-Z]+$/", $name)) {
        $nameError = "Name should only contain letters<br>";
    }

    if (empty($surname)) {
        $surnameError = "Please enter a Surname <br>";
    } elseif (!preg_match("/^[a-zA-Z]+$/", $surname)) {
        $surnameError = "Surname should only contain letters<br>";
    }

    if (preg_match("/[0-9]/", $name)) {
        $nameError = "Name should not contain numbers<br>";
    }

    if (preg_match("/[0-9]/", $surname)) {
        $surnameError = "Surname should not contain numbers<br>";
    }

    if (empty($email)) {
        $emailError = "Please enter an email<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email address<br>";
    }

    if (empty($password)) {
        $passError = "Please enter a password<br>";
    }

    if (empty($nameError) && empty($surnameError) && empty($emailError) && empty($passError) && empty($captchaError)) {
        // Check if Email already exists
        $checkQuery = "SELECT * FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $checkQuery)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Email already exists
                $emailError = "Email already exists<br>";
            } else {
                // Insert the new user to the users table
                $insertQuery = "INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($conn, $insertQuery)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt, "ssss", $name, $surname, $email, $hash);
                    mysqli_stmt_execute($stmt);

                    // Redirect to login page after successful registration
                    header("Location: login.php");
                    exit();
                } else {
                    echo "Error: Could not prepare insert query: " . mysqli_error($conn);
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error: Could not prepare select query: " . mysqli_error($conn);
        }
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/reg.css">
    <title>Register</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <form id="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h1>Registration</h1>
        
        <label for="name">Name: </label><br>
        <input type="text" name="name" placeholder="Enter a Name"><br>
        <span style="color: red;"><?php echo $nameError ?></span>
        
        <label for="Surname">Surname: </label><br>
        <input type="text" name="Surname" placeholder="Enter a Surname"><br>
        <span style="color: red;"><?php echo $surnameError ?></span>

        <label for="email">Email</label><br>
        <input type="text" name="email" placeholder="Enter an Email"><br>
        <span style="color: red;"><?php echo $emailError ?></span>
    
        <label for="password">Password</label><br>
        <input type="password" name="password" placeholder="Enter a Password"><br>
        <span style="color: red;"><?php echo $passError ?></span>

        <div class="g-recaptcha" data-sitekey="6LcJrfQpAAAAABN0IZY2U0waYXokgh2V9z1xLdhM"></div>
        <span style="color: red;"><?php echo $captchaError ?></span>
    
        <button type="submit" name="submit">Register</button>
        <span style="color: green;"><?php echo $success ?></span>
        <p>Already have an account? <a href="login.php">Log In</a></p>
    </form>
</body>
</html>