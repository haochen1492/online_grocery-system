<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'online_grocery';

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $database);
// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>