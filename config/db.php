<?php
// config/db.php

$servername = "localhost";
$username = "root";  // MySQL username
$password = "";      // MySQL password (leave blank if none)
$dbname = "surgical_equipment_management";  // database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully!";
}
?>
