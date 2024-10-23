<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];  // Get user data from session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo $user['name']; ?> (<?php echo $user['role']; ?>)</h1>

    <p>This is your admin dashboard. You can manage surgical equipment here.</p>

    <ul>
        <li><a href="admin/add_equipment.php" class="link-box">Add Equipment</a></li>
        <li><a href="admin/equipment_list.php" class="link-box">View Equipment List</a></li>
        <li><a href="admin/schedule_maintenance.php" class="link-box">Schedule Maintenance</a></li>
        <li><a href="logout.php" class="link-box">Logout</a></li>
    </ul>
</div>

</body>
</html>