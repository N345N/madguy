<?php
session_start();
include("database.php");

if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total users (excluding admin)
$admin_email = $_SESSION['admin_email'];
$totalUsersResult = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE email != '$admin_email'");
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

// Fetch number of users who have purchased a package (excluding admin)
$purchasedPackageResult = $conn->query("SELECT COUNT(DISTINCT email) AS purchased_package FROM package WHERE selected_package <> '' AND email != '$admin_email'");
$purchasedPackage = $purchasedPackageResult->fetch_assoc()['purchased_package'];

// Fetch number of users who have selected a membership (excluding admin)
$selectedMembershipResult = $conn->query("SELECT COUNT(DISTINCT email) AS selected_membership FROM membership WHERE selected_membership <> '' AND email != '$admin_email'");
$selectedMembership = $selectedMembershipResult->fetch_assoc()['selected_membership'];

// Fetch number of archived users (excluding admin)
$archivedUsersResult = $conn->query("SELECT COUNT(*) AS archived_users FROM archive WHERE email != '$admin_email'");
$archivedUsers = $archivedUsersResult->fetch_assoc()['archived_users'];

// Membership and package prices
$membership_prices = [
    'Bronze' => 1050,
    'Silver' => 2100,
    'Gold' => 2790,
    'Diamond' => 5250
];

$package_prices = [
    'Fitness' => 125,
    'Zumba' => 125,
    'Gym' => 250,
    'All' => 350
];

// Fetch all users with their selected membership and package choices
$users = [];
$sql = "SELECT u.id, u.name, u.surname, u.email, m.selected_membership, p.selected_package
        FROM users u
        LEFT JOIN membership m ON u.email = m.email
        LEFT JOIN package p ON u.email = p.email";
$result = $conn->query($sql);

// Initialize sales totals
$total_package_sales = 0;
$total_membership_sales = 0;
$total_sales = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calculate membership and package costs based on the user's selection
        $row['membership_cost'] = isset($membership_prices[$row['selected_membership']]) ? $membership_prices[$row['selected_membership']] : 'No Membership';
        $row['package_cost'] = isset($package_prices[$row['selected_package']]) ? $package_prices[$row['selected_package']] : 'No Package';

        // Calculate sales totals
        $total_package_sales += is_numeric($row['package_cost']) ? $row['package_cost'] : 0;
        $total_membership_sales += is_numeric($row['membership_cost']) ? $row['membership_cost'] : 0;

        // Add to total sales
        $total_sales = $total_package_sales + $total_membership_sales;

        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="../admin/css/styles.css">
</head>
<body>
<div class="grid-container">

    <header class="header">
        <div class="menu-icon" onclick="openSidebar()">
            <span class="material-icons-outlined">menu</span>
        </div>
        <h1>Dashboard</h1>
    </header>

    <aside id="sidebar" class="sidebar">
        <div class="sidebar-title">
            <div class="sidebar-brand">
                <span class="material-icons-outlined">inventory</span> Sexy Body Fitness Gym
            </div>
            <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
        </div>

        <ul class="sidebar-list">
            <li class="sidebar-list-item">
                <a href="dashboard.php">
                    <span class="material-icons-outlined">dashboard</span> Dashboard
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="users.php">
                    <span class="material-icons-outlined">people</span> Users
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="salesrecord.php">
                     <span class="material-icons-outlined">receipt_long</span> Sales Record
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="update.php">
                    <span class="material-icons-outlined">update</span> Update
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="archive.php">
                    <span class="material-icons-outlined">archive</span> Archive
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="admin_logout.php">
                    <span class="material-icons-outlined">logout</span> Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-container">
        <section class="users">
            <div class="box-container">
                <div class="box">
                    <h3><i class="material-icons-outlined">people</i> Total Users</h3>
                    <p><?= $totalUsers ?></p>
                </div>
                <div class="box">
                    <h3><i class="material-icons-outlined">shopping_cart</i> Purchased Packages</h3>
                    <p><?= $purchasedPackage ?></p>
                </div>
                <div class="box">
                    <h3><i class="material-icons-outlined">people</i> Membership</h3>
                    <p><?= $selectedMembership ?></p>
                </div>
                <div class="box">
                    <h3><i class="material-icons-outlined">receipt_long</i> Total Sales Record</h3>
                    <p>₱<?= number_format($total_sales, 2) ?></p>
                </div>
                <div class="box">
                    <h3><i class="material-icons-outlined">archive</i> Archived Accounts</h3>
                    <p><?= $archivedUsers ?></p>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    function openSidebar() {
        document.getElementById("sidebar").classList.add('sidebar-responsive');
    }

    function closeSidebar() {
        document.getElementById("sidebar").classList.remove('sidebar-responsive');
    }
</script>
</body>
</html>