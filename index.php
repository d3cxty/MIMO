<?php
include("index_query.php")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meme and Quote Sharing</title>
    <link href="/images/logo.ico" rel="icon" />
    <link rel="stylesheet" href="public/css/tailwind.css">
    
    <link rel="stylesheet" href="public/css/tailwind.css">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css/css/sakura.css" type="text/css"> -->

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
            background-color: rgba(0,0,0,0.5);
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
            width: 60%;
            height: auto;
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
<body class="bg-white">

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between p-4">
            <div class="text-2xl font-bold text-yellow-500">MiMo</div>
            <form method="GET" action="" class="flex w-1/2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." class="border rounded-lg p-2 w-full outline-yellow-500">
                <button type="submit" class="ml-2 bg-yellow-500 text-white p-2 rounded-lg">Search</button>
            </form>
            <div class="flex space-x-4">
                <span class="text-gray-600 cursor-pointer" onclick="showModal('profileModal')">
                    <i class="fas fa-user text-green-500"></i>
                </span>
            </div>
        </div>
    </header>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="text-lg font-semibold">Profile</h2>
                <span class="close" onclick="closeModal('profileModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>Name: <?php echo htmlspecialchars($username); ?></p>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
            </div>
            <div class="modal-footer">
                <button class="bg-yellow-500 text-white px-4 py-2 rounded-lg" onclick="closeModal('profileModal')">Close</button>
            </div>
        </div>
    </div>

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
                        <span><a href="category.php">Categories</a></span>
                    </li>
                    <li class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <i class="fas fa-message text-yellow-500"></i>
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
        <main class="main-content w-3/4 p-4">
            <?php if (empty($uploads)): ?>
                <p class="text-gray-500 text-center">No posts found matching your search criteria.</p>
            <?php else: ?>
                <?php foreach ($uploads as $upload): ?>
                    <div class="bg-white p-6 rounded-md mb-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gray-300 rounded-full mr-4">
                                <!-- Correctly display the profile picture of the user who made the upload -->
                                <img src="<?php echo !empty($upload['profile_picture']) ? htmlspecialchars($upload['profile_picture']) : 'https://via.placeholder.com/96'; ?>" 
                                    alt="Profile Picture" 
                                    class="w-full h-full object-cover rounded-full">
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($upload['username']); ?></h2>
                                <p class="text-gray-500"><?php echo htmlspecialchars($upload['created_at']); ?></p>
                            </div>
                        </div>

                        <?php if ($upload['upload_type'] === 'image'): ?>
                            <img src="<?php echo htmlspecialchars($upload['file_path']); ?>" alt="Upload" class="upload-image mb-4">
                            <p><?php echo htmlspecialchars($upload['caption']); ?></p>
                        <?php elseif ($upload['upload_type'] === 'quote'): ?>
                            <blockquote class="bg-gray-100 p-4 border-l-4 border-yellow-500 italic mb-4">
                                <p><?php echo htmlspecialchars($upload['caption']); ?></p>
                            </blockquote>
                        <?php endif; ?>

                        <div class="flex space-x-4 mt-4">
                            <!-- Like Button -->
                            <form action="like.php" method="POST" class="inline">
                                <input type="hidden" name="upload_id" value="<?php echo $upload['upload_id']; ?>">
                                <button type="submit" class="flex items-center space-x-1 text-gray-600 cursor-pointer">
                                    <i class="fas fa-heart <?php echo in_array($upload['upload_id'], $user_likes) ? 'text-pink-500' : ''; ?>"></i>
                                    <span><?php echo isset($likes[$upload['upload_id']]) ? $likes[$upload['upload_id']] : 0; ?></span>
                                </button>
                            </form>
                            <!-- Comment Button -->
                            <button class="flex items-center space-x-1 text-gray-600 cursor-pointer" class="comment-form"onclick="toggleComments(<?php echo $upload['upload_id']; ?>)">
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
        </main>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function toggleComments(uploadId) {
            const commentSection = document.getElementById(comments-${uploadId});
            commentSection.classList.toggle('hidden');
        }




        document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const uploadId = this.dataset.uploadId;

        fetch('like.php', {
            method: 'POST',
            body: JSON.stringify({ upload_id: uploadId }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.querySelector(`#like-count-${uploadId}`).textContent = data.newLikeCount;
        })
        .catch(error => console.error('Error:', error));
    });
});
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission
        const formData = new FormData(this);

        fetch('comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            const commentBoxId = `comments-${formData.get('upload_id')}`;
            const commentBox = document.getElementById(commentBoxId);
            commentBox.innerHTML += data; // Append the new comment
            this.reset(); // Reset the form fields
        })
        .catch(error => console.error('Error:', error));
    });
});
const searchInput = document.getElementById('search');
const mainContent = document.getElementById('main-content');

searchInput.addEventListener('input', function() {
    const searchValue = this.value;

    fetch(`search.php?query=${searchValue}`)
    .then(response => response.text())
    .then(data => {
        mainContent.innerHTML = data; // Update the main content area with search results
    })
    .catch(error => console.error('Error:', error));
});

        
    </script>
    

</body>
</html>