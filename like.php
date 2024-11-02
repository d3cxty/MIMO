<?php
include("conn.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_id'])) {
    $upload_id = intval($_POST['upload_id']);

    // Check if the user has already liked this post
    $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND upload_id = ?");
    $stmt->bind_param('ii', $user_id, $upload_id);
    $stmt->execute();
    $stmt->bind_result($like_count);
    $stmt->fetch();
    $stmt->close();

    if ($like_count > 0) {
        // Unlike the post if it is already liked
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND upload_id = ?");
        $stmt->bind_param('ii', $user_id, $upload_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Like the post if it is not liked yet
        $stmt = $conn->prepare("INSERT INTO likes (user_id, upload_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $user_id, $upload_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit();
}
?>
