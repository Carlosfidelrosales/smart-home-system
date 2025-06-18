<?php
date_default_timezone_set("Asia/Manila");

$conn = new mysqli("localhost", "root", "", "final_project");

$sql = "SELECT id, temperature, humidity, status, timestamp FROM dht_data ORDER BY timestamp DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
