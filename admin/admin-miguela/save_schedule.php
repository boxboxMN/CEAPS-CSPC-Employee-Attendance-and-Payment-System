<?php
session_start();
include '../db_conn.php';

$employee_id = $_POST['employee_id'] ?? '';
$time_in = date("H:i:s", strtotime($_POST['time_in'] ?? ''));
$time_out = date("H:i:s", strtotime($_POST['time_out'] ?? ''));

// Define role IDs
$roleIds = [
    "Software Engineer" => 1,
    "System Administrator" => 2,
    "Database Administrator" => 3,
    "Network Engineer" => 4,
    "Ot" => 5 // Assuming "Ot" is a role
];

if (!empty($employee_id) && !empty($time_in) && !empty($time_out)) {
    // Fetch employee role
    $roleQuery = "SELECT role FROM employees WHERE employee_id = ?";
    $roleStmt = $conn->prepare($roleQuery);
    $roleStmt->bind_param('s', $employee_id);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $role = $roleResult->fetch_assoc()['role'] ?? '';

    $roleStmt->close();

    if (!empty($role)) {
        $role_id = $roleIds[$role] ?? null;

        if ($role_id === null) {
            $_SESSION['message'] = "❗ Role ID not defined for the role: $role.";
        } else {
            // Check if schedule already exists
            $checkQuery = "SELECT * FROM schedule WHERE employee_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('s', $employee_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $_SESSION['message'] = "❗ Schedule already exists for this employee.";
            } else {
                // Insert new schedule
                $insertQuery = "INSERT INTO schedule (employee_id, role, role_id, time_in, time_out) 
                                VALUES (?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                if ($insertStmt) {
                    $insertStmt->bind_param('ssiss', $employee_id, $role, $role_id, $time_in, $time_out);
                    if ($insertStmt->execute()) {
                        $_SESSION['message'] = "✅ Schedule saved successfully!";
                    } else {
                        $_SESSION['message'] = "❌ Failed to save schedule. Error: " . $insertStmt->error;
                    }
                    $insertStmt->close();
                } else {
                    $_SESSION['message'] = "❌ Failed to prepare insert statement. Error: " . $conn->error;
                }
            }

            $checkStmt->close();
        }
    } else {
        $_SESSION['message'] = "❗ Could not find employee role.";
    }

} else {
    $_SESSION['message'] = "❗ All fields are required.";
}

$conn->close();
header("Location: schedule.php");
exit();
?>
