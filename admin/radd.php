<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_conn.php'; // Include DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = trim($_POST['role']);
    $rate_per_hour = trim($_POST['rate_per_hour']);

    if (!empty($role) && is_numeric($rate_per_hour)) {
        // Prepare and bind to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO roles (role, rate_per_hour) VALUES (?, ?)");
        $stmt->bind_param("sd", $role, $rate_per_hour);

        if ($stmt->execute()) {
            // Redirect back with success message (optional)
            // After successful insert
        header("Location: role.php?message=Role added successfully.");
        exit();

        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid input.";
    }

    $conn->close();
} else {
    header("Location: role.php");
    exit();
}
