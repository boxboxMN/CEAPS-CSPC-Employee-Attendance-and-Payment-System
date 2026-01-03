<?php
$host = "localhost";
$user = "root";
$pass = ""; // default XAMPP password is blank
$db = "wage_warriors"; // must match your imported DB name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
