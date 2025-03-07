<?php
session_start();
include("../php/database.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    // Redirect to the admin login page if not logged in
    header("Location: admin_login.php");
    exit();
}

// Establish database connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch statistics or other admin dashboard data
$totalUsersResult = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

// Fetch number of users who have purchased a package
$purchasedPackageResult = $conn->query("SELECT COUNT(DISTINCT email) AS purchased_package FROM package WHERE selected_package <> ''");
$purchasedPackage = $purchasedPackageResult->fetch_assoc()['purchased_package'];

// Fetch number of users who have selected a membership
$selectedMembershipResult = $conn->query("SELECT COUNT(DISTINCT email) AS selected_membership FROM membership WHERE selected_membership <> ''");
$selectedMembership = $selectedMembershipResult->fetch_assoc()['selected_membership'];

// Fetch number of accounts in the archive
$archivedUsersResult = $conn->query("SELECT COUNT(*) AS archived_users FROM archive");
$archivedUsers = $archivedUsersResult->fetch_assoc()['archived_users'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
                <span class="material-icons-outlined">inventory</span> Inventory
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