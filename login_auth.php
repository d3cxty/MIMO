<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate username
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        die("Invalid username. Only letters, numbers, and underscores are allowed, and it must be between 3 and 20 characters.");
    }

    // Fetch and validate password
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Prepare and execute statement to get the password hash and user ID
    $stmt = $conn->prepare("SELECT password, user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if the username exists
    if ($stmt->num_rows > 0) {
        // Bind results to variables
        $stmt->bind_result($hash, $user_id);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hash)) {
        
            // Set session variables upon successful login
            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $user_id;
            $_SESSION["loggedin"] = true;

            
            // Redirect to index page to avoid form resubmission
            header("Location: index.php");
            exit();
        } else {
            
            echo "Incorrect password. Please try again.";
        }
    } else {
        echo "Username does not exist.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>