<?php
echo '<pre>'; print_r($_POST); echo '</pre>';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect POST data
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Convert AM/PM time to 24-hour format with seconds (HH:MM:SS)
    $time_in = date("H:i:s", strtotime($_POST['time_in']));
    $time_out = date("H:i:s", strtotime($_POST['time_out']));

    // DB config
    $host = "localhost";
    $dbname = "wage_warriors";
    $dbuser = "root";
    $dbpass = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare and execute update
        $stmt = $pdo->prepare("
            UPDATE attendance 
            SET time_in = :time_in,
                time_out = :time_out,
                status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            ':time_in' => $time_in,
            ':time_out' => $time_out,
            ':status' => $status,
            ':id' => $id
        ]);

        // Redirect back to attendance page with success message
        header("Location: attendance.php?edit=success");
exit();

    } catch (PDOException $e) {
        die("Error updating record: " . $e->getMessage());
    }
} else {
    // Redirect back if not POST
    header("Location: attendance.php");
    exit();
}