<?php
session_start();
include '../db_conn.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $role_id = $_POST['role_id'];
    $department = $_POST['department'];

    // Check if a new password was entered
    if (!empty($_POST['password'])) {
        // Hash the password using password_hash
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        // If no new password is provided, keep the old password unchanged
        $password = $_POST['old_password']; // Make sure you pass the old password to the form if no change
    }

    // Update query
    $updateQuery = "UPDATE employees SET first_name = ?, last_name = ?, address = ?, contact_number = ?, role_id = ?, department = ?, password = ? WHERE employee_id = ?";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $address, $contact_number, $role_id, $department, $password, $employee_id);

    if ($stmt->execute()) {
        header("Location: employee_list.php?success=Employee updated successfully");
    } else {
        header("Location: employee_list.php?error=An error occurred while updating the employee.");
    }

    $stmt->close();
    $conn->close();
}
?>
