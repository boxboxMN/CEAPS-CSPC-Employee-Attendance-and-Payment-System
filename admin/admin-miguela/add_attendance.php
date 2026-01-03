<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $host = "localhost";
    $dbname = "wage_warriors";
    $dbuser = "root";
    $dbpass = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $employee_id = $_POST['employee_id'];
        $date = $_POST['date'];
        $time_in = $_POST['time_in'];
        $time_out = $_POST['time_out'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, time_in, time_out, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time_in, $time_out, $status]);

        header("Location: attendance.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<?php


