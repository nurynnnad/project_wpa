<?php
session_start();
require '../config/db.php'; // Database connection

$message = ''; // Message initialization

// Fetch available equipment
$sql_equipment = "SELECT id, equipment_name, quantity 
                  FROM equipment 
                  WHERE quantity > 0 AND status = 'available'";
$equipment = $conn->query($sql_equipment);

// Fetch all surgery types
$sql_surgeries = "SELECT id, surgery_name FROM surgeries";
$surgeries = $conn->query($sql_surgeries);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = $_POST['equipment_id'];
    $surgery_id = $_POST['surgery_id'];
    $usage_date = $_POST['usage_date'];
    $quantity = $_POST['quantity'];

    // Insert the reservation into the database
    $stmt = $conn->prepare("
        INSERT INTO equipment_usage (equipment_id, surgery_id, usage_date, quantity, reserved_by, status) 
        VALUES (?, ?, ?, ?, ?, 'in-use')
    ");
    $stmt->bind_param('iisii', $equipment_id, $surgery_id, $usage_date, $quantity, $_SESSION['user']['id']);

    if ($stmt->execute()) {
        $message = "<p class='success-message'>Reservation successful for <strong>{$quantity}</strong> unit(s)!</p>";
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
    <title>Reserve Equipment for Surgery</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        select, input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Reserve Equipment for Surgery</h2>

    <!-- Display Success/Error Message -->
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <form method="POST" action="reserve_equipment.php">
        <!-- Equipment Dropdown -->
        <label for="equipment_id">Select Equipment:</label>
        <select id="equipment_id" name="equipment_id" required>
            <option value="">Select Equipment</option>
            <?php while ($item = $equipment->fetch_assoc()): ?>
                <option value="<?php echo $item['id']; ?>">
                    <?php echo htmlspecialchars($item['equipment_name'] . ' (Available: ' . $item['quantity'] . ')'); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Surgery Dropdown -->
        <label for="surgery_id">Select Surgery Type:</label>
        <select id="surgery_id" name="surgery_id" required>
            <option value="">Select Surgery</option>
            <?php while ($surgery = $surgeries->fetch_assoc()): ?>
                <option value="<?php echo $surgery['id']; ?>">
                    <?php echo htmlspecialchars($surgery['surgery_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Quantity Input -->
        <label for="quantity">Quantity to Reserve:</label>
        <input type="number" id="quantity" name="quantity" min="1" required>

        <!-- Usage Date Input -->
        <label for="usage_date">Usage Date:</label>
        <input type="date" id="usage_date" name="usage_date" required><br>

        <button type="submit">Reserve Equipment</button>
    </form>

    <a href="../staff_dashboard.php" style="margin-top: 15px; display: block;">
        <button class="done-button">Back to Dashboard</button>
    </a>
</div>

</body>
</html>
