<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['room']) && isset($_POST['state'])) {
        $room = $_POST['room'];
        $state = $_POST['state'];

        $validRooms = ['living_led', 'kitchen_led', 'bedroom_led', 'porch_led'];

        if (in_array($room, $validRooms)) {
            $filename = "../device_cmds/" . $room . ".txt";
            file_put_contents($filename, $state);
            echo "✅ File '$room.txt' updated to $state";
        } else {
            echo "❌ Invalid room name.";
        }
    } else {
        echo "❌ Missing room or state.";
    }
} else {
    echo "❌ Invalid request method.";
}
?>
