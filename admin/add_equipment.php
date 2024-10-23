<?php
session_start();
require '../config/db.php';

$message = '';  // Initialize message variable
$redirect = false;  // Flag to show the redirect button

// Define the valid equipment types for each equipment name
$equipmentTypes = [
    'Scalpel' => ['Surgical Instruments', 'Consumables'],
    'Surgical Scissors' => ['Surgical Instruments', 'Consumables'],
    'Electrosurgical Unit' => ['Electrical Equipment'],
    'Anesthesia Machine' => ['Electrical Equipment'],
    'Infusion Pump' => ['Electrical Equipment'],
    'Surgical Drapes' => ['Consumables'],
    'Stethoscope' => ['Diagnostic Equipment'],
    'Surgical Gloves' => ['Consumables']
];

// Fetch existing equipment types for the dropdown
$equipment_types = $conn->query("SELECT DISTINCT equipment_type FROM equipment");

// Fetch existing equipment names for the dropdown
$equipment_names = $conn->query("SELECT id, equipment_name FROM equipment WHERE status = 'Available'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_name = $_POST['equipment_name'];
    $equipment_type = $_POST['equipment_type'];
    $valid_types = $equipmentTypes[$equipment_name]; // Fetch valid types based on the name

    if (!in_array($equipment_type, $valid_types)) {
        $message = "<p class='error-message'>Invalid equipment type selected for $equipment_name.</p>";
    } else {
        // Proceed to insert into the database
        $quantity = $_POST['quantity'];
        $status = 'available'; 
        $acquisition_date = $_POST['acquisition_date'];

        // Insert into equipment table
        $sql = "INSERT INTO equipment (equipment_name, equipment_type, quantity, status, acquisition_date) 
                VALUES ('$equipment_name', '$equipment_type', '$quantity', '$status', '$acquisition_date')";
        if ($conn->query($sql)) {
            $message = "<p class='success-message'>Equipment added successfully!</p>";
            $redirect = true;  // Show redirect button
        } else {
            $message = "<p class='error-message'>Error: " . $conn->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment</title>
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

        .success-message {
            color: #4CAF50;
            background-color: #e0f7fa;
            padding: 10px;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .error-message {
            color: red;
            background-color: #f9d6d5;
            padding: 10px;
            border: 2px solid red;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        /* Adding a subtle background icon */
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
    <h2>Add New Equipment</h2>

    <!-- Display success or error message -->
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <?php if (!$redirect): ?>
    <form method="POST" action="add_equipment.php">
        <select id="equipment_name" name="equipment_name" required onchange="updateEquipmentTypes()">
            <option value="">Select Equipment Name</option>
            <option value="Scalpel">Scalpel</option>
            <option value="Surgical Scissors">Surgical Scissors</option>
            <option value="Electrosurgical Unit">Electrosurgical Unit</option>
            <option value="Anesthesia Machine">Anesthesia Machine</option>
            <option value="Infusion Pump">Infusion Pump</option>
            <option value="Surgical Drapes">Surgical Drapes</option>
            <option value="Stethoscope">Stethoscope</option>
            <option value="Surgical Gloves">Surgical Gloves</option>
        </select><br>

        <select id="equipment_type" name="equipment_type" required>
            <option value="">Select Equipment Type</option>
            <!-- Options will be populated based on selection -->
        </select><br>

        <input type="number" name="quantity" placeholder="Quantity" required><br>

        <label for="acquisition_date">Acquisition Date:</label>
        <input type="date" name="acquisition_date" required><br>

        <button type="submit">Add Equipment</button>
    </form>

    <script>
        const equipmentTypes = {
            'Scalpel': ['Surgical Instruments', 'Consumables'],
            'Surgical Scissors': ['Surgical Instruments', 'Consumables'],
            'Electrosurgical Unit': ['Electrical Equipment'],
            'Anesthesia Machine': ['Electrical Equipment'],
            'Infusion Pump': ['Electrical Equipment'],
            'Surgical Drapes': ['Consumables'],
            'Stethoscope': ['Diagnostic Equipment'],
            'Surgical Gloves': ['Consumables']
        };

        function updateEquipmentTypes() {
            const equipmentName = document.getElementById("equipment_name").value;
            const typeSelect = document.getElementById("equipment_type");
            typeSelect.innerHTML = ""; // Clear existing options

            if (equipmentName && equipmentTypes[equipmentName]) {
                equipmentTypes[equipmentName].forEach(type => {
                    const option = document.createElement("option");
                    option.value = type;
                    option.textContent = type;
                    typeSelect.appendChild(option);
                });
            } else {
                // Option to select when no valid types available
                const option = document.createElement("option");
                option.value = "";
                option.textContent = "Select Equipment Type";
                typeSelect.appendChild(option);
            }
        }
    </script>

    <?php endif; ?>

    <!-- Dashboard button appears only after successful submission -->
    <a href="<?php echo ($_SESSION['user']['role'] === 'admin') ? '../admin_dashboard.php' : '../staff_dashboard.php'; ?>">
        <button>Back to Dashboard</button>
    </a>
</div>

</body>
</html>
