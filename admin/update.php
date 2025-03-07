<?php
session_start();
include("../php/database.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Establish database connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["update_package"])) {
        // Get the email and new package from the form submission
        $email = $_POST["email"];
        $newPackage = $_POST["new_package"];

        // Check if the user has an active membership
        $membershipCheck = $conn->prepare("SELECT selected_membership FROM membership WHERE email = ?");
        $membershipCheck->bind_param("s", $email);
        $membershipCheck->execute();
        $membershipResult = $membershipCheck->get_result();

        if ($membershipResult->num_rows > 0) {
            // User has an active membership, cannot add package
            echo "User has an active membership. Cannot update package.";
        } else {
            // Update the package in the database
            $stmt = $conn->prepare("UPDATE package SET selected_package = ? WHERE email = ?");
            $stmt->bind_param("ss", $newPackage, $email);
            $stmt->execute();
            $stmt->close();

            // Redirect to the dashboard page after updating
            header("Location: update.php");
            exit();
        }
    } elseif (isset($_POST["delete_package"])) {
        // Get the email from the form submission
        $email = $_POST["email"];

        // Delete the package record from the database
        $stmt = $conn->prepare("DELETE FROM package WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        // Redirect to the dashboard page after deleting
        header("Location: dashboard.php");
        exit();
    } elseif (isset($_POST["update_membership"])) {
        // Get the email and new membership from the form submission
        $email = $_POST["email"];
        $newMembership = $_POST["new_membership"];

        // Check if the user has an active package
        $packageCheck = $conn->prepare("SELECT selected_package FROM package WHERE email = ?");
        $packageCheck->bind_param("s", $email);
        $packageCheck->execute();
        $packageResult = $packageCheck->get_result();

        if ($packageResult->num_rows > 0) {
            // User has an active package, cannot add membership
            echo "User has an active package. Cannot update membership.";
        } else {
            // Update the membership in the database
            $stmt = $conn->prepare("UPDATE membership SET selected_membership = ? WHERE email = ?");
            $stmt->bind_param("ss", $newMembership, $email);
            $stmt->execute();
            $stmt->close();

            // Redirect to the dashboard page after updating
            header("Location: dashboard.php");
            exit();
        }
    } elseif (isset($_POST["delete_membership"])) {
        // Get the email from the form submission
        $email = $_POST["email"];

        // Delete the membership record from the database
        $stmt = $conn->prepare("DELETE FROM membership WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        // Redirect to the dashboard page after deleting
        header("Location: dashboard.php");
        exit();
    }
}

// Fetch all users and their selected packages and memberships
$users = [];
$sql = "SELECT u.id, u.name, u.surname, u.email, p.selected_package, m.selected_membership 
        FROM users u 
        LEFT JOIN (
            SELECT email, selected_package 
            FROM package 
            WHERE (email, selected_date) IN (
                SELECT email, MAX(selected_date) 
                FROM package 
                GROUP BY email
            )
        ) p ON u.email = p.email
        LEFT JOIN (
            SELECT email, selected_membership 
            FROM membership 
            WHERE (email, selected_date) IN (
                SELECT email, MAX(selected_date) 
                FROM membership 
                GROUP BY email
            )
        ) m ON u.email = m.email";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="../admin/css/dashboard.css">
</head>
<body>
<div class="grid-container">

    <header class="header">
        <div class="menu-icon" onclick="openSidebar()">
            <span class="material-icons-outlined">menu</span>
        </div>
        <h1>Admin Dashboard - Update</h1>
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
                <a href="logout.php">
                    <span class="material-icons-outlined">logout</span> Logout
                </a>
            </li>
        </ul>
    </aside>
    <main class="main-container">
        <section class="users">
            <h2>Manage Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th>Package</th>
                        <th>Membership</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['name'] ?></td>
                            <td><?= $user['surname'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['selected_package'] ?></td>
                            <td><?= $user['selected_membership'] ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="email" value="<?= $user['email'] ?>">
                                    <select name="new_package" required>
                                        <option value="Fitness" <?= $user['selected_package'] == 'Fitness' ? 'selected' : '' ?>>Fitness</option>
                                        <option value="Zumba" <?= $user['selected_package'] == 'Zumba' ? 'selected' : '' ?>>Zumba</option>
                                        <option value="Gym" <?= $user['selected_package'] == 'Gym' ? 'selected' : '' ?>>Gym</option>
                                        <option value="All" <?= $user['selected_package'] == 'All' ? 'selected' : '' ?>>All</option>
                                    </select>
                                    <button type="submit" name="update_package">Update</button>
                                    <button type="submit" name="delete_package">Delete</button> <!-- Changed name from delete_membership to delete_package -->
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="email" value="<?= $user['email'] ?>">
                                    <select name="new_membership" required>
                                        <option value="Bronze" <?= $user['selected_membership'] == 'Bronze' ? 'selected' : '' ?>>Bronze</option>
                                        <option value="Silver" <?= $user['selected_membership'] == 'Silver' ? 'selected' : '' ?>>Silver</option>
                                        <option value="Gold" <?= $user['selected_membership'] == 'Gold' ? 'selected' : '' ?>>Gold</option>
                                        <option value="Diamond" <?= $user['selected_membership'] == 'Diamond' ? 'selected' : '' ?>>Diamond</option>
                                    </select>
                                    <button type="submit" name="update_membership">Update</button>
                                    <button type="submit" name="delete_membership">Delete</button>
                                </form>
                            </td>
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