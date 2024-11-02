<?php
include("conn.php");
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all users except the logged-in user
$sql = "SELECT user_id, username FROM users WHERE user_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p><a href='chat.php?user_id=" . $row['user_id'] . "'>" . htmlspecialchars($row['username']) . "</a></p>";
    }
} else {
    echo "<p>No other users found.</p>";
}

$stmt->close();
?>
