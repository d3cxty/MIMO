<?php
include("conn.php"); // Include your database connection file
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch user information from the database
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

// Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search query
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch uploads from the database with search functionality
$sql = "SELECT u.upload_id, u.user_id, u.upload_type, u.file_path, u.caption, u.created_at, 
               users.username, users.email, users.profile_picture
        FROM uploads u
        JOIN users ON u.user_id = users.user_id
        WHERE u.caption LIKE ? OR users.username LIKE ?
        ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
$search_param = '%' . $search . '%';
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

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