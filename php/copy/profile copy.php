<?php
error_reporting(E_ALL);
session_start();
include("database.php");

$captchaError = ""; // Initialize $captchaError

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Function to archive the deleted user
function archiveUser($conn, $id, $name, $loggedInSurname, $loggedInEmail, $loggedInPassword) {
    $hashedPassword = password_hash($loggedInPassword, PASSWORD_DEFAULT); // Hash the password before storing
    $stmt = mysqli_prepare($conn, "INSERT INTO archive (id, name, surname, email, password) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issss", $id, $name, $loggedInSurname, $loggedInEmail, $loggedInPassword);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Function to delete the user
function deleteUser($conn, $loggedInEmail) {
    // Check if the email is not admin's email
    if ($loggedInEmail !== $_SESSION['admin_email']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Validate reCAPTCHA if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Check if the delete button is clicked
    if (isset($_POST['delete']) && empty($captchaError)) { // Ensure captcha is validated before proceeding with delete action
        $loggedInEmail = $_SESSION['email'];
        $loggedInPassword = isset($_SESSION['password']) ? $_SESSION['password'] : ''; // Retrieve password from session
        $loggedInName = isset($_SESSION['name']) ? $_SESSION['name'] : ''; // Retrieve name from session
        $loggedInSurname = isset($_SESSION['surname']) ? $_SESSION['surname'] : ''; // Retrieve surname from session

        // Fetch user details including ID
        $stmt = mysqli_prepare($conn, "SELECT id, name, surname, email, password FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $loggedInId, $loggedInName, $loggedInSurname, $loggedInEmail, $loggedInPassword);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);       

        // Archive the user before deletion
        archiveUser($conn, null, $loggedInName, $loggedInSurname, $loggedInEmail, $loggedInPassword);

        // Delete the user
        deleteUser($conn, $loggedInEmail);

        // Redirect to the login page if not an admin
        if ($loggedInEmail !== $_SESSION['admin_email']) {
            header("Location: logout.php");
            exit();
        }
    }

    // Handle package and membership selection
    if ((isset($_POST['selected_package']) || isset($_POST['selected_membership'])) && empty($captchaError)) {
        if (isset($_POST['selected_package']) && !isset($_POST['selected_membership'])) {
            $selectedPackage = $_POST['selected_package'];
            $_SESSION['selected_package'] = $selectedPackage;
            $_SESSION['selected_time'] = time(); // Store the current time

            // Update the user's package in the database
            $stmt = mysqli_prepare($conn, "UPDATE package SET package = ?, name = ?, surname = ? WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "ssss", $selectedPackage, $_SESSION['name'], $_SESSION['surname'], $_SESSION['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['selected_membership']) && !isset($_POST['selected_package'])) {
            $selectedMembership = $_POST['selected_membership'];
            $_SESSION['selected_membership'] = $selectedMembership;
            $_SESSION['selected_time'] = time(); // Store the current time

            // Update the user's membership in the database
            $stmt = mysqli_prepare($conn, "UPDATE membership SET membership = ?, name = ?, surname = ? WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "ssss", $selectedMembership, $_SESSION['name'], $_SESSION['surname'], $_SESSION['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo "You cannot select both a package and a membership.";
        }
    }
}

// Fetch user details from the database based on the logged-in email
$loggedInName = $loggedInSurname = $loggedInEmail = '';
$loggedInEmail = $_SESSION['email'];
$stmt = mysqli_prepare($conn, "SELECT id, name, surname, email, password FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $loggedInId, $loggedInName, $loggedInSurname, $loggedInEmail, $loggedInPassword);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Fetch the selected package and membership information from the database
$selectedPackage = $selectedMembership = '';
$stmt = mysqli_prepare($conn, "SELECT selected_package FROM package WHERE email = ? ORDER BY selected_date DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $selectedPackage);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT selected_membership FROM membership WHERE email = ? ORDER BY selected_date DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $selectedMembership);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Clear the selected package after a week
if (isset($_SESSION['selected_time']) && time() - $_SESSION['selected_time'] > (7 * 24 * 60 * 60)) {
    unset($_SESSION['selected_package']);
    unset($_SESSION['selected_time']);

    // Remove the package from the database
    $stmt = mysqli_prepare($conn, "UPDATE package SET selected_package = NULL WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Clear the selected membership after a month
if (isset($_SESSION['selected_time']) && time() - $_SESSION['selected_time'] > (30 * 24 * 60 * 60)) {
    unset($_SESSION['selected_membership']);
    unset($_SESSION['selected_time']);

    // Remove the membership from the database
    $stmt = mysqli_prepare($conn, "UPDATE membership SET selected_membership = NULL WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../css/profile.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <title>Profile</title>
</head>
<body>
<div id="form">
   <h1>Account</h1>
   <script src="https://www.google.com/recaptcha/api.js" async defer></script>
   <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

      <div class="row">
         <div class="column">
            <label for="fname">First Name</label>
            <input type="text" id="fname" name="fname" value="<?= $loggedInName ?>" readonly>

            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lname" value="<?= $loggedInSurname ?>" readonly>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= $loggedInEmail ?>" readonly>
            </div>

         <div class="column">
            <label for="package">Package</label>
            <input type="text" id="package" name="package" value="<?= isset($selectedPackage) ? $selectedPackage : 'No package selected' ?>" readonly>

            <label for="membership">Membership</label>
            <input type="text" id="membership" name="membership" value="<?= isset($selectedMembership) ? $selectedMembership : 'No Membership selected' ?>" readonly>

            <div class="g-recaptcha" data-sitekey="6Lcw5PcpAAAAAGVMJFRTpk1JxLxZD7CXwUqQaGk6"></div>
            <span style="color: red;"><?php echo $captchaError; ?></span>
         </div>
      </div>

      <div class="row">
         <div class="column">
            <button type="button" onclick="goUpdate()">Edit</button>
         </div>
         <div class="column">
            <button type="button" onclick="goBack()">Go Back</button>
         </div>
         <div class="column">
            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete your account?')">Delete</button>
         </div>
      </div>
   </form>
</div>

<script>
   function goBack() {
      window.location.href = 'index.php';
   }
   function goUpdate()  {
      window.location.href = 'updateprofile.php';
   }
</script>

</body>
</html>