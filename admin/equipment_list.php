<?php
session_start();
require '../config/db.php';  // Database connection

// Check if the user is admin to show edit options
$isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

// Get the filter value from the form or set default to 'all'
$status_filter = isset($_POST['status_filter']) ? strtolower(trim($_POST['status_filter'])) : 'all';

// SQL Query to Fetch Equipment Data with Calculated Quantities
$sql_equipment_list = "
    SELECT 
        e.id, 
        e.equipment_name, 
        e.equipment_type, 
        e.quantity AS total_quantity, 
        e.acquisition_date, 
        e.last_maintenance_date, 
        e.maintenance_due_date, 
        IFNULL(SUM(usg.quantity), 0) AS in_use_quantity, 
        IFNULL(SUM(maint.quantity), 0) AS under_maintenance_quantity, 
        (e.quantity - IFNULL(SUM(usg.quantity), 0) - IFNULL(SUM(maint.quantity), 0)) AS available_quantity
    FROM equipment e
    LEFT JOIN equipment_usage usg 
        ON e.id = usg.equipment_id AND usg.status = 'in-use'
    LEFT JOIN equipment_maintenance maint 
        ON e.id = maint.equipment_id AND maint.status = 'under maintenance'
    GROUP BY e.id
";

// Execute the query and handle errors
$equipment_list = $conn->query($sql_equipment_list);
if (!$equipment_list) {
    die("Error fetching equipment list: " . $conn->error);
}

// Create an array to store the filtered rows
$filtered_rows = [];

// Loop through the query result and apply the filter logic
while ($row = $equipment_list->fetch_assoc()) {
    $available_quantity = $row['available_quantity'] ?? 0;
    $in_use_quantity = $row['in_use_quantity'] ?? 0;
    $under_maintenance_quantity = $row['under_maintenance_quantity'] ?? 0;

    // Determine if the row matches the filter or 'all'
    $matches_filter = (
        $status_filter === 'all' ||
        ($status_filter === 'available' && $available_quantity > 0) ||
        ($status_filter === 'in-use' && $in_use_quantity > 0) ||
        ($status_filter === 'under maintenance' && $under_maintenance_quantity > 0)
    );

    if ($matches_filter) {
        $filtered_rows[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment List</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .filter-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-container select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e1f5fe;
        }
        .edit-btn {
            background-color: #008080;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 14px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
        .edit-btn:hover {
            background-color: #30D5C8;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .done-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #008080;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
        .done-btn:hover {
            background-color: #30D5C8;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .attention-message {
            color: red;
            font-size: 16px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<h1>Equipment List</h1>

<!-- Filter Form -->
<div class="filter-container">
    <form method="POST" action="equipment_list.php">
        <label for="status_filter">Filter by Status:</label>
        <select name="status_filter" id="status_filter" onchange="this.form.submit()">
            <option value="all" <?= ($status_filter === 'all') ? 'selected' : ''; ?>>All</option>
            <option value="available" <?= ($status_filter === 'available') ? 'selected' : ''; ?>>Available</option>
            <option value="in-use" <?= ($status_filter === 'in-use') ? 'selected' : ''; ?>>In-Use</option>
            <option value="under maintenance" <?= ($status_filter === 'under maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
        </select>
    </form>
</div>

<!-- Equipment Table -->
<?php if (!empty($filtered_rows)): ?>
    <table>
        <tr>
            <th>Equipment Name</th>
            <th>Type</th>
            <th>Total Quantity</th>
            <th>Status</th>
            <th>Acquisition Date</th>
            <th>Last Maintenance</th>
            <th>Next Maintenance</th>
            <?php if ($isAdmin): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>

        <?php foreach ($filtered_rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['equipment_name']); ?></td>
                <td><?= htmlspecialchars($row['equipment_type']); ?></td>
                <td><?= htmlspecialchars($row['total_quantity']); ?></td>
                <td>
                    <?php 
                    $statuses = [];
                    if ($row['available_quantity'] > 0) {
                        $statuses[] = "Available: {$row['available_quantity']}";
                    }
                    if ($row['in_use_quantity'] > 0) {
                        $statuses[] = "In-Use: {$row['in_use_quantity']}";
                    }
                    if ($row['under_maintenance_quantity'] > 0) {
                        $statuses[] = "Under Maintenance: {$row['under_maintenance_quantity']}";
                    }
                    echo implode(', ', $statuses);
                    ?>
                </td>
                <td><?= htmlspecialchars($row['acquisition_date']); ?></td>
                <td><?= htmlspecialchars($row['last_maintenance_date']); ?></td>
                <td><?= htmlspecialchars($row['maintenance_due_date']); ?></td>
                <?php if ($isAdmin): ?>
                    <td><a href="edit_equipment.php?id=<?= $row['id']; ?>" class="edit-btn">Edit</a></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No equipment found for the selected filter.</p>
<?php endif; ?>

<a href="<?= $isAdmin ? '../admin_dashboard.php' : '../staff_dashboard.php'; ?>" class="done-btn">Back to Dashboard</a>

</body>
</html>
