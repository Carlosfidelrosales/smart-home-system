<?php
$servername = "localhost";
$username = "root";
$password = ""; // use your phpMyAdmin password
$database = "final_project";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
