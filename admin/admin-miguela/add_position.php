<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_conn.php';

    $title = trim($_POST['title']);
    $rate = floatval($_POST['rate']);

    $stmt = $conn->prepare("INSERT INTO roles (role, rate_per_hour) VALUES (?, ?)");
    $stmt->bind_param("sd", $title, $rate);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'title' => $title,
            'rate' => number_format($rate, 2)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
