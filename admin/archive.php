<?php
session_start();
include("../php/database.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    // Redirect to the login page if not logged in
    header("Location: admin_login.php");
    exit();
}

// Establish database connection
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle account restoration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restore'])) {
    $userId = $_POST['user_id'];

    // Fetch user data from archive
    $stmt = $conn->prepare("SELECT * FROM archive WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Check if the email is already active in users table
        $stmt_check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $user['email']);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            // Email is already active, display error message
            $status = "Error: The email is already active and cannot be restored.";
        } else {
            // Restore user to users table
            $stmt_restore = $conn->prepare("INSERT INTO users (id, name, surname, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt_restore->bind_param("issss", $user['id'], $user['name'], $user['surname'], $user['email'], $user['password']);
            $stmt_restore->execute();
            $stmt_restore->close();

            // Remove user from archive
            $stmt_delete = $conn->prepare("DELETE FROM archive WHERE id = ?");
            $stmt_delete->bind_param("i", $userId);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Redirect to prevent resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Fetch all archived users
$archivedUsers = [];
$sql = "SELECT * FROM archive";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $archivedUsers[] = $row;
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
    <title>Admin Dashboard - Archive</title>
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
        <h1>Admin Dashboard - Archive</h1>
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
            <h2>Archived Users</h2>
            <?php if (isset($status)): ?>
                <div class="error-message"><?= $status ?></div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedUsers as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['name'] ?></td>
                            <td><?= $user['surname'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="restore">Restore</button>
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