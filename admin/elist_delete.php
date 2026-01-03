<?php
session_start();
include '../db_conn.php';

if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    // Delete from employees table
    $delete_employee_query = "DELETE FROM employees WHERE employee_id = ?";
    $stmt_employee = $conn->prepare($delete_employee_query);
    $stmt_employee->bind_param("s", $employee_id); // employee_id is a string like "EMP001"

    if ($stmt_employee->execute()) {
        header("Location: employee_list.php?success=Employee deleted successfully.");
    } else {
        header("Location: employee_list.php?error=Failed to delete employee.");
    }

    $stmt_employee->close();
}
?>
