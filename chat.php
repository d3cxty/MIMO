<?php
include("conn.php");
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$logged_in_user_id = $_SESSION['user_id'];
$chat_with_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Fetch the user list to chat with, excluding the logged-in user, and get their last message
$user_list_stmt = $conn->prepare("
    SELECT u.user_id, u.username, u.profile_picture, 
           (SELECT message FROM chats c 
            WHERE (c.user_id = u.user_id AND c.recipient_id = ?) 
               OR (c.user_id = ? AND c.recipient_id = u.user_id) 
            ORDER BY c.created_at DESC LIMIT 1) AS last_message 
    FROM users u 
    WHERE u.user_id != ?
");
$user_list_stmt->bind_param('iii', $logged_in_user_id, $logged_in_user_id, $logged_in_user_id);
$user_list_stmt->execute();
$user_list_result = $user_list_stmt->get_result();

// Fetch the specific user to chat with if a user ID is provided
$user_to_chat_with = null;
if ($chat_with_user_id) {
    $chat_user_stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $chat_user_stmt->bind_param("i", $chat_with_user_id);
    $chat_user_stmt->execute();
    $chat_user_result = $chat_user_stmt->get_result();
    $user_to_chat_with = $chat_user_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <img src="" typ alt="">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiMo Chat</title>
    <link rel="stylesheet" href="public/css/tailwind.css">
    <link rel="stylesheet" href="public/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #ffffff;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 20%;
            overflow-y: auto;
            background: #f9f9f9;
            padding-top: 60px; /* Aligning with header */
        }

        .main-content {
            margin-left: 20%;
            padding: 20px;
            width: 80%;
        }

        .chat-container {
            display: flex;
            height: 80vh;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-list {
            width: 30%;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .user-list-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .user-list-item:hover {
            background-color: #f0f0f0;
        }

        .user-profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: scroll;
            background: #f9f9f9;
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .chat-message strong {
            color: #1a202c;
        }

        .send-button {
            display: block;
            width: 100%;
            margin-top: 10px;
            background-color: purple;
        }

        .no-messages {
            color: #aaa;
            text-align: center;
            margin-top: 50px;
        }
        
    </style>
</head>
<body class="bg-white">

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between p-4">
            <div class="text-2xl font-bold text-yellow-500">MiMo</div>
            <form method="GET" class="flex w-1/2">
                <input type="text" name="search" placeholder="Search..." class="border rounded-lg p-2 w-full outline-yellow-500">
                <button type="submit" class="ml-2 bg-yellow-500 text-white p-2 rounded-lg">Search</button>
            </form>
            <div class="flex space-x-4">
                <span class="text-gray-600 cursor-pointer"><i class="fas fa-bell text-blue-500"></i></span>
                <span class="text-gray-600 cursor-pointer"><i class="fas fa-user text-green-500"></i></span>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav>
            <ul class="space-y-8 p-4 pt-[80px]">
                <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                    <i class="fas fa-home text-red-500"></i>
                    <span><a href="index.php">Home</a></span>
                </li>
                <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                    <i class="fas fa-folder text-yellow-500"></i>
                    <span>Categories</span>
                </li>
                <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                    <i class="fas fa-message text-purple-1000"></i>
                    <span><a href="chat.php">Chat</a></span>
                </li>
                <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                    <i class="fas fa-upload text-orange-500"></i>
                    <span><a href="upload.php">Upload</a></span>
                </li>
                <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                    <i class="fas fa-user text-purple-500"></i>
                    <span><a href="profile.php">Profile</a></span>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="chat-container">
            <!-- User List -->
            <aside class="user-list">
                <h2 class="text-xl font-semibold mb-4">Chats</h2>
                <ul class="mb-6">
                    <?php while ($user = $user_list_result->fetch_assoc()): ?>
                        <li class="user-list-item">
                            <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: 'https://via.placeholder.com/40'); ?>" 
                                 alt="Profile Picture" class="user-profile-picture">
                            <div>
                                <a href="chat.php?user_id=<?php echo $user['user_id']; ?>" class="text-blue-500 hover:underline">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </a>
                                <p class="text-gray-500 text-sm">
                                    <?php echo htmlspecialchars($user['last_message'] ?: 'No messages yet.'); ?>
                                </p>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </aside>

            <!-- Chat Section -->
            <div class="chat-box">
                <?php if ($user_to_chat_with): ?>
                    <h2 class="text-2xl font-semibold mb-4">Chat with <?php echo htmlspecialchars($user_to_chat_with['username']); ?></h2>
                    <div id="chat-box-content">
                        <?php
                        // Fetch chat messages between the logged-in user and the selected user
                        $sql = "SELECT c.message, c.created_at, u.username 
                                FROM chats c 
                                JOIN users u ON c.user_id = u.user_id 
                                WHERE (c.user_id = ? AND c.recipient_id = ?) 
                                OR (c.user_id = ? AND c.recipient_id = ?)
                                ORDER BY c.created_at ASC";

                        $stmt = $conn->prepare($sql);

                        if (!$stmt) {
                            die("Query error: " . $conn->error);
                        }

                        $stmt->bind_param("iiii", $logged_in_user_id, $chat_with_user_id, $chat_with_user_id, $logged_in_user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<div class='chat-message'><strong>" . htmlspecialchars($row['username']) . ":</strong> " . htmlspecialchars($row['message']) . " <span class='text-gray-500 text-sm'>(" . $row['created_at'] . ")</span></div>";
                            }
                        } else {
                            echo "<p class='no-messages'>No messages yet.</p>";
                        }
                        ?>
                    </div>
                    <form id="chat-form" class="mt-4">
                        <textarea id="message" rows="3" class="border rounded-lg p-2 w-full" placeholder="Type your message..."></textarea>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg mt-2 send-button">Send</button>
                    </form>
                <?php else: ?>
                    <p class="text-red-500 no-messages">Select a user to start chatting.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Handle form submission with AJAX
        $('#chat-form').submit(function(e) {
            e.preventDefault();
            const message = $('#message').val();
            if (message.trim() !== '') {
                $.ajax({
                    url: 'send_message.php',
                    type: 'POST',
                    data: {
                        message: message,
                        recipient_id: <?php echo $chat_with_user_id ?: 'null'; ?> // Handle null if no user is selected
                    },
                    success: function(response) {
                        $('#message').val('');
                        $('#chat-box-content').append(response);
                        $('#chat-box').scrollTop($('#chat-box-content')[0].scrollHeight);
                    }
                });
            }
        });
    </script>

</body>
</html>

<?php
if (isset($stmt)) $stmt->close();
$user_list_stmt->close();
?>
