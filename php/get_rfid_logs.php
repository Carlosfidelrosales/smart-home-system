<?php
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "final_project";

$conn = new mysqli($servername, $username, $password, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch last 10 entries
$sql = "SELECT uid, result, timestamp FROM rfid_logs ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

header('Content-Type: application/json');
echo json_encode($logs);
?>
