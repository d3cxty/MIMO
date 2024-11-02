<?php
include("conn.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uname = $conn->real_escape_string($username);

// Fetch uploads from the database
$sql = "SELECT u.user_id, u.upload_type, u.file_path, u.caption, u.created_at, 
        users.username, users.email
        FROM uploads u
        JOIN users ON u.user_id = users.user_id
        WHERE users.username = '$uname'
        ORDER BY u.created_at DESC";

$result = $conn->query($sql);
if ($result === false) {
    die("Error: " . $conn->error);
}

$uploads = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uploads[] = $row;
    }
}
$email = isset($uploads[0]['email']) ? $uploads[0]['email'] : 'N/A';

// Fetch user information
$stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
    }
} else {
    die("Error executing query: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #ffffff; /* Set background color to white */
        }
        /* Ensure sidebar is fixed */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh; /* Full viewport height */
            overflow-y: auto; /* Add scroll if needed */
        }
        /* Main content should have margin to avoid overlap with fixed sidebar */
        .main-content {
            margin-left: 25%; /* Adjust based on sidebar width */
        }
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5); /* Black overlay */
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
        .modal-header, .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header {
            border-bottom: 1px solid #ddd;
        }
        .modal-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
        /* Image styles */
        .upload-image {
            width: 60%; /* Full width of the container */
            height: 20%; /* Fixed height */
            object-fit: contain; /* Cover the container, maintaining aspect ratio */
            border-radius: 8px; /* Rounded corners */
        }
        /* Adjust image styles if needed */
.upload-image {
    width: 60%; /* Full width of the container */
    height: auto; /* Adjust height to maintain aspect ratio */
    object-fit: contain; /* Cover the container, maintaining aspect ratio */
    border-radius: 8px; /* Rounded corners */
}

