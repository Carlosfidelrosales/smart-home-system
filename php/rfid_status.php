<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST['status'] ?? '';
    file_put_contents("../device_cmds/rfid_status.txt", $status);
    echo "RFID status updated to: $status";
} else {
    echo "Invalid request.";
}
