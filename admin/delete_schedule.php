<?php
session_start();
include '../db_conn.php';

function deleteSchedule($conn, $id) {
    if (!empty($id)) {
        $query = "DELETE FROM schedule WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = " Schedule deleted successfully!";
            } else {
                $_SESSION['message'] = " Failed to delete schedule. Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = " Failed to prepare statement. Error: " . $conn->error;
        }
    } else {
        $_SESSION['message'] = "❗ Invalid schedule ID.";
    }
}

if (isset($_GET['id'])) {
    deleteSchedule($conn, $_GET['id']);
    $conn->close();
    header("Location: schedule.php");
    exit();
}
?>