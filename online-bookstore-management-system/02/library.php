<?php
include_once("connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Library</title></head>
<body>
    <h2>Library</h2>
    <p>This is a placeholder for your library content. You can fill it with books, articles, or other resources.</p>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>