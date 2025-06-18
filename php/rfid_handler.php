<?php
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "final_project";

$conn = new mysqli($servername, $username, $password, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle RFID status update
    $status = $_POST['status'] ?? '';
    $uid = $_POST['uid'] ?? ''; // Get UID if available

    if (!empty($status)) {
        // Write to text file
        file_put_contents("../device_cmds/rfid_status.txt", $status);

        // Save to database (only if UID is given)
        if (!empty($uid)) {
            $stmt = $conn->prepare("INSERT INTO rfid_logs (uid, result) VALUES (?, ?)");
            $stmt->bind_param("ss", $uid, $status);
            if ($stmt->execute()) {
                echo "RFID status updated and logged.";
            } else {
                echo "RFID status updated, but logging failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "RFID status updated, but UID not logged.";
        }
    } else {
        echo "No status provided.";
    }

} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Handle fetching latest RFID logs
    $sql = "SELECT uid, result, timestamp FROM rfid_logs ORDER BY id DESC LIMIT 10";
    $result = $conn->query($sql);

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($logs);
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
