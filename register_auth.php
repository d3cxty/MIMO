<?php
include("conn.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Sanitize and validate username
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        die("Invalid username. Only letters, numbers, and underscores are allowed, and it must be between 3 and 20 characters.");
    }

    // Sanitize and validate email
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    // Sanitize and hash the password
    $password = htmlspecialchars($_POST["password"]);
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username or email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Debug line added
    }
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        die("Username or email already exists. Please choose another one.");
    }

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Debug line added
    }
    $stmt->bind_param('sss', $username, $email, $hash);

    if ($stmt->execute()) {
        echo "Registration complete.";
        header("Location: login.php");
        exit();
    } else {
        echo "Something went wrong. Please try again.";
    }

    $stmt->close();
    $conn->close();
}
?>