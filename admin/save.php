<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $role = $_POST['role'];
    $department = $_POST['department'];
    $password = $_POST['password'];

    // Optional: You can hash the password here if needed
    // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE employees SET first_name = ?, last_name = ?, address = ?, contact_number = ?, role = ?, department = ?, password = ? WHERE employee_id = ?");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $address, $contact_number, $role, $department, $password, $employee_id);

    if ($stmt->execute()) {
        header("Location: employee_list.php?success=Employee updated successfully");
    } else {
        echo "Error updating employee: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>