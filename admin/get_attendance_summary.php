<?php
include '../db_conn.php';


$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Initialize with 12 months
$summary = [
  'late' => array_fill(0, 12, 0),
  'ontime' => array_fill(0, 12, 0)
];

// Join attendance, employees, and schedule
$sql = "
    SELECT 
        MONTH(a.date) AS month,
        a.time_in,
        s.time_in AS schedule_in
    FROM attendance a
    JOIN employees e ON a.employee_id = e.employee_id
    JOIN schedule s ON a.employee_id = s.employee_id
    WHERE YEAR(a.date) = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $monthIndex = (int)$row['month'] - 1;
    $actualIn = strtotime($row['time_in']);
    $scheduledIn = strtotime($row['schedule_in']);

    if ($actualIn <= $scheduledIn) {
        $summary['ontime'][$monthIndex]++;
    } else {
        $summary['late'][$monthIndex]++;
    }
}

echo json_encode($summary);
?>
