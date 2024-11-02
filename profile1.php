<?php
include("conn.php"); // Include your database connection file
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user information
$stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error); // Error check for prepared statement
}

$stmt->bind_param('s', $username);
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error); // Error check for execution
}

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_info = $result->fetch_assoc();
} else {
    $user_info = null;
}

$email = $user_info ? $user_info['email'] : 'N/A';
$profile_picture = $user_info ? $user_info['profile_picture'] : 'https://via.placeholder.com/96';

// Handle search input
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
}

// Fetch uploads from the database for the current user only, with search functionality
$sql = "SELECT u.upload_id, u.user_id, u.upload_type, u.file_path, u.caption, u.created_at, 
        users.username, users.email, users.profile_picture, users.bio
        FROM uploads u
        JOIN users ON u.user_id = users.user_id
        WHERE u.user_id = ?";

if (!empty($searchQuery)) {
    $sql .= " AND (u.caption LIKE ? OR u.upload_type = 'quote' AND u.caption LIKE ?)";
}

$sql .= " ORDER BY u.created_at DESC";

// Prepare the statement with error checking
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement for uploads: " . $conn->error); // Error check for prepared statement
}

// Bind parameters based on whether a search query is provided
if (!empty($searchQuery)) {
    $searchTerm = '%' . $searchQuery . '%';
    $stmt->bind_param('iss', $user_id, $searchTerm, $searchTerm);
} else {
    $stmt->bind_param('i', $user_id);
}

// Execute the query and handle errors
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error); // Error check for execution
}

$result = $stmt->get_result();

if ($result === false) {
    die("Error fetching result: " . $conn->error); // Error check for fetching results
}

$uploads = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uploads[] = $row;
    }
}

// Fetch likes information
$likes = [];
$like_stmt = $conn->prepare("SELECT upload_id, COUNT(*) as like_count FROM likes GROUP BY upload_id");
$like_stmt->execute();
$like_result = $like_stmt->get_result();
while ($like_row = $like_result->fetch_assoc()) {
    $likes[$like_row['upload_id']] = $like_row['like_count'];
}

