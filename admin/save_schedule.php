<?php
session_start();
include '../db_conn.php';

$employee_id = $_POST['employee_id'] ?? '';
$time_in = date("H:i:s", strtotime($_POST['time_in']));
$time_out = date("H:i:s", strtotime($_POST['time_out']));

// Role mapping (you could fetch from DB in future)
$roleIds = [
    "Software Engineer" => 1,
    "System Administrator" => 2,
    "Database Administrator" => 3,
    "Network Engineer" => 4,
    "Other" => 5
];

if (!empty($employee_id) && !empty($time_in) && !empty($time_out)) {
    // Get role_id from employee
    $roleQuery = "
        SELECT e.role_id
        FROM employees e
        WHERE e.employee_id = ?
    ";
    $roleStmt = $conn->prepare($roleQuery);
    $roleStmt->bind_param('s', $employee_id);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $role_id = $roleResult->fetch_assoc()['role_id'] ?? null;
    $roleStmt->close();

    if (!empty($role_id)) {
        // Check if the employee already has a schedule
        $checkQuery = "SELECT * FROM schedule WHERE employee_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('s', $employee_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $exists = $checkResult->num_rows > 0;
        $checkStmt->close();

        if ($exists) {
            // Update existing schedule
            $updateQuery = "UPDATE schedule SET time_in = ?, time_out = ?, role_id = ? WHERE employee_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ssis', $time_in, $time_out, $role_id, $employee_id);

            if ($updateStmt->execute()) {
                $_SESSION['message'] = "Schedule updated successfully!";
            } else {
                $_SESSION['message'] = " Failed to update schedule: " . $updateStmt->error;
            }
            $updateStmt->close();
        } else {
            // Insert new schedule
            $insertQuery = "INSERT INTO schedule (employee_id, role_id, time_in, time_out) 
                            VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param('siss', $employee_id, $role_id, $time_in, $time_out);

            if ($insertStmt->execute()) {
                $_SESSION['message'] = " Schedule saved successfully!";
            } else {
                $_SESSION['message'] = " Failed to save schedule: " . $insertStmt->error;
            }
            $insertStmt->close();
        }
    } else {
        $_SESSION['message'] = "❗ Could not find employee's role.";
    }
} else {
    $_SESSION['message'] = "❗ All fields are required.";
}

$conn->close();
header("Location: schedule.php");
exit();