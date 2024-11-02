<?php
include("conn.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    $recipient_id = intval($_POST['recipient_id']);
    $user_id = $_SESSION['user_id']; // The currently logged-in user's ID

    if (!empty($message) && $recipient_id && $user_id) {
        // Prepare and execute the insertion query
        $sql = "INSERT INTO chats (user_id, recipient_id, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $recipient_id, $message);

        if ($stmt->execute()) {
            // Echo the message back to append it to the chatbox
            echo "<div class='chat-message'><strong>" . htmlspecialchars($_SESSION['username']) . ":</strong> " . htmlspecialchars($message) . " <span class='text-gray-500 text-sm'>(just now)</span></div>";
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error: Invalid message or recipient.";
    }
}
?>
