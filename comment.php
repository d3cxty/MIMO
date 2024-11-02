<?php
include("conn.php"); // Include your database connection file
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
    $upload_id = $_POST['upload_id']; // Get the ID of the upload to which the comment is being added
    $comment_text = trim($_POST['comment']); // Get the comment text and trim whitespace

    // Validate comment
    if (empty($comment_text)) {
        $_SESSION['message'] = 'Comment cannot be empty.';
        header("Location: index.php");
        exit();
    }

    // Insert the comment into the database
    $stmt = $conn->prepare("INSERT INTO comments (user_id, upload_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $user_id, $upload_id, $comment_text);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Comment posted successfully.';
    } else {
        $_SESSION['message'] = 'Failed to post comment. Please try again.';
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the index page
    header("Location: index.php");
    exit();
} else {
    // If accessed directly without submitting the form, redirect to index
    header("Location: index.php");
    exit();
}
?>
