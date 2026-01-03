<?php
session_start();
include 'db_conn.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$employee_id = $data['employee_id'];
$type = $data['type'];
$current_time = date('H:i:s');
$today = date('Y-m-d');

try {
    // Check existing attendance
    $check_stmt = $conn->prepare("SELECT time_in, time_out FROM attendance WHERE employee_id = ? AND date = ?");
    $check_stmt->bind_param("ss", $employee_id, $today);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $exists = $result->num_rows > 0;
    $attendance = $exists ? $result->fetch_assoc() : null;
    $check_stmt->close();

    if ($type == 'time_in') {
        if ($exists && !empty($attendance['time_in'])) {
            echo json_encode(['success' => false, 'message' => 'You have already timed in today']);
            exit;
        }
        
        // Get employee's schedule
        $schedule_stmt = $conn->prepare("SELECT time_in FROM schedule WHERE employee_id = ?");
        $schedule_stmt->bind_param("s", $employee_id);
        $schedule_stmt->execute();
        $schedule = $schedule_stmt->get_result()->fetch_assoc();
        $schedule_stmt->close();
        
        // Calculate status (1 = on time, 0 = late)
        $status = (strtotime($current_time) <= strtotime($schedule['time_in'])) ? 1 : 0;
        
        if ($exists) {
            // Update existing record (in case time_in was null)
            $stmt = $conn->prepare("UPDATE attendance SET time_in = ?, status = ? WHERE employee_id = ? AND date = ?");
            $stmt->bind_param("siss", $current_time, $status, $employee_id, $today);
        } else {
            // Create new record
            $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, time_in, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $employee_id, $today, $current_time, $status);
        }
    } else { // time_out
        if (!$exists || empty($attendance['time_in'])) {
            echo json_encode(['success' => false, 'message' => 'You need to time in first']);
            exit;
        }
        
        if (!empty($attendance['time_out'])) {
            echo json_encode(['success' => false, 'message' => 'You have already timed out today']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE employee_id = ? AND date = ?");
        $stmt->bind_param("sss", $current_time, $employee_id, $today);
    }

    $success = $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Attendance recorded successfully' : 'Error recording attendance'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>