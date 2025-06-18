<?php
sleep(5);  // Wait for 5 seconds

$filePath = "../device_cmds/rfid_status.txt";
file_put_contents($filePath, "Access Denied");
?>
