<?php
include("conn.php");
session_start();

// Check if the user is logged in and if the delete request is set
if (!isset($_SESSION["user_id"])) {
    echo "User not logged in.";
    header("Location: login.php");
    exit();
}

if (!isset($_POST["delete"]) || !isset($_POST['post_id'])) {
    echo "Delete request or post ID is missing.";
    exit();
}

$user_id = $_SESSION["user_id"];
$post_id = $_POST['post_id'];

// Debugging - Check received post ID and user ID
echo "Received post ID: " . htmlspecialchars($post_id) . "<br>";
echo "User ID: " . htmlspecialchars($user_id) . "<br>";

// Prepare the SQL statement to delete the post
$stmt = $conn->prepare('DELETE FROM uploads WHERE upload_id = ? AND user_id = ?');

// Check if the statement preparation was successful
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind the parameters, both should be integers
$stmt->bind_param('ii', $post_id, $user_id);

// Execute the statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Successful deletion
        echo "Post deleted successfully.";
        header('Location: profile.php');
        exit();
    } else {
        // No rows affected, meaning no matching post was found
        echo "No matching post found or you do not have permission to delete this post.";
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
