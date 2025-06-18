<?php
session_start();
include '../user_system/db.php'; // Your DB connection

// ---------- LOGIN ----------
if (isset($_POST['login'])) {
    $identity = $_POST['login_identity']; // can be username or email
    $password = $_POST['login_password'];

    // Match either username OR email
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identity, $identity);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}


// ---------- SIGNUP ----------
if (isset($_POST['signup'])) {
    $username = $_POST['signup_username'];
    $email = $_POST['signup_email'];
    $password = password_hash($_POST['signup_password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email already exists. Please use a different one.";
    } else {
        // Proceed with insert
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $success = "Signup successful. You can now log in.";
        } else {
            $error = "Signup failed. Username might already exist.";
        }
        $stmt->close();
    }
    $check->close();
}



// ---------- LOGOUT ----------
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Smart Home</title>
  <link rel="stylesheet" href="../css/style.css">
  <script>
    function toggleForms() {
        var loginForm = document.getElementById('loginForm');
        var signupForm = document.getElementById('signupForm');
        loginForm.style.display = (loginForm.style.display === 'none') ? 'block' : 'none';
        signupForm.style.display = (signupForm.style.display === 'none') ? 'block' : 'none';
    }
  </script>
</head>
<body>

<?php if (!isset($_SESSION['user_id'])): ?>
  
  <div class="auth-container">
    <h1 id = "header">Smart Home</h1>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <!-- Login Form -->
    <div id="loginForm">
      <form method="POST">
        <h3>Login</h3>
        <input type="text" name="login_identity" placeholder="Enter Email or Username" required>
        <input type="password" name="login_password" placeholder="Enter Password" required>
        <input type="submit" name="login" value="Login">
      </form>
      <div class="auth-footer">
        <p>Don't have an account? <br><button onclick="toggleForms()">Sign Up</button></p>
      </div>
      
    </div>

    <!-- Signup Form -->
    <div id="signupForm" style="display:none;">
      <form method="POST">
        <h3>Sign Up</h3>
        Username <input type="text" name="signup_username" required><br>
        Email <input type="email" name="signup_email" required><br>
        Password <input type="password" name="signup_password" required><br>
        <input type="submit" name="signup" value="Sign Up">
      </form>
      <div class="auth-footer">
        <p>Already have an account? <button onclick="toggleForms()">Login</button></p>
      </div>
      
    </div>

<?php else: ?>


<?php
// Logged-in: load dashboard data
$living = trim(file_get_contents("../device_cmds/living_led.txt"));
$kitchen = trim(file_get_contents("../device_cmds/kitchen_led.txt"));
$bedroom = trim(file_get_contents("../device_cmds/bedroom_led.txt"));
$pir = trim(file_get_contents("../device_cmds/pir_status.txt"));
$porch = trim(file_get_contents("../device_cmds/porch_led.txt"));
$rfid = trim(file_get_contents("../device_cmds/rfid_status.txt"));

$result = $conn->query("SELECT * FROM dht_data ORDER BY id DESC LIMIT 1");
$row = $result->fetch_assoc();
$temperature = $row['temperature'];
$humidity = $row['humidity'];
$status = $row['status'];
?>

