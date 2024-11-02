<?php
include("conn.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Use user_id to uniquely identify the user
    $username = $_SESSION['username'];

    // Handle profile picture update
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $filename = $_FILES['profilePicture']['name'];
        $allowed = array('jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'jfif' => 'image/jpeg');
        $filetype = $_FILES['profilePicture']['type'];
        $filesize = $_FILES['profilePicture']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $allowed) || $filetype !== $allowed[$ext]) {
            $_SESSION['message'] = 'File type not allowed.';
        } else {
            $limit = 7 * 1024 * 1024; // Set the file size limit to 7 MB
            if ($filesize > $limit) {
                $_SESSION['message'] = 'The file should be under 7 MB.';
            } else {
                $upload_dir = 'pfp/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Generate a unique filename to prevent overwriting
                $new_filename = uniqid('pfp_', true) . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $upload_path)) {
                    // Update profile picture in the database using user_id to ensure it applies only to the current user
                    $stmt = $conn->prepare('UPDATE users SET profile_picture = ? WHERE user_id = ?');
                    $stmt->bind_param('si', $upload_path, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = 'Profile picture updated successfully.';
                    } else {
                        $_SESSION['message'] = 'Error updating database: ' . $conn->error;
                    }
                } else {
                    $_SESSION['message'] = 'Failed to move uploaded file.';
                }
            }
        }
    }

    // Handle bio update
    if (isset($_POST['bio'])) {
        $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
        $stmt = $conn->prepare('UPDATE users SET bio = ? WHERE user_id = ?');
        $stmt->bind_param('si', $bio, $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Bio updated successfully.';
        } else {
            $_SESSION['message'] = 'Error updating bio: ' . $conn->error;
        }
    }

    // Handle email update
    if (isset($_POST['email'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = 'Invalid email address.';
        } else {
            $stmt = $conn->prepare('UPDATE users SET email = ? WHERE user_id = ?');
            $stmt->bind_param('si', $email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Email updated successfully.';
            } else {
                $_SESSION['message'] = 'Error updating email: ' . $conn->error;
            }
        }
    }

    // Handle username update
    if (isset($_POST['username'])) {
        $newUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $newUsername)) {
            $_SESSION['message'] = 'Invalid username. Only letters, numbers, and underscores are allowed, and it must be between 3 and 20 characters.';
        } else {
            // Check if the new username already exists
            $stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $stmt->bind_param('s', $newUsername);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $_SESSION['message'] = 'Username already exists. Please choose another one.';
            } else {
                $stmt = $conn->prepare('UPDATE users SET username = ? WHERE user_id = ?');
                $stmt->bind_param('si', $newUsername, $user_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Username updated successfully.';
                    $_SESSION['username'] = $newUsername; // Update session username
                } else {
                    $_SESSION['message'] = 'Error updating username: ' . $conn->error;
                }
            }
        }
    }

    $conn->close();
    header('Location: profile.php'); // Redirect to avoid form resubmission
    exit();
}
?>
<?php
include("conn.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Use user_id to uniquely identify the user
    $username = $_SESSION['username'];

    // Handle profile picture update
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $filename = $_FILES['profilePicture']['name'];
        $allowed = array('jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'jfif' => 'image/jpeg');
        $filetype = $_FILES['profilePicture']['type'];
        $filesize = $_FILES['profilePicture']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $allowed) || $filetype !== $allowed[$ext]) {
            $_SESSION['message'] = 'File type not allowed.';
        } else {
            $limit = 7 * 1024 * 1024; // Set the file size limit to 7 MB
            if ($filesize > $limit) {
                $_SESSION['message'] = 'The file should be under 7 MB.';
            } else {
                $upload_dir = 'pfp/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Generate a unique filename to prevent overwriting
                $new_filename = uniqid('pfp_', true) . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $upload_path)) {
                    // Update profile picture in the database using user_id to ensure it applies only to the current user
                    $stmt = $conn->prepare('UPDATE users SET profile_picture = ? WHERE user_id = ?');
                    $stmt->bind_param('si', $upload_path, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = 'Profile picture updated successfully.';
                    } else {
                        $_SESSION['message'] = 'Error updating database: ' . $conn->error;
                    }
                } else {
                    $_SESSION['message'] = 'Failed to move uploaded file.';
                }
            }
        }
    }

    // Handle bio update
    if (isset($_POST['bio'])) {
        $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
        $stmt = $conn->prepare('UPDATE users SET bio = ? WHERE user_id = ?');
        $stmt->bind_param('si', $bio, $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Bio updated successfully.';
        } else {
            $_SESSION['message'] = 'Error updating bio: ' . $conn->error;
        }
    }

    // Handle email update
    if (isset($_POST['email'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = 'Invalid email address.';
        } else {
            $stmt = $conn->prepare('UPDATE users SET email = ? WHERE user_id = ?');
            $stmt->bind_param('si', $email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Email updated successfully.';
            } else {
                $_SESSION['message'] = 'Error updating email: ' . $conn->error;
            }
        }
    }

    // Handle username update
    if (isset($_POST['username'])) {
        $newUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $newUsername)) {
            $_SESSION['message'] = 'Invalid username. Only letters, numbers, and underscores are allowed, and it must be between 3 and 20 characters.';
        } else {
            // Check if the new username already exists
            $stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $stmt->bind_param('s', $newUsername);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $_SESSION['message'] = 'Username already exists. Please choose another one.';
            } else {
                $stmt = $conn->prepare('UPDATE users SET username = ? WHERE user_id = ?');
                $stmt->bind_param('si', $newUsername, $user_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Username updated successfully.';
                    $_SESSION['username'] = $newUsername; // Update session username
                } else {
                    $_SESSION['message'] = 'Error updating username: ' . $conn->error;
                }
            }
        }
    }

    $conn->close();
    header('Location: profile.php'); // Redirect to avoid form resubmission
    exit();
}
?>
