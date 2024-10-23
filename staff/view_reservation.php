<?php
session_start(); // Start session

// Include your database connection file
require '../config/db.php';

// Check if the user is logged in and is a staff member
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
    header("Location: login.php"); // Redirect to login if not staff
    exit();
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user']['id'];

// Fetch reservations made by the logged-in staff member
$reservations = $conn->query("SELECT equipment_usage.id, equipment_usage.equipment_id, equipment_usage.surgery_id, 
                                    equipment.equipment_name, surgeries.surgery_name, equipment_usage.usage_date, 
                                    equipment_usage.quantity
                            FROM equipment_usage 
                            JOIN equipment ON equipment_usage.equipment_id = equipment.id 
                            JOIN surgeries ON equipment_usage.surgery_id = surgeries.id 
                            WHERE equipment_usage.reserved_by = '$user_id'");


/*if ($reservations->num_rows > 0) {
    echo "<p>Reservations found for user ID: " . $user_id . "</p>";
} else {
    echo "<p>No reservations found for user ID: " . $user_id . "</p>";
}*/


// If the query fails, show the SQL error
if (!$reservations) {
    die("Error in SQL: " . $conn->error);
}

// Display debug information to ensure data is being fetched correctly (can remove this later)
//echo "Number of Reservations Found: " . $reservations->num_rows . "<br>";
//echo "Current User ID: " . $_SESSION['user']['id'];

// Initialize a message variable for success or error notifications
$message = '';

// Handle form submission for editing a reservation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $equipment_id = $_POST['equipment_id'];
    $surgery_id = $_POST['surgery_id'];
    $usage_date = $_POST['usage_date'];
    $quantity = $_POST['quantity'];

    // SQL to update the reservation details
    $sql = "UPDATE equipment_usage 
            SET equipment_id = '$equipment_id', 
                surgery_id = '$surgery_id', 
                usage_date = '$usage_date',
                quantity = '$quantity'
            WHERE id = '$reservation_id'";

    if ($conn->query($sql)) {
        $message = "<p class='success-message'>Reservation updated successfully!</p>";
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
    <title>View Reservations</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: auto;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        h2 {
            color: #2c3e50;
            font-size: 26px;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        .edit-button {
            background-color: #008080;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 14px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .edit-button:hover {
            background-color: #30D5C8;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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

        .done-button {
            background-color: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .done-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Reservations</h2>

    <!-- Display success or error message -->
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <?php if ($reservations->num_rows > 0): ?>
    <table>
        <tr>
            <th>Equipment Name</th>
            <th>Surgery Name</th>
            <th>Usage Date</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php while ($reservation = $reservations->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['equipment_name']); ?></td>
                <td><?php echo htmlspecialchars($reservation['surgery_name']); ?></td>
                <td><?php echo htmlspecialchars($reservation['usage_date']); ?></td>
                <td><?php echo htmlspecialchars($reservation['quantity']); ?></td>
                <td>
                    <form method="POST" action="view_reservation.php">
                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                        <input type="hidden" name="equipment_id" value="<?php echo $reservation['equipment_id']; ?>">
                        <input type="hidden" name="surgery_id" value="<?php echo $reservation['surgery_id']; ?>">
                        <input type="date" name="usage_date" value="<?php echo $reservation['usage_date']; ?>" required>
                        <input type="number" name="quantity" min="1" value="<?php echo $reservation['quantity']; ?>" required>
                        <button type="submit" class="edit-button">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No reservations found.</p>
    <?php endif; ?>

    <!-- Done button to go back to the dashboard -->
    <a href="../staff_dashboard.php">
        <button class="done-button">Back to Dashboard</button>
    </a>
</div>

</body>
</html>