<div class="container">
  <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
  <a href="?logout=1" class="logout-btn">🚪 Logout</a>

  <h2>Smart Home LED Control</h2>
  <div class="toggle-grid">
    <!-- LED toggles... (same as your current code) -->
    <div class="toggle-box">
      <label class="switch">
        <input type="checkbox" id="living_led" onchange="toggleLED(this, 'living_led')" <?= $living === 'ON' ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
      <div class="label-text">Living Room</div>
    </div>
    <div class="toggle-box">
      <label class="switch">
        <input type="checkbox" id="kitchen_led" onchange="toggleLED(this, 'kitchen_led')" <?= $kitchen === 'ON' ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
      <div class="label-text">Kitchen</div>
    </div>
    <div class="toggle-box">
      <label class="switch">
        <input type="checkbox" id="bedroom_led" onchange="toggleLED(this, 'bedroom_led')" <?= $bedroom === 'ON' ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
      <div class="label-text">Bedroom</div>
    </div>
    <div class="toggle-box">
      <label class="switch">
        <input type="checkbox" id="porch_led" onchange="toggleLED(this, 'porch_led')" <?= $porch === 'ON' ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
      <div class="label-text">Porch</div>
    </div>
  </div>

  <div id="led-counter">LEDs Turned On: 0</div>

  <div class="status-card">
    <h3>🌡️ DHT11 Readings</h3>
    <p>Temperature: <span id="temp-val">Loading...</span></p>
    <p>Humidity: <span id="humidity-val">Loading...</span></p>
    <p>Status: <span id="status-val">Loading...</span></p>
    <p id="last-updated"><em>Updated as of: Loading...</em></p>
  </div>

  <div class="status-card">
    <h3>🚶 PIR Motion</h3>
    <p>Motion: <strong><?= htmlspecialchars($pir) ?></strong></p>
  </div>

  <div class="status-card">
    <h3>🔐 RFID Access</h3>
    <p>Status: <strong id="rfid-status"><?= htmlspecialchars($rfid) ?></strong></p>
  </div>
  <a href="/finale_project/php/temp_data_history.php" target="_blank">📈 View Temperature & Humidity History</a>
</div>

<script>
function toggleLED(checkbox, room) {
  const state = checkbox.checked ? 'ON' : 'OFF';
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "update_led.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("room=" + room + "&state=" + state);
  xhr.onload = () => {
    if (xhr.status === 200) {
      console.log("✅ Updated:", xhr.responseText);
    } else {
      console.error("❌ Error:", xhr.status);
    }
  };
  updateLEDCount();
}

function updateLEDCount() {
  const checkboxes = document.querySelectorAll("input[type=checkbox]");
  let count = 0;
  checkboxes.forEach(cb => { if (cb.checked) count++; });
  document.getElementById("led-counter").textContent = `LEDs Turned On: ${count}`;
}

function fetchDHTData() {
  fetch("get_dht.php")
    .then(res => res.json())
    .then(data => {
      document.getElementById("temp-val").textContent = `${data.temperature} °C`;
      document.getElementById("humidity-val").textContent = `${data.humidity} %`;
      const statusEl = document.getElementById("status-val");
      statusEl.textContent = data.status;
      statusEl.style.color = data.status === "Danger" ? "red" : data.status === "High" ? "orange" : "green";
      document.getElementById("last-updated").innerHTML = `<em>Updated as of: ${data.timestamp}</em>`;
    })
    .catch(err => console.error("Error fetching DHT data:", err));
}

let lastRFIDStatus = "";

function pollRFIDStatus() {
  fetch("../device_cmds/rfid_status.txt?" + new Date().getTime()) // prevent cache
    .then(response => response.text())
    .then(status => {
      status = status.trim();
      if (status !== lastRFIDStatus) {
        document.getElementById("rfid-status").textContent = status;
        lastRFIDStatus = status;
      }
    })
    .catch(err => console.error("RFID polling error:", err))
    .finally(() => {
      setTimeout(pollRFIDStatus, 300); // recursive polling every 300ms
    });
}

function fetchRFIDLogs() {
  fetch("get_rfid_logs.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#rfid-log-table tbody");
      tbody.innerHTML = ""; // Clear old rows
      data.forEach(log => {
        const row = `<tr>
          <td>${log.uid}</td>
          <td style="color: ${log.result === 'Granted' ? 'green' : 'red'};">${log.result}</td>
          <td>${log.timestamp}</td>
        </tr>`;
        tbody.innerHTML += row;
      });
    })
    .catch(err => console.error("Failed to load RFID logs:", err))
    .finally(() => {
      setTimeout(fetchRFIDLogs, 1000); // Refresh every second
    });
}



setInterval(fetchDHTData, 10000);
window.onload = () => {
  fetchDHTData();
  updateLEDCount();
  pollRFIDStatus();
  fetchRFIDLogs();
};

</script>

<?php endif; ?>
</body>
</html>
