<?php
session_start();
include("../php/database.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// Establish database connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="../admin/css/salesrecord.css">
</head>
<body>
<div class="grid-container">

    <header class="header">
        <div class="menu-icon" onclick="openSidebar()">
            <span class="material-icons-outlined">menu</span>
        </div>
        <h1>Admin Dashboard - Sales Record</h1>
    </header>

    <aside id="sidebar" class="sidebar">
        <div class="sidebar-title">
            <div class="sidebar-brand">
                <span class="material-icons-outlined">inventory</span> Inventory
            </div>
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
        <section class="stats">
            <div class="stat-box">
                <h3>Purchased Packages</h3>
                <p><?= $total_package_sales ?></p>
            </div>
            <div class="stat-box">
                <h3>Membership</h3>
                <p><?= $total_membership_sales ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Sales Record</h3>
                <p><?= $total_sales ?></p>
            </div>
        </section>

        <section class="users">
            <h2>Users and Purchases</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Membership Cost (₱)</th>
                        <th>Package Cost (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= is_numeric($user['membership_cost']) ? '₱' . $user['membership_cost'] : $user['membership_cost'] ?></td>
                            <td><?= is_numeric($user['package_cost']) ? '₱' . $user['package_cost'] : $user['package_cost'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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