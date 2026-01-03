<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_id = trim($_POST['employee_id']); 
    if (empty($employee_id)) {
        die("No employee_id passed.");
    }

    $host = "localhost";
    $dbname = "wage_warriors";
    $dbuser = "root";
    $dbpass = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $employee_id = $_POST['employee_id'];
        $date = $_POST['date'];
        
        // Convert time inputs to 24-hour format for database storage
        $time_in = !empty($_POST['time_in']) ? date("H:i:s", strtotime($_POST['time_in'])) : null;
        $time_out = !empty($_POST['time_out']) ? date("H:i:s", strtotime($_POST['time_out'])) : null;
        
        $status = $_POST['status'];

        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, time_in, time_out, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time_in, $time_out, $status]);

        header("Location: attendance.php?success=Attendance record added successfully!");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: attendance.php");
    exit();
}
?>