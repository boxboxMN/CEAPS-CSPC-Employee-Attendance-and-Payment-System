<?php
$host = "localhost";
$dbname = "wage_warriors";
$dbuser = "root";
$dbpass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the ID from the URL
$id = $_GET['id'] ?? null;

// If ID is set, delete the attendance record
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirect back to attendance page
header("Location: attendance.php?message=Record+deleted+successfully");
exit();