// Fetch user's likes
$user_likes = [];
$user_like_stmt = $conn->prepare("SELECT upload_id FROM likes WHERE user_id = ?");
$user_like_stmt->bind_param('i', $user_id);
$user_like_stmt->execute();
$user_like_result = $user_like_stmt->get_result();
while ($user_like_row = $user_like_result->fetch_assoc()) {
    $user_likes[] = $user_like_row['upload_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            overflow-y: auto;
        }

        .main-content {
            margin-left: 25%;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .upload-image {
            width: 70%;
            height: 70%;
            object-fit: contain;
            border-radius: 8px;
        }

        blockquote {
            background-color: #f9f9f9;
            border-left: 4px solid #fbbf24;
            padding: 16px;
            border-radius: 8px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between p-4">
            <div class="text-2xl font-bold text-yellow-500">MiMo</div>
            <form method="GET" class="flex">
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search your posts..." class="border rounded-lg p-2 w-1/2 outline-yellow-500">
                <button type="submit" class="bg-yellow-500 text-white p-2 ml-2 rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div class="flex space-x-4">
                <span class="text-gray-600 cursor-pointer"><i class="fas fa-bell text-blue-500"></i></span>
                <span class="text-gray-600 cursor-pointer"><i class="fas fa-user text-green-500"></i></span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto flex mt-4">
        <!-- Sidebar -->
        <aside class="sidebar w-1/4 p-4 pt-[200px] pl-[60px] bg-white">
            <nav>
                <ul class="space-y-8">
                    <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <i class="fas fa-home text-red-500"></i>
                        <span><a href="index.php">Home</a></span>
                    </li>
                    <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <i class="fas fa-folder text-yellow-500"></i>
                        <span>Categories</span>
                    </li>
                    <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <i class="fas fa-message text-yellow-500"></i>
                        <span>Chat</span>
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

        <!-- Profile Content -->
        <main class="main-content w-3/4 p-4">
            <!-- Profile Header -->
            <div class="bg-white p-6 rounded-md mb-6 shadow-md">
                <div class="flex items-center mb-4">
                    <div class="bg-gray-300 rounded-full overflow-hidden mr-4">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="w-12 h-12 object-cover rounded-full">
                    </div>
                    <div>
                        <h1 class="text-3xl font-semibold"><?php echo htmlspecialchars($user_info['username'] ?? 'User'); ?></h1>
                        <p class="text-gray-600">Email: <?php echo htmlspecialchars($email); ?></p>
                        <p class="text-gray-600">Joined: January 2024</p>
                        <div class="mt-4 flex space-x-2">
                            <button id="editProfileButton" class="bg-yellow-500 text-white p-2 rounded-lg hover:bg-yellow-600">
                                <i class="fas fa-edit mr-2"></i> Edit Profile
                            </button>
                            <form action="profile.php" method="POST" class="inline">
                                <button type="submit" name="logout" class="bg-red-500 text-white p-2 rounded-lg hover:bg-red-600">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold">About Me</h2>
                    <p class="text-gray-700 mt-2"><?php echo $user_info['bio']; ?></p>
                </div>
            </div>

            <!-- User Posts -->
            <h2 class="text-2xl font-semibold mb-4">Recent Posts</h2>
            <div class="flex flex-col space-y-6">
                <?php if (empty($uploads)): ?>
                    <p class="text-gray-500">No posts found.</p>
                <?php else: ?>
                    <?php foreach ($uploads as $upload): ?>
                        <div class="bg-white p-6 rounded-md mb-6 shadow-md">
                            <form action="delete.php" method="post" class="flex justify-between items-start">
                                <input type="hidden" name="post_id" value="<?php echo $upload['upload_id']; ?>">
                                <button class="text-red-500 font-semibold" name="delete" type="submit">Delete</button>
                            </form>
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gray-300 rounded-full overflow-hidden">
                                    <img src="<?php echo !empty($upload['profile_picture']) ? htmlspecialchars($upload['profile_picture']) : 'https://via.placeholder.com/96'; ?>" 
                                         alt="Profile Picture" class="w-full h-full object-cover rounded-full">
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($upload['username']); ?></h2>
                                    <p class="text-gray-500"><?php echo htmlspecialchars($upload['created_at']); ?></p>
                                </div>
                            </div>

                            <?php if ($upload['upload_type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($upload['file_path']); ?>" alt="Upload" class="upload-image mb-4">
                                <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($upload['caption']); ?></p>
                            <?php elseif ($upload['upload_type'] === 'quote'): ?>
                                <blockquote class="bg-gray-100 p-4 border-l-4 border-yellow-500 italic text-base mb-4">
                                    <p><?php echo htmlspecialchars($upload['caption']); ?></p>
                                </blockquote>
                            <?php endif; ?>
                            <div class="flex space-x-4 mt-2">
                                <!-- Like Button -->
                                <form action="like.php" method="POST" class="inline">
                                    <input type="hidden" name="upload_id" value="<?php echo $upload['upload_id']; ?>">
                                    <button type="submit" class="flex items-center space-x-1 text-gray-600 cursor-pointer">
                                        <i class="fas fa-heart <?php echo in_array($upload['upload_id'], $user_likes) ? 'text-pink-500' : ''; ?>"></i>
                                        <span><?php echo isset($likes[$upload['upload_id']]) ? $likes[$upload['upload_id']] : 0; ?></span>
                                    </button>
                                </form>
                                <!-- Comment Button -->
                                <button class="flex items-center space-x-1 text-gray-600 cursor-pointer" onclick="toggleComments(<?php echo $upload['upload_id']; ?>)">
                                    <i class="fas fa-comment text-blue-500"></i>
                                    <span>Comment</span>
                                </button>
                            </div>

                            <!-- Comment Section -->
                            <div id="comments-<?php echo $upload['upload_id']; ?>" class="hidden mt-4">
                                <form action="comment.php" method="POST">
                                    <input type="hidden" name="upload_id" value="<?php echo $upload['upload_id']; ?>">
                                    <textarea name="comment" rows="2" class="border p-2 rounded-lg w-full" placeholder="Add a comment..."></textarea>
                                    <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-lg mt-2">Post Comment</button>
                                </form>
                                <!-- Display existing comments -->
                                <?php
                                $comment_stmt = $conn->prepare("SELECT c.comment_text, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.upload_id = ? ORDER BY c.created_at DESC");
                                $comment_stmt->bind_param('i', $upload['upload_id']);
                                $comment_stmt->execute();
                                $comment_result = $comment_stmt->get_result();
                                if ($comment_result->num_rows > 0):
                                    while ($comment = $comment_result->fetch_assoc()): ?>
                                        <p class="mt-2"><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                    <?php endwhile;
                                else: ?>
                                    <p class="text-gray-500 mt-2">No comments yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg w-1/3 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">Edit Profile</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="editprofile.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4 text-center">
                    <label for="profilePicture" class="block text-gray-700 mb-2">Profile Picture:</label>
                    <div class="relative w-24 h-24 mx-auto mb-4">
                        <img id="profilePicturePreview" src="https://via.placeholder.com/96" alt="Profile Picture Preview" class="w-full h-full object-cover rounded-full border">
                        <label for="profilePicture" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white cursor-pointer rounded-full">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    <input type="file" id="profilePicture" name="profilePicture" class="hidden" accept="image/*">
                </div>

                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username:</label>
                    <input type="text" id="username" name="username" class="border p-2 rounded-lg w-full" placeholder="Enter your username">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" class="border p-2 rounded-lg w-full" placeholder="Enter your email">
                </div>

                <div class="mb-4">
                    <label for="bio" class="block text-gray-700">Bio:</label>
                    <textarea id="bio" name="bio" class="border p-2 rounded-lg w-full" placeholder="Tell us about yourself..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" name="cancel" id="cancelButton" class="bg-gray-500 text-white p-2 rounded-lg mr-2 hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" name="save" class="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('profilePicture').addEventListener('change', function (event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profilePicturePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        const editProfileButton = document.getElementById('editProfileButton');
        const editProfileModal = document.getElementById('editProfileModal');
        const closeModal = document.getElementById('closeModal');
        const cancelButton = document.getElementById('cancelButton');

        editProfileButton.addEventListener('click', () => {
            editProfileModal.classList.remove('hidden');
        });

        closeModal.addEventListener('click', () => {
            editProfileModal.classList.add('hidden');
        });
        cancelButton.addEventListener('click', () => {
            editProfileModal.classList.add('hidden');
        });

        function toggleComments(uploadId) {
            const commentSection = document.getElementById(`comments-${uploadId}`);
            commentSection.classList.toggle('hidden');
        }
    </script>
</body>

</html>

<?php
// Logout handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
