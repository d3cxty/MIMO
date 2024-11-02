<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mimo-Logout</title>
</head>
<body>
    
</body>
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    header('login.php');
    session_destroy();
   
}
?>
</html>