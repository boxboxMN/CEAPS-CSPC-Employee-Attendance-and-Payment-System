<?php
include '../db_conn.php'; // Adjust path if needed

if (isset($_GET['role_id'])) {
    $role_id = intval($_GET['role_id']);

    // Check if the role is associated with any employees
    $checkQuery = $conn->prepare("SELECT COUNT(*) as total FROM employees WHERE role_id = ?");
    $checkQuery->bind_param("i", $role_id);
    $checkQuery->execute();
    $checkResult = $checkQuery->get_result()->fetch_assoc();
    $checkQuery->close();

    if ($checkResult['total'] > 0) {
        // Redirect with error if role is in use
        header("Location: role.php?error=Role+is+currently+assigned+to+employees");
        exit();
    }

    // Proceed with deletion
    $deleteQuery = $conn->prepare("DELETE FROM roles WHERE role_id = ?");
    $deleteQuery->bind_param("i", $role_id);

    if ($deleteQuery->execute()) {
        header("Location: role.php?message=Role+successfully+deleted");
        exit();
    } else {
        header("Location: role.php?error=Failed+to+delete+role");
    }

    $deleteQuery->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
