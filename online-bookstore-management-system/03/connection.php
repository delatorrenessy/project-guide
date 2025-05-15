<?php
$servername = "localhost";
$username = "root";
$password = ""; // your MySQL password if applicable
$database = "db_lastname_project";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
include_once("functions.php");
?>