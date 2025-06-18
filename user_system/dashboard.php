<?php
include 'db.php';
session_start();

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<p style='color:red;'>Invalid password.</p>";
        }
    } else {
        echo "<p style='color:red;'>Username not found.</p>";
    }
    $stmt->close();
}

// Handle Signup
if (isset($_POST['signup'])) {
    $username = $_POST['signup_username'];
    $password = password_hash($_POST['signup_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Signup successful. You can now log in.</p>";
    } else {
        echo "<p style='color:red;'>Error: Username might already exist.</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login / Signup</title>
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

<h2>User System</h2>

<!-- Login Form -->
<div id="loginForm">
  <form method="POST">
    <h3>Login</h3>
    Username: <input type="text" name="login_username" required><br>
    Password: <input type="password" name="login_password" required><br>
    <input type="submit" name="login" value="Login">
  </form>
  <p>Don't have an account? <button onclick="toggleForms()">Sign Up</button></p>
</div>

<!-- Signup Form (hidden initially) -->
<div id="signupForm" style="display:none;">
  <form method="POST">
    <h3>Sign Up</h3>
    Username: <input type="text" name="signup_username" required><br>
    Password: <input type="password" name="signup_password" required><br>
    <input type="submit" name="signup" value="Sign Up">
  </form>
  <p>Already have an account? <button onclick="toggleForms()">Login</button></p>
</div>

</body>
</html>
