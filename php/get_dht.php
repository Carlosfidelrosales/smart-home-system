<?php

// $conn = new mysqli("localhost", "root", "", "final_project");
// $result = $conn->query("SELECT * FROM dht_data ORDER BY id DESC LIMIT 1");
// $row = $result->fetch_assoc();
// date_default_timezone_set('Asia/Manila');
// header('Content-Type: application/json');
// echo json_encode([
//     'temperature' => $row['temperature'],
//     'humidity' => $row['humidity'],
//     'status' => $row['status'],
//     'timestamp' => $row['created_at'] ?? date("Y-m-d H:i:s")  // fallback if null
// ]);
$servername = "localhost";
$username = "root";
$password = "";
$database = "final_project";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed."]));
}

$sql = "SELECT temperature, humidity, status, timestamp FROM dht_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $formattedTimestamp = date("F j, Y \\a\\t g:i:s A", strtotime($row["timestamp"])); // Example: June 15, 2025 at 10:42:31 AM
    echo json_encode([
        "temperature" => $row["temperature"],
        "humidity" => $row["humidity"],
        "status" => $row["status"],
        "timestamp" => $formattedTimestamp
    ]);
} else {
    echo json_encode(["error" => "No data found."]);
}
?>

