<?php
session_start();
include("database.php");

// Establishing connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

// Checking connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['selected_package'])) {
        $loggedInEmail = $_SESSION['email'];
        $selectedPackage = $_POST['selected_package'];

        // Check if the user has already selected a membership
        $stmt = $conn->prepare("SELECT selected_membership FROM membership WHERE email = ?");
        $stmt->bind_param("s", $loggedInEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "You cannot select a package while having an active membership.";
        } else {
            // Check if the user has already selected a package
            $stmt = $conn->prepare("SELECT selected_package, selected_date FROM package WHERE email = ?");
            $stmt->bind_param("s", $loggedInEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // User already has a package, check if it's expired
                $row = $result->fetch_assoc();
                $selectedDate = strtotime($row['selected_date']);
                $expirationDate = strtotime('+1 week', $selectedDate); // Package expires after 1 week
                if (time() < $expirationDate) {
                    // Package is still active, user cannot select a new one
                    echo "You already have an active package. You can select a new one after it expires.";
                } else {
                    // Update the user's package with the new selection
                    $stmt = $conn->prepare("UPDATE package SET selected_package = ?, selected_date = NOW() WHERE email = ?");
                    $stmt->bind_param("ss", $selectedPackage, $loggedInEmail);
                    if ($stmt->execute()) {
                        echo "Package selection updated successfully.";
                    } else {
                        echo "Error updating record: " . $conn->error;
                    }
                    $stmt->close();
                }
            } else {
                // User doesn't have a package, insert the new selection
                $stmt = $conn->prepare("INSERT INTO package (email, selected_package, selected_date) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $loggedInEmail, $selectedPackage);
                if ($stmt->execute()) {
                    echo "Package selection saved successfully.";
                } else {
                    echo "Error inserting record: " . $conn->error;
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        $loggedInEmail = $_SESSION['email'];

        // Delete user's record from the package table
        $stmt = $conn->prepare("DELETE FROM package WHERE email = ?");
        $stmt->bind_param("s", $loggedInEmail);
        if ($stmt->execute()) {
            echo "Account deleted successfully.";
            // Clear the session
            session_unset();
            session_destroy();
            // Redirect to a confirmation page or home page
            header("Location: index.php");
            exit();
        } else {
            echo "Error deleting account: " . $conn->error;
        }
        $stmt->close();
    }
}

// Check if the user has a membership and notify
$stmt = $conn->prepare("SELECT selected_membership FROM membership WHERE email = ?");
$stmt->bind_param("s", $loggedInEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "You have an active membership. You cannot select a package.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sexy Body Fitness Gym</title>
    <link rel="stylesheet" href="../css/package.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/>
</head>
<body>
<header class="header">
    <div class="header-icons">
        <a href="index.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
    </div>
</header>

<section class="pricing" id="pricing">
    <header class="section-header">
        <h3>Pricing</h3>
        <h1>Join Suitable Package</h1>
    </header>
    <div class="pricing-contents">
        <div class="pricing-card">
            <div class="pricing-card-header">
                <span class="pricing-card-title">Fitness</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>125</span>
                    <span class="desc">/week</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-check"></i>Fitness</li>
                    <li><i class="fa-solid fa-times"></i>Zumba</li>
                    <li><i class="fa-solid fa-times"></i>Gym</li>
                </ul>
				<form method="post" onsubmit="return confirmSelection('Fitness')">
    			<input type="hidden" name="name" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>">
   			 	<input type="hidden" name="surname" value="<?php echo isset($_SESSION['surname']) ? $_SESSION['surname'] : ''; ?>">
    			<input type="hidden" name="selected_package" value="Fitness">
    			<button type="submit" class="btn price-plan-btn">Select Plan</button>
				</form>
            </div>
        </div>

        <div class="pricing-card">
            <div class="pricing-card-header">
                <span class="pricing-card-title">Zumba</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>125</span>
                    <span class="desc">/week</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-times"></i>Fitness</li>
                    <li><i class="fa-solid fa-check"></i>Zumba</li>
                    <li><i class="fa-solid fa-times"></i>Gym</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('Zumba')">
				<input type="hidden" name="name" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>">
   			 	<input type="hidden" name="surname" value="<?php echo isset($_SESSION['surname']) ? $_SESSION['surname'] : ''; ?>">
                <input type="hidden" name="selected_package" value="Zumba">
                <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>

        <div class="pricing-card">
            <div class="pricing-card-header">
                <span class="pricing-card-title">Gym</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>250</span>
                    <span class="desc">/week</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-times"></i>Fitness</li>
                    <li><i class="fa-solid fa-times"></i>Zumba</li>
                    <li><i class="fa-solid fa-check"></i>Gym</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('Gym')">
				<input type="hidden" name="name" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>">
   			 	<input type="hidden" name="surname" value="<?php echo isset($_SESSION['surname']) ? $_SESSION['surname'] : ''; ?>">
                <input type="hidden" name="selected_package" value="Gym">
                <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>

        <div class="pricing-card">
            <div class="pricing-card-header">
                <div class="tag-box">
                    <span class="tag">Recommend</span>
                </div>
                <span class="pricing-card-title">All</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>350</span>
                    <span class="desc">/week</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-check"></i>Fitness</li>
                    <li><i class="fa-solid fa-check"></i>Gym</li>
                    <li><i class="fa-solid fa-check"></i>Zumba</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('All')">
				<input type="hidden" name="name" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>">
   			 	<input type="hidden" name="surname" value="<?php echo isset($_SESSION['surname']) ? $_SESSION['surname'] : ''; ?>">
                <input type="hidden" name="selected_package" value="All">
                <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    function confirmSelection(packageName) {
        var confirmationMessage = "Are you sure you want to select the " + packageName + " plan?";
        return confirm(confirmationMessage);
    }
</script>

</body>
</html>