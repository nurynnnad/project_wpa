<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission to update the equipment
    $id = $_POST['id'];
    $equipment_name = $_POST['equipment_name'];
    $equipment_type = $_POST['equipment_type'];
    $quantity = $_POST['quantity'];
    $status = $_POST['status'];
    $last_maintenance = $_POST['last_maintenance'];
    $maintenance_due = $_POST['maintenance_due'];

    $sql = "UPDATE equipment 
            SET equipment_name = ?, 
                equipment_type = ?, 
                quantity = ?, 
                status = ?, 
                last_maintenance_date = ?, 
                maintenance_due_date = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssisssi', $equipment_name, $equipment_type, $quantity, $status, $last_maintenance, $maintenance_due, $id);

    if ($stmt->execute()) {
        header("Location: equipment_list.php"); // Redirect back to list after successful update
        exit;
    } else {
        echo "Error updating record: " . $stmt->error;
    }
} else {
    // Get the equipment details for the form
    if (!isset($_GET['id'])) {
        echo "No equipment ID provided.";
        exit;
    }

    $id = $_GET['id'];
    $sql = "SELECT * FROM equipment WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "No equipment found.";
        exit;
    }
    $equipment = $result->fetch_assoc();
}

// Warning message for maintenance duration
$currentDate = new DateTime();
$lastMaintenanceDate = new DateTime($equipment['last_maintenance_date']);
$interval = $currentDate->diff($lastMaintenanceDate);
$warningMessage = "";
if ($interval->y >= 5) {
    $warningMessage = "<p style='color: red;'>Warning: This equipment has been in use for more than 5 years. Please review.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment</title>
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
            max-width: 500px;
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

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus {
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

        .warning-message {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .form-container::before {
            content: '';
            background: url('medical-icon.png') no-repeat center center;
            background-size: 100px 100px;
            opacity: 0.05;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Equipment</h2>
    <?php echo $warningMessage; ?>
    <form method="POST" action="edit_equipment.php">
        <input type="hidden" name="id" value="<?php echo $equipment['id']; ?>">

        <label for="equipment_name">Equipment Name:</label>
        <input type="text" id="equipment_name" name="equipment_name" value="<?php echo htmlspecialchars($equipment['equipment_name']); ?>" required><br>

        <label for="equipment_type">Type:</label>
        <input type="text" id="equipment_type" name="equipment_type" value="<?php echo htmlspecialchars($equipment['equipment_type']); ?>" required><br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($equipment['quantity']); ?>" required><br>

        <label for="last_maintenance">Last Maintenance Date:</label>
        <input type="date" id="last_maintenance" name="last_maintenance" value="<?php echo htmlspecialchars($equipment['last_maintenance_date']); ?>" required><br>

        <label for="maintenance_due">Next Maintenance Due:</label>
        <input type="date" id="maintenance_due" name="maintenance_due" value="<?php echo htmlspecialchars($equipment['maintenance_due_date']); ?>" required><br>

        <button type="submit">Update Equipment</button>
    </form>
</div>

</body>
</html>
