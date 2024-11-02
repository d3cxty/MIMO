<?php
include("conn.php");
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch distinct categories and their post counts from the uploads table
$sql = "SELECT upload_type, COUNT(*) as post_count 
        FROM uploads 
        GROUP BY upload_type";
$result = $conn->query($sql);

$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - MiMo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="public/css/tailwind.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css/css/sakura.css" type="text/css">
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

        .category-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .category-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .category-details {
            padding: 20px;
        }

        .category-name {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .category-count {
            color: #6b7280;
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between p-4">
            <div class="text-2xl font-bold text-yellow-500">MiMo</div>
            <form method="GET" class="flex w-1/2">
                <input type="text" name="search" placeholder="Search categories..." class="border rounded-lg p-2 w-full outline-yellow-500">
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
                        <i class="fas fa-message text-purple-100"></i>
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

        <!-- Main Feed -->
        <main class="main-content w-3/4 p-4 fixed">
            <h1 class="text-3xl font-bold text-center mb-8">Explore Categories</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <?php 
                        // Set images for categories dynamically based on upload type
                        $category_image = 'images/default-category.jpg'; // Default image
                        if ($category['upload_type'] === 'image') {
                            $category_image = 'images/image-category.jpg';
                        } elseif ($category['upload_type'] === 'quote') {
                            $category_image = 'images/quote-category.jpg';
                        }
                        // You can add more conditions for other types if needed
                        ?>
                        <a href="category.php?type=<?php echo urlencode($category['upload_type']); ?>" class="category-card">
                            <img src="<?php echo htmlspecialchars($category_image); ?>" alt="<?php echo htmlspecialchars($category['upload_type']); ?> Image" class="category-image">
                            <div class="category-details">
                                <h2 class="category-name"><?php echo ucfirst(htmlspecialchars($category['upload_type'])); ?></h2>
                                <p class="category-count"><?php echo $category['post_count']; ?> posts</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center col-span-full">No categories found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
