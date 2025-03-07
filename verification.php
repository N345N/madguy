<?php
$error_message = "";
$alert_class = "alert-info"; // Default alert class for general messages

// Display message from URL parameter if exists
if (isset($_GET['message'])) {
    $error_message = htmlspecialchars($_GET['message']);
    // Check if the message is about the OTP being sent
    if (strpos($error_message, 'Code has been sent') !== false) {
        $alert_class = "alert-info"; // Keep the info color for this message
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if 'verify' button is clicked
    if (isset($_POST['verify'])) {
        // Retrieve OTP code from the form
        $otp_code = $_POST['otp_code'];
        
        // Check if OTP code is not empty
        if (!empty($otp_code)) {
            $conn = new mysqli('localhost', 'root', '', 'sexybodyfitnessgym');
            if ($conn->connect_error) {
                die('Could not connect to the database.');
            }

            // Query the database to verify the OTP code
            $verifyQuery = $conn->query("SELECT code FROM users WHERE code = '$otp_code'");

            if ($verifyQuery->num_rows > 0) {
                // Redirect to change password page if OTP is valid
                header("Location: change_password.php?code=$otp_code");
                exit();
            } else {
                // Show error message if OTP is invalid
                $error_message = "Invalid OTP code. Please try again.";
                $alert_class = "alert-danger"; // Set alert class to danger for invalid OTP
            }

            $conn->close();
        } else {
            // Show error message if OTP code is empty
            $error_message = "Please enter the OTP code.";
            $alert_class = "alert-danger"; // Set alert class to danger for empty OTP
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>Verification</title>
</head>
<body>
    <main class="login-form">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Verification Account</div>
                        <div class="card-body">
                            <?php if (!empty($error_message)) : ?>
                                <div class="alert <?php echo $alert_class; ?>" role="alert">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                <div class="form-group row">
                                    <label for="otp" class="col-md-4 col-form-label text-md-right">OTP Code</label>
                                    <div class="col-md-6">
                                        <input type="text" id="otp" class="form-control" name="otp_code" required autofocus>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <input type="submit" value="Verify" name="verify" class="btn btn-primary">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
