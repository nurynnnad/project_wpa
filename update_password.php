<?php
session_start(); // Start the session
require 'config/db.php'; // Include the database connection

// Initialize variables
$username = "";
$newPassword = "";
$error = ""; // Initialize error message

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // Get entered username
    $newPassword = $_POST['new_password']; // Get new password

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Prepare SQL query to update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $hashedPassword, $username);
        if ($stmt->execute()) {
            echo "Password updated successfully!";
        } else {
            $error = "Failed to update password.";
        }
        $stmt->close();
    } else {
        $error = "Failed to prepare statement.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #FFDEE9, #B5FFFC);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .update-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .update-container h2 {
            color: #2c3e50;
            font-size: 26px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .update-container input {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .update-container input:focus {
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
        }

        button:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: red;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="update-container">
    <h2>Update Password</h2>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required aria-label="Username">
        <input type="password" name="new_password" placeholder="New Password" required aria-label="New Password">
        <button type="submit">Update Password</button>
    </form>

    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
