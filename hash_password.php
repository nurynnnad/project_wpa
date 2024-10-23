<?php
// Include your database connection
require 'config/db.php';

// Example users with plaintext passwords (change these to your real users)
$users = [
    'admin_one' => 'adminpass123',
    'staff_one' => 'staffpass456',
];

// Loop through the users, hash their passwords, and update the database
foreach ($users as $username => $password) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare the SQL to update the user with the hashed password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param('ss', $hashed_password, $username);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        echo "Password for $username updated successfully.<br>";
    } else {
        echo "Error updating password for $username.<br>";
    }
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>
