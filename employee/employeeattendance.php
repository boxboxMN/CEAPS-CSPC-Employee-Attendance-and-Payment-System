<?php
// Include the database connection file
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming you have already connected to the database
    $employee_id = $_POST['employee'];  // Employee's unique ID
    $status = $_POST['status'];  // Time In or Time Out
    $current_time = date('Y-m-d H:i:s');  // Get current time

    // Check if it's a "Time In" or "Time Out"
    if ($status == 'in') {
        // Time In query
        $query = "INSERT INTO `attendance` (`employee_id`, `time_in`, `status`) VALUES (?, ?, ?)";
    } else {
        // Update Time Out for the employee
        $query = "UPDATE `attendance` SET `time_out` = ?, `status` = ? WHERE `employee_id` = ? AND `time_out` IS NULL";
    }

    // Prepare the statement
    if ($stmt = $mysqli->prepare($query)) {
        if ($status == 'in') {
            $stmt->bind_param('sss', $employee_id, $current_time, $status);
        } else {
            $stmt->bind_param('sss', $current_time, $status, $employee_id);
        }

        // Execute the query
        if ($stmt->execute()) {
            echo "Attendance updated successfully.";
        } else {
            echo "Error updating attendance: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Close the database connection
    $mysqli->close();
}
?>

<!-- Attendance Form -->
<form method="POST" action="attendance.php">
    <label for="employee">Employee ID:</label>
    <input type="text" name="employee" id="employee" required>

    <label for="status">Status:</label>
    <select name="status" id="status" required>
        <option value="in">Time In</option>
        <option value="out">Time Out</option>
    </select>

    <button type="submit">Submit</button>
</form>
