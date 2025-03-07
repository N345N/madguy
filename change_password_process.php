<?php
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $conn = new mysqli('localhost', 'root', '', 'sexybodyfitnessgym');
    if ($conn->connect_error) {
        die('Could not connect to the database');
    }

    $verifyQuery = $conn->prepare("SELECT email FROM users WHERE code = ? AND updatedtime >= NOW() - INTERVAL 1 DAY");
    $verifyQuery->bind_param("s", $code);
    $verifyQuery->execute();
    $result = $verifyQuery->get_result();

    if ($result->num_rows == 0) {
        header("Location: php/login.php");
        exit();
    }

    $row = $result->fetch_assoc();
    $email = $row['email'];

    if (isset($_POST['change'])) {
        $newPassword = $_POST['newPassword'];

        if (empty($newPassword)) {
            echo "New password cannot be empty.";
            exit();
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $changeQuery = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND code = ? AND updatedtime >= NOW() - INTERVAL 1 DAY");
        $changeQuery->bind_param("sss", $hashedPassword, $email, $code);
        $changeQuery->execute();

        if ($changeQuery) {
            header("Location: success.html");
            exit();
        } else {
            echo "Error updating password: " . $conn->error;
            exit();
        }
    }

    $conn->close();
} else {
    header("Location: php/login.php");
    exit();
}
?>