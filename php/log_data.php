<?php
if (isset($_GET["temperature"]) && isset($_GET["humidity"])) {
    $temperature = $_GET["temperature"];
    $humidity = $_GET["humidity"];
    $status = $_GET['status'];

    // Calculate temperature status
    if ($temperature <= 30) {
        $status = 'Normal';
    } elseif ($temperature <= 35) {
        $status = 'High';
    } else {
        $status = 'Danger';
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database_name = "final_project";

    $connection = new mysqli($servername, $username, $password, $database_name);

    if ($connection->connect_error) {
        die("MySQL connection failed: " . $connection->connect_error);
    }

    // Use prepared statement for security
    $stmt = $connection->prepare("INSERT INTO dht_data (temperature, humidity, status) VALUES (?, ?, ?)");
    $stmt->bind_param("dds", $temperature, $humidity, $status);

    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
} else {
    echo "temperature or humidity is not set in the HTTP request";
}
?>
