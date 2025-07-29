<?php
// Database configuration
$host = "localhost";         // Your database host (usually localhost)
$db_user = "root";           // Your MySQL username
$db_pass = "";               // Your MySQL password
$db_name = "missing_portal"; // Your database name

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