/* Style for quotes */
blockquote {
    background-color: #f9f9f9;
    border-left: 4px solid #fbbf24;
    padding: 16px;
    border-radius: 8px;
    font-style: italic;
}

    </style>
    <style>
        
        body {
            background-color: #f9fafb;
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

        .profile-picture {
            width: 96px;
            height: 96px;
            object-fit: cover;
            border-radius: 50%;
        }

        .upload-image {
            width: 100%; /* Adjust width to be slightly larger */
            max-height: 400px;
            object-fit: fill;
            border-radius: 8px;
            display: block; /* Ensure image is a block element */
            
        }

        .caption {
            text-align: center;
            margin-top: 10px;
            font-size: 1rem;
            color: #555;
        }

        /* Modal styles */
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

        .modal-header,
        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header {
            border-bottom: 1px solid #ddd;
        }

        .modal-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
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

        /* Style for quotes */
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
            <input type="text" placeholder="Search..." class="border rounded-lg p-2 w-1/2 outline-yellow-500">
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
                        <i class="fas fa-upload text-green-500"></i>
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

        <!-- Profile Content -->
        <main class="main-content w-3/4 p-4">
            <!-- Profile Header -->
            <div class="bg-white p-6 rounded-md mb-6 shadow-md">
                <div class="flex items-center mb-4">
                <div class="bg-gray-300 rounded-full overflow-hidden mr-4 h-20 w-20">
    <!-- Display profile picture if set, otherwise a placeholder -->
    <img src="<?php echo !empty($user_info['profile_picture']) ? htmlspecialchars($user_info['profile_picture']) : 'https://via.placeholder.com/96'; ?>"
        alt="Profile Picture" class="w-full h-full object-cover">
</div>

                    <div>
                        <h1 class="text-3xl font-semibold"><?php echo htmlspecialchars($user_info['username']); ?></h1>
                        <p class="text-gray-600">Email: <?php echo htmlspecialchars($user_info['email']); ?></p>
                        <p class="text-gray-600">Joined: January 2024</p>
                        <div class="mt-4 flex space-x-2">
                            <button id="editProfileButton"
                                class="bg-yellow-500 text-white p-2 rounded-lg hover:bg-yellow-600">
                                <i class="fas fa-edit mr-2"></i> Edit Profile
                            </button>
                            <form action="profile.php" method="POST" class="inline">
                                <button type="submit" name="logout"
                                    class="bg-red-500 text-white p-2 rounded-lg hover:bg-red-600">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold">About Me</h2>
                    <p class="text-gray-700 mt-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </div>
            </div>

            <!-- User Posts -->
            <div class="bg-white p-6 rounded-md mb-6 shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Recent Posts</h2>

                <!-- Display user posts -->
                <?php foreach ($uploads as $upload): ?>
    <div class="bg-white p-6 rounded-md mb-6 shadow-md max-w-lg mx-auto">
    <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-gray-300 rounded-full overflow-hidden">
        <img src="<?php echo !empty($user_info['profile_picture']) ? htmlspecialchars($user_info['profile_picture']) : 'https://via.placeholder.com/96'; ?>" alt="Profile Picture" class="w-full h-full object-cover rounded-full">
    </div>
    <div class="ml-4">
        <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($upload['username']); ?></h2>
        <p class="text-gray-500"><?php echo htmlspecialchars($upload['created_at']); ?></p>
    </div>
</div>


        <?php if ($upload['upload_type'] === 'image'): ?>
            <div class=" bg-gray-200 mb-2 overflow-hidden">
                <img src="<?php echo htmlspecialchars($upload['file_path']); ?>" alt="Upload" class="w-full h-120 max-w-full object-cover">
            </div>
            <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($upload['caption']); ?></p>
        <?php elseif ($upload['upload_type'] === 'quote'): ?>
            <blockquote class="bg-gray-100 p-4 border-l-4 border-yellow-500 italic text-base mb-4">
                <p><?php echo htmlspecialchars($upload['caption']); ?></p>
            </blockquote>
        <?php endif; ?>

        <div class="flex space-x-4 mt-4">
            <button class="flex items-center space-x-1 text-gray-600 hover:text-gray-800 cursor-pointer">
                <i class="fas fa-heart text-pink-500"></i>
                <span>Like</span>
            </button>
            <button class="flex items-center space-x-1 text-gray-600 hover:text-gray-800 cursor-pointer">
                <i class="fas fa-comment text-blue-500"></i>
                <span>Comment</span>
            </button>
        </div>
    </div>
<?php endforeach; ?>


            </div>
        </main>
    </div>

    <!-- Modal (Initially Hidden) -->
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
                <!-- Profile Picture Upload -->
                <div class="mb-4 text-center">
                    <label for="profilePicture" class="block text-gray-700 mb-2">Profile Picture:</label>
                    <div class="relative w-24 h-24 mx-auto mb-4">
                        <img id="profilePicturePreview" src="https://via.placeholder.com/96"
                            alt="Profile Picture Preview" class="w-full h-full object-cover rounded-full border">
                        <label for="profilePicture"
                            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white cursor-pointer rounded-full">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    <input type="file" id="profilePicture" name="profilePicture" class="hidden" accept="image/*">
                </div>

                <!-- Username and Email -->
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username:</label>
                    <input type="text" id="username" name="username" class="border p-2 rounded-lg w-full"
                        placeholder="Enter your username">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" class="border p-2 rounded-lg w-full"
                        placeholder="Enter your email">
                </div>

                <!-- Form Submission Buttons -->
                <div class="flex justify-end">
                    <button type="button" name="cancel" id="cancelButton"
                        class="bg-gray-500 text-white p-2 rounded-lg mr-2 hover:bg-gray-600">
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
        // JavaScript for previewing uploaded profile picture
        document.getElementById('profilePicture').addEventListener('change', function (event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profilePicturePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        // Get elements for modal interaction
        const editProfileButton = document.getElementById('editProfileButton');
        const editProfileModal = document.getElementById('editProfileModal');
        const closeModal = document.getElementById('closeModal');
        const cancelButton = document.getElementById('cancelButton');

        // Show modal on edit button click
        editProfileButton.addEventListener('click', () => {
            editProfileModal.classList.remove('hidden');
        });

        // Hide modal on close or cancel button click
        closeModal.addEventListener('click', () => {
            editProfileModal.classList.add('hidden');
        });
        cancelButton.addEventListener('click', () => {
            editProfileModal.classList.add('hidden');
        });
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
