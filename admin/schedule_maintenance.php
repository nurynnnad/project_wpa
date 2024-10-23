<?php
session_start();
require '../config/db.php'; // Database connection

$message = ''; // Message initialization

// Fetch available equipment for the dropdown
$sql_available_equipment = "
    SELECT e.id, e.equipment_name, 
        (e.quantity - IFNULL(SUM(usg.quantity), 0) - IFNULL(SUM(maint.quantity), 0)) AS available_quantity 
    FROM equipment e
    LEFT JOIN equipment_usage usg 
        ON e.id = usg.equipment_id AND usg.status = 'in-use'
    LEFT JOIN equipment_maintenance maint 
        ON e.id = maint.equipment_id AND maint.status = 'under maintenance'
    GROUP BY e.id
    HAVING available_quantity > 0;
";

$available_equipment = $conn->query($sql_available_equipment);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form inputs
    $equipment_id = $_POST['equipment_id'];
    $quantity_needed = $_POST['quantity_needed'];
    $maintenance_date = $_POST['maintenance_date'];
    $maintenance_description = $_POST['maintenance_description'];
    $maintenance_cost = $_POST['maintenance_cost'];

    // Insert maintenance record without modifying total quantity
    $stmt = $conn->prepare("
        INSERT INTO equipment_maintenance 
        (equipment_id, maintenance_date, maintenance_description, maintenance_cost, quantity, status) 
        VALUES (?, ?, ?, ?, ?, 'under maintenance')
    ");
    $stmt->bind_param('issdi', $equipment_id, $maintenance_date, $maintenance_description, $maintenance_cost, $quantity_needed);

    if ($stmt->execute()) {
        $message = "<p class='success-message'>Maintenance scheduled successfully!</p>";
    } else {
        $message = "<p class='error-message'>Error: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Maintenance</title>
    <style>
        body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, #74ebd5, #ACB6E5);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        color: #333;
    }

    .form-container {
        background-color: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 600px;
        text-align: center;
        position: relative;
    }

    h2 {
        color: #2c3e50;
        font-size: 26px;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    select,
    input[type="date"],
    input[type="number"],
    textarea {
        width: 100%;
        padding: 12px;
        margin: 15px 0;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }

    select:focus,
    input:focus,
    textarea:focus {
        border-color: #3498db;
        outline: none;
    }

    button {
        background-color: #3498db;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        transition: background-color 0.3s;
        margin-top: 10px;
    }

    button:hover {
        background-color: #2980b9;
    }

    .success-message,
    .error-message {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 16px;
    }

    .success-message {
        color: #4CAF50;
        background-color: #e0f7fa;
        border: 2px solid #4CAF50;
    }

    .error-message {
        color: red;
        background-color: #f9d6d5;
        border: 2px solid red;
    }

    .low-stock-warning {
        color: red;
        margin-bottom: 20px;
        font-size: 16px;
    }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Schedule Maintenance</h2>

    <!-- Display Success/Error Message -->
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <form method="POST" action="schedule_maintenance.php">
        <label for="equipment_id">Select Equipment for Maintenance:</label>
        <select name="equipment_id" required>
            <option value="">Select Equipment</option>
            <?php while ($item = $available_equipment->fetch_assoc()): ?>
                <option value="<?php echo $item['id']; ?>">
                    <?php echo htmlspecialchars($item['equipment_name'] . " (Available: " . $item['available_quantity'] . ")"); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="quantity_needed">Quantity for Maintenance:</label>
        <input type="number" name="quantity_needed" required min="1"><br>

        <label for="maintenance_date">Maintenance Date:</label>
        <input type="date" name="maintenance_date" required><br>

        <label for="maintenance_description">Description:</label>
        <textarea name="maintenance_description" required></textarea><br>

        <label for="maintenance_cost">Cost (in MYR):</label>
        <input type="number" name="maintenance_cost" required><br>

        <button type="submit">Schedule Maintenance</button>
    </form>

    <a href="../admin_dashboard.php">
        <button>Back to Dashboard</button>
    </a>
</div>

</body>
</html>
