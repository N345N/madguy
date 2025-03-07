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
    if (isset($_POST['selected_membership'])) {
        $loggedInEmail = $_SESSION['email'];
        $selectedMembership = $_POST['selected_membership'];

        // Check if the user has an active package
        $stmt = $conn->prepare("SELECT selected_package FROM package WHERE email = ? AND selected_package != ''");
        $stmt->bind_param("s", $loggedInEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "You cannot select a membership while having an active package.";
        } else {
            // Check if the user has already selected a membership
            $stmt = $conn->prepare("SELECT selected_membership, selected_date FROM membership WHERE email = ?");
            $stmt->bind_param("s", $loggedInEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // User already has a membership, check if it's expired
                $row = $result->fetch_assoc();
                $selectedDate = strtotime($row['selected_date']);
                $expirationDate = strtotime('+1 month', $selectedDate);
                if (time() < $expirationDate) {
                    echo "You already have an active membership. You can select a new one after it expires.";
                } else {
                    // Update the user's membership with the new selection
                    $stmt = $conn->prepare("UPDATE membership SET selected_membership = ?, selected_date = NOW() WHERE email = ?");
                    $stmt->bind_param("ss", $selectedMembership, $loggedInEmail);
                    if ($stmt->execute()) {
                        echo "Membership selection updated successfully.";
                        recordSales($conn, $loggedInEmail, 0, getMembershipPrice($selectedMembership), getMembershipPrice($selectedMembership));
                    } else {
                        echo "Error updating record: " . $conn->error;
                    }
                    $stmt->close();
                }
            } else {
                // User doesn't have a membership, insert the new selection
                $stmt = $conn->prepare("INSERT INTO membership (email, selected_membership, selected_date) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $loggedInEmail, $selectedMembership);
                if ($stmt->execute()) {
                    echo "Membership selection saved successfully.";
                    recordSales($conn, $loggedInEmail, 0, getMembershipPrice($selectedMembership), getMembershipPrice($selectedMembership));
                } else {
                    echo "Error inserting record: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

// Function to get membership price based on selected plan
function getMembershipPrice($membership) {
    switch ($membership) {
        case 'Bronze': return 1050;
        case 'Silver': return 2100;
        case 'Gold': return 2790;
        case 'Diamond': return 5250;
        default: return 0;
    }
}

// Function to record sales in the salesrecord table
function recordSales($conn, $email, $packageSales, $membershipSales, $totalSales) {
    $stmt = $conn->prepare("INSERT INTO salesrecord (email, total_package_sales, total_membership_sales, total_sales) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE total_package_sales = total_package_sales + ?, total_membership_sales = total_membership_sales + ?, total_sales = total_sales + ?");
    $stmt->bind_param("siiiiii", $email, $packageSales, $membershipSales, $totalSales, $packageSales, $membershipSales, $totalSales);
    $stmt->execute();
    $stmt->close();
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
    <link rel="stylesheet" href="../css/membership.css">
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
        <h1>Join Suitable Plan</h1>
    </header>
    <div class="pricing-contents">
        <div class="pricing-card">
            <div class="pricing-card-header">
                <span class="pricing-card-title">Bronze</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>1,050</span>
                    <span class="desc">/1 Month</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-check"></i>Fitness</li>
                    <li><i class="fa-solid fa-check"></i>Zumba</li>
                    <li><i class="fa-solid fa-check"></i>Gym</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('1 Month')">
                    <input type="hidden" name="selected_membership" value="Bronze">
                    <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>

        <div class="pricing-card">
            <div class="pricing-card-header">
                <span class="pricing-card-title">Silver</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>2,100</span>
                    <span class="desc">/2 Month</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-check"></i>Fitness</li>
                    <li><i class="fa-solid fa-check"></i>Zumba</li>
                    <li><i class="fa-solid fa-check"></i>Gym</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('2 Month')">
                    <input type="hidden" name="selected_membership" value="Silver">
                    <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>

        <div class="pricing-card">
            <div class="pricing-card-header">
                <div class="tag-box">
                    <span class="tag">10% Off</span>
                </div>
                <span class="pricing-card-title">Gold</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>2,790</span>
                    <span class="desc">/3 Month</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-check"></i>Fitness</li>
                    <li><i class="fa-solid fa-check"></i>Zumba</li>
                    <li><i class="fa-solid fa-check"></i>Gym</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('3 Month')">
                    <input type="hidden" name="selected_membership" value="Gold">
                    <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>

        <div class="pricing-card">
            <div class="pricing-card-header">
                <span class="pricing-card-title">Diamond</span>
                <div class="price-circle">
                    <span class="price"><i>₱</i>5,250</span>
                    <span class="desc">/5 Month</span>
                </div>
            </div>
            <div class="pricing-card-body">
                <ul>
                    <li><i class="fa-solid fa-check"></i>Fitness</li>
                    <li><i class="fa-solid fa-check"></i>Zumba</li>
                    <li><i class="fa-solid fa-check"></i>Gym</li>
                </ul>
                <form method="post" onsubmit="return confirmSelection('5 Month')">
                    <input type="hidden" name="selected_membership" value="Diamond">
                    <button type="submit" class="btn price-plan-btn">Select Plan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    function confirmSelection(packageName) {
        var confirmationMessage = "Are you sure you want to select the " + packageName + " membership?";
        return confirm(confirmationMessage);
    }
</script>

</body>
</html>