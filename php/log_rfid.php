<?php
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "final_project";

// Connect
$conn = new mysqli($servername, $username, $password, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get UID and result
$uid = $_POST['uid'] ?? '';
$result = $_POST['result'] ?? ''; // 'Granted' or 'Denied'

// Save
$stmt = $conn->prepare("INSERT INTO rfid_logs (uid, result, timestamp) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $uid, $result);
$stmt->execute();
$stmt->close();

echo "RFID log saved.";
?>
