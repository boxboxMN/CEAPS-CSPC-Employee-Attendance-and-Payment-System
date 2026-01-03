<?php
include '../db_conn.php';

if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    // Prepare and execute the delete query
    $delete_query = "DELETE FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        // Redirect to employee list with a success message
        header("Location: employee_list.php?success=Employee deleted successfully.");
    } else {
        // Redirect to employee list with an error message
        header("Location: employee_list.php?error=Failed to delete employee.");
    }

    $stmt->close();
}
?>