<?php
include("conn.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != "true") {
    header("Location: login.php"); // Corrected header redirect syntax
    exit();
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meme and Quote Sharing</title>
    <link rel="stylesheet" href="public/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="bg-white">

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
                    <i class="fas fa-message text-purple-1000"></i>
                    <span><a href="chat.php">Chat</a></span>
                </li>
                    <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <i class="fas fa-upload text-orange-500"></i>
                        <span><a href="upload.php">Upload</a></span>
                    </li>
                    <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <i class="fas fa-user text-purple-500"></i>
                        <a href="profile.php">Profile</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Feed -->
        <main class="main-content w-1/2 p-4">
            <!-- Upload Form -->
            <div class="bg-white p-6 rounded-md mb-6">
                <h2 class="text-2xl font-semibold mb-4 text-center">Upload Your Meme or Quote</h2>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                    <div class="mb-4 border-yellow-500">
                        <label for="upload-type" class="block text-lg font-medium mb-2 text-center">Upload Type:</label>
                        <select id="upload-type" name="upload-type" class="border rounded-lg p-2 w-full border-yellow-500">
                            <option value="image">Image</option>
                            <option value="quote">Quote</option>
                        </select>
                    </div>
                    <div id="image-upload" class="mb-4">
                        <label for="file" class="block text-lg font-medium mb-2 text-center">Select Image:</label>
                        <input type="file" id="file" name="file" accept="image/*" class="border rounded-lg p-2 w-full border-yellow-500">
                    </div>
                    <div class="mb-4">
                        <label for="caption" class="block text-lg font-medium mb-2 border-yellow-500">Caption:</label>
                        <textarea id="caption" name="caption" rows="4" class="border rounded-lg p-2 w-full border-yellow-500"></textarea>
                    </div>
                    <button  type="submit" class="bg-yellow-500 text-white p-2 rounded-lg hover:bg-blue-600">
                        <i class="fas fa-upload mr-2"></i> Upload
                    </button>
                   
                </form>
            </div>
        </main>
    </div>

    <script>
        const uploadTypeSelect = document.getElementById('upload-type');
        const imageUploadDiv = document.getElementById('image-upload');

        uploadTypeSelect.addEventListener('change', () => {
            if (uploadTypeSelect.value === 'quote') {
                imageUploadDiv.style.display = 'none';
            } else {
                imageUploadDiv.style.display = 'block';
            }
        });

        // Initialize visibility based on the default value
        document.addEventListener('DOMContentLoaded', () => {
            uploadTypeSelect.dispatchEvent(new Event('change'));
        });
    </script>

</body>
<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $upload_type = $_POST['upload-type'];
    $caption = htmlspecialchars($_POST['caption']);

    // Check if upload type is image
    if ($upload_type == 'image') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file_name = basename($_FILES['file']['name']);
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_path = 'uploads/' . uniqid('img_', true) . '_' . $file_name;

            // Ensure upload directory exists and has write permissions
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true); // Creates the directory if it doesn't exist
            }

            // Attempt to move the uploaded file
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Insert data into the database
                try {
                    $stmt = $conn->prepare("INSERT INTO uploads (user_id, upload_type, file_path, caption) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $user_id, $upload_type, $file_path, $caption);
                    $stmt->execute();
                    echo "Upload successful.";
                } catch (PDOException $e) {
                    // Catch SQL errors and print
                    echo "Database error: " . $e->getMessage();
                }
            } else {
                echo "File upload failed. Unable to move file to upload directory.";
            }
        } else {
            // Check for specific file upload errors
            echo "No file uploaded or error during upload.";
            echo "Error Code: " . $_FILES['file']['error']; // Display error code for debugging
        }
    } elseif ($upload_type == 'quote') {
        // Insert quote into the database
        try {
            $stmt = $conn->prepare("INSERT INTO uploads (user_id, upload_type, caption) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $upload_type, $caption);
            $stmt->execute();
            echo "Quote uploaded successfully.";
            header("Location: home.php");
            exit();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    } else {
        echo "Invalid upload type.";
    }
}
?>

</html>
