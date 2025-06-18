<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST['status'] ?? '';
    file_put_contents("../device_cmds/pir_status.txt", $status);
    echo "PIR status updated to: $status";
} else {
    echo "Invalid request.";
}
?>
