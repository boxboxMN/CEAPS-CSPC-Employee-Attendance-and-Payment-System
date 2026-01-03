<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_conn.php';

    $id = intval($_POST['id']);
    $rate = floatval($_POST['rate']);

    $stmt = $conn->prepare("UPDATE roles SET rate_per_hour = ? WHERE id = ?");
    $stmt->bind_param("di", $rate, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
