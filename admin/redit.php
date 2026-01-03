<?php 

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_conn.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $role_id = $_POST['role_id'];
    $role = $_POST['role'];
    $rate_per_hour = $_POST['rate_per_hour'];

    // Prepare an SQL statement to update the role in the database
    $stmt = $conn->prepare("UPDATE roles SET role = ?, rate_per_hour = ? WHERE role_id = ?");
    $stmt->bind_param("sdi", $role, $rate_per_hour, $role_id);

    if ($stmt->execute()) {
        // If the update was successful, redirect to the role management page with a success message
        // After successful update
        header("Location: role.php?message=Role updated successfully.");
        exit();

    } else {
        echo "Error updating role: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>