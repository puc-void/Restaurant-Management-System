<?php
$host = "localhost";      // Usually localhost for XAMPP
$db   = "online_food";    // Your database name
$user = "root";           // Default XAMPP MySQL user
$pass = "";               // Default XAMPP MySQL password (empty)

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
