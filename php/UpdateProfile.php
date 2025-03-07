<?php
error_reporting(E_ALL);
session_start();
include("database.php"); // Make sure to include database.php

$nameError = $surnameError = $passwordError = $confirmPasswordError = $currentPasswordError = $captchaError = "";

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to the login page if not logged in
    header("Location: php/login.php");
    exit();
}

// Fetch user details from the database based on the logged-in email
$loggedInEmail = $_SESSION['email'];
$stmt = mysqli_prepare($conn, "SELECT name, surname, email, password FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstName, $surname, $email, $currentPasswordFromDatabase);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $newFirstName = $_POST["fname"];
    $newSurname = $_POST["lname"];
    $currentPassword = $_POST["current_password"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    // Validate reCAPTCHA
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $recaptchaSecretKey = '6Lcw5PcpAAAAAJxe80P29yliwKEMFGkak3QeGUr-'; // Replace with your reCAPTCHA secret key
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecretKey . '&response=' . $recaptchaResponse;
        $recaptchaResponseData = json_decode(file_get_contents($recaptchaUrl));
         
        if (!$recaptchaResponseData->success) {
            $captchaError = "Please complete the reCAPTCHA";
        }
    } else {
        $captchaError = "Please complete the reCAPTCHA";
    }

    // If there are no captcha errors, proceed with further validation
    if (empty($captchaError)) {
        // Validate name
        if (!empty($newFirstName)) {
            if (!preg_match('/^[a-zA-Z\s]+$/', $newFirstName)) {
                $nameError = "Name can only contain letters and spaces";
            }
        }

        // Validate surname
        if (!empty($newSurname)) {
            if (!preg_match('/^[a-zA-Z\s]+$/', $newSurname)) {
                $surnameError = "Surname can only contain letters and spaces";
            }
        }

        // Validate current password
        if (!empty($newPassword) && !empty($confirmPassword)) {
            if (!password_verify($currentPassword, $currentPasswordFromDatabase)) {
                // Incorrect current password
                $currentPasswordError = "Incorrect current password";
            }
        }

        // Validate new password if it's not empty
        if (!empty($newPassword)) {
            // Your existing new password validation code

            // Validate confirm password
            if (empty($confirmPassword)) {
                $confirmPasswordError = "Please confirm your new password";
            }

            // Validate password match
            if ($newPassword !== $confirmPassword) {
                $confirmPasswordError = "Passwords do not match";
            }
        } elseif (!empty($confirmPassword)) {
            // If new password is empty but confirm password is provided
            $passwordError = "Please provide a new password";
        }

        // If everything is valid, update the user's data
        if (empty($nameError) && empty($surnameError) && empty($currentPasswordError) && empty($passwordError) && empty($confirmPasswordError) && empty($captchaError)) {
            // If new password is provided, hash it
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            } else {
                $hashedPassword = $currentPasswordFromDatabase; // Use the current hashed password
            }

            // Update user data in the database
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, surname=?, password=? WHERE email=?");
            mysqli_stmt_bind_param($stmt, "ssss", $newFirstName, $newSurname, $hashedPassword, $loggedInEmail);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);

            // Reset error messages after successful update
            $nameError = $surnameError = $currentPasswordError = $passwordError = $confirmPasswordError = $captchaError = "";

            // Redirect to the profile page after the update
            header("Location: profile.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../css/UpdateProfile.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <title>Profile</title>
</head>
<body>
<div id="form">
   <h1>Update Account</h1>
   <script src="https://www.google.com/recaptcha/api.js" async defer></script>
   <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post">

      <div class="column">
         <label for="fname">First Name</label>
         <input type="text" id="fname" name="fname" value="<?php echo $firstName; ?>"><br>
         <span style="color: red;"><?php echo $nameError ?></span>

         <label for="lname">Last Name</label>
         <input type="text" id="lname" name="lname" value="<?php echo $surname; ?>"><br>
         <span style="color: red;"><?php echo $surnameError ?></span>
         
         <label for="email">Email</label>
         <input type="email" id="email" name="email" value="<?php echo $email; ?>" readonly><br>
      </div>  

      <div class="column">
         <label for="current_password">Current Password</label>
         <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
         <span style="color: red;"><?php echo $currentPasswordError ?></span>
         
         <label for="new_password">New Password</label>
         <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
         <span style="color: red;"><?php echo $passwordError ?></span>

         <label for="confirm_password">Confirm New Password</label>
         <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
         <span style="color: red;"><?php echo $confirmPasswordError ?></span>

         <div class="g-recaptcha" data-sitekey="6Lcw5PcpAAAAAGVMJFRTpk1JxLxZD7CXwUqQaGk6"></div>
         <span style="color: red;"><?php echo $captchaError; ?></span>
      </div> 

      <button type="submit">Update</button>
      <button type="button" onclick="goBack()">Cancel</button>
   </form>
</div>
<script>
   function goBack() {
      window.location.href = 'profile.php';
   }
</script>

</body>
</html>