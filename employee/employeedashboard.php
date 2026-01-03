<?php
session_start();
include 'db_conn.php';

// Set timezone to ensure consistent time calculations
date_default_timezone_set('Asia/Manila');

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: employeelogin.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$employee_exists = false;
$attendance_records = [];
$has_timed_in_today = false;
$has_timed_out_today = false;
$today = date('Y-m-d');

// Fetch employee details
$stmt = $conn->prepare("SELECT first_name, last_name FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($first_name, $last_name);
    $stmt->fetch();
    $employee_exists = true;
} else {
    $first_name = $last_name = '';
    echo "Employee not found in database!";
}
$stmt->close();

if ($employee_exists) {
    // Check today's attendance status
    $check_stmt = $conn->prepare("
        SELECT 
            a.time_in, 
            a.time_out,
            s.time_in AS schedule_in
        FROM attendance a
        JOIN schedule s ON a.employee_id = s.employee_id
        WHERE a.employee_id = ? AND a.date = ?
    ");
    $check_stmt->bind_param("ss", $employee_id, $today);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $has_timed_in_today = !empty($row['time_in']);
        $has_timed_out_today = !empty($row['time_out']);
        $today_status = (strtotime($row['time_in']) <= strtotime($row['schedule_in'])) ? 'On Time' : 'Late';
    }
    $check_stmt->close();

    // Fetch total work hours
    $stmt = $conn->prepare("
        SELECT 
            ROUND(SUM(TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600), 2) AS total_hours
        FROM attendance
        WHERE employee_id = ?
    ");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $stmt->bind_result($total_hours);
    $stmt->fetch();
    $total_work_hours = $total_hours ?? 0;
    $stmt->close();
}

// Total Attendance
$total_attendance = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ?");
$total_attendance->bind_param("s", $employee_id);
$total_attendance->execute();
$total_attendance->bind_result($attendance_count);
$total_attendance->fetch();
$total_attendance->close();

// Count total presents (days with a time_in recorded)
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM attendance 
    WHERE employee_id = ? AND time_in IS NOT NULL
");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->bind_result($total_presents);
$stmt->fetch();
$stmt->close();

// Count total lates (days where time_in > schedule_in)
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM attendance a
    JOIN schedule s ON a.employee_id = s.employee_id
    WHERE a.employee_id = ? AND TIME(a.time_in) > TIME(s.time_in)
");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->bind_result($total_lates);
$stmt->fetch();
$stmt->close();

// Fetch attendance records
if ($employee_exists) {
    $stmt = $conn->prepare("
        SELECT 
            a.date,
            a.time_in,
            a.time_out,
            s.time_in AS schedule_in
        FROM attendance a
        LEFT JOIN schedule s ON a.employee_id = s.employee_id
        WHERE a.employee_id = ?
        ORDER BY a.date DESC
    ");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['formatted_date'] = date('F d, Y', strtotime($row['date']));
        $row['time_in_formatted'] = $row['time_in'] ? date("h:i:s A", strtotime($row['time_in'])) : '';
        $row['time_out_formatted'] = $row['time_out'] ? date("h:i:s A", strtotime($row['time_out'])) : '';
        $row['status'] = (strtotime($row['time_in']) <= strtotime($row['schedule_in'])) ? 'On Time' : 'Late';
        $attendance_records[] = $row;
    }
    $stmt->close();
}

// Salary calculation
$startMonth = $_GET['start_month'] ?? null;
$endMonth = $_GET['end_month'] ?? null;
$year = $_GET['year'] ?? null;
$salaryData = null;

if ($startMonth && $endMonth && $year) {
    $startDate = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
    $endDate = $year . '-' . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . '-01';
    $endDate = date("Y-m-t", strtotime($endDate));

    $stmt = $conn->prepare("
        SELECT 
            e.employee_id,
            CONCAT(e.first_name, ' ', e.last_name) AS full_name,
            r.role,
            s.time_in AS schedule_in,
            s.time_out AS schedule_out,
            COUNT(a.date) AS total_work_days,
            ROUND(SUM(TIME_TO_SEC(TIMEDIFF(a.time_out, a.time_in))/3600), 2) AS total_work_hours,
            ROUND(SUM(TIME_TO_SEC(TIMEDIFF(a.time_out, a.time_in))/3600 * r.rate_per_hour), 2) AS salary
        FROM attendance a
        JOIN employees e ON e.employee_id = a.employee_id
        JOIN roles r ON r.role_id = e.role_id
        JOIN schedule s ON s.employee_id = e.employee_id
        WHERE a.date BETWEEN ? AND ? AND e.employee_id = ?
        GROUP BY e.employee_id
    ");
    $stmt->bind_param("sss", $startDate, $endDate, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $salaryData = $row;
    } else {
        $_SESSION['payroll_message'] = "No payroll records found for the selected Month range.";
    }
    $stmt->close();
}

$average_work_hours = $total_work_hours / ($salaryData['total_work_days'] ?? 1);

// Fetch rate per hour
$stmt = $conn->prepare("
    SELECT r.rate_per_hour
    FROM employees e
    JOIN roles r ON r.role_id = e.role_id
    WHERE e.employee_id = ?
");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->bind_result($rate_per_hour);
$stmt->fetch();
$stmt->close();

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: employeelogin.php?logout=You have been successfully logged out.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Dashboard</title>
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background-color: #ecf0f5;
      color: black;
    }
    
    .attendance-buttons {
      justify-content: flex-start !important;
      margin-left: 0 !important;
    }

    /* Header Styles */
    .main-header {
      height: 45px;
      border-bottom: 2px solid #2a628f;
      display: flex;
      align-items: center;
      padding: 0 20px;
      position: relative;
    }

    .header-container {
      display: flex;
      width: 100%;
      align-items: center;
    }

    .dashboard-title {
      font-size: 25px;
      font-weight: bold;
      color: #000;
      margin: 0 0 0 340px;
      flex-grow: 1;
    }

    .logout-btn {
      padding: 7px 14px;
      background-color: #dd4b39;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-left: auto;
    }

    /* Sidebar Styles */
    .main-sidebar {
      width: 320px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      color: white;
      background: url('bg1.png') no-repeat center center;
      background-size: cover;
     
      overflow-y: auto;
      z-index: 1000;
    }
    

    .sidebar {
      padding-top: 20px;
    }

    .user-panel {
      text-align: center;
      padding: 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .user-panel .image img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 3px solid white;
      background: #2a628f;
      padding: 3px;
      object-fit: cover;
    }

    .user-panel .info span {
      display: block;
      font-weight: bold;
      font-size: 18px;
      margin-top: 10px;
    }

    .user-panel .status {
      margin-top: 5px;
      font-size: 13px;
      color: #4cd137;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    /* Main Content Styles */
    .content-wrapper {
      margin-left: 320px;
      padding: 20px;
    }

    .dashboard-box {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      text-align: left;
    }

    .flex-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .stat-box {
      flex: 1;
      min-width: 200px;
      padding: 15px;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Attendance Styles */
    .attendance-summary {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 10px;
    }

    .attendance-count {
      flex: 1;
      text-align: center;
      padding: 40px;
      border-radius: 8px;
      background-color: rgba(255, 255, 255, 0.2);
    }

    .attendance-count .number {
      font-size: 26px;
      font-weight: bold;
      margin: 5px 0;
    }

    .attendance-buttons {
      display: flex;
      gap: 15px;
      margin-top: 15px;
      justify-content: flex-start;
    }

    /* Table Styles */
    table {
      background: white;
      border: 1px solid #ccc;
      width: 100%;
      border-radius: 6px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 15px;
      text-align: center;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #2a628f;
      color: white;
      font-weight: bold;
    }

    /* Status Badges */
    .status-badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 999px;
      font-size: 16px;
      text-transform: lowercase;
      margin-left: 8px;
      vertical-align: middle;
    }
    
    /* Form Controls */
    .select-group {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 10px;
      margin-bottom: 20px;
    }

    .select-group select,
    .select-group button {
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .select-group button {
      background-color: #2a628f;
      color: white;
      border: none;
      transition: background-color 0.3s;
    }

    .select-group button:hover {
      background-color: #1d4b6f;
    }

    /* Box Styles */
    .salary-box, .no-record-box {
      background: white;
      padding: 10px;
      margin-top: 10px;
      border-radius: 10px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      text-align: center;
    }

    .no-record-box {
      border: 2px dashed #ccc;
      color: #777;
    }

    /* Button Styles */
    .btn {
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      border: none;
      transition: all 0.3s;
    }

    .btn-primary {
      background-color: #2a628f;
      color: white;
    }

    .btn-primary:hover {
      background-color: #1d4b6f;
    }

    .btn-warning {
      background-color: #2a628f;
      color: white;
    }

    .btn-warning:hover {
      background-color: #e67e22;
    }

    .btn-success {
      background-color: #27ae60;
      color: white;
    }

    .btn-success:hover {
      background-color: #2ecc71;
    }

    .stat-box.blue-bg {
      background: #2a628f;
      color: white;
    }

    .sidebar-box {
      background-color: rgba(255, 255, 255, 0.1);
      border: 2px solid #ffffff55;
      border-radius: 10px;
      padding: 15px;
      margin: 30px 15px 15px 15px;
    }

    .sidebar-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 12px 0;
      font-size: 14px;
    }

    .sidebar-item .label {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sidebar-item .value {
      font-weight: bold;
    }
.month-year-range-box {
  text-align: center; 
}

.select-group {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin: 15px auto;
  max-width: 600px;
}
.select-group button {
  margin: 0; 
}
  </style>
</head>
<body>
<!-- Header -->
<header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Employee Summary</h1>
    <form method="get" action="employeedashboard.php">
      <input type="hidden" name="logout" value="true">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</header>

<aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="image">
        <img src="avatar1.jpg" alt="User Image">
      </div>
      <div class="info">
        <?php if ($employee_exists): ?>
          <span><?= htmlspecialchars($first_name . ' ' . $last_name) ?></span>
          <small style="display: block; font-size: 13px; color: #ccc;"><?= htmlspecialchars($employee_id) ?></small>
        <?php else: ?>
          <span>Unknown User</span>
          <small style="display: block; font-size: 13px; color: #ccc;">No ID</small>
        <?php endif; ?>
      </div>
      <div class="status"><i class="fa fa-circle"></i> Online</div>
    </div>

    <div class="sidebar-box">
      <div class="sidebar-item">
        <div class="label"><i class="fa-solid fa-clock"></i> Total Work Hours</div>
        <div class="value"><?= $total_work_hours ?> hrs</div>
      </div>
      <div class="sidebar-item">
        <div class="label"><i class="fa-solid fa-calendar-day"></i> Work Days</div>
        <div class="value"><?= isset($salaryData['total_work_days']) ? $salaryData['total_work_days'] : '0' ?> days</div>
      </div>
      <div class="sidebar-item">
        <div class="label"><i class="fa-solid fa-chart-line"></i> Avg Hours/Day</div>
        <div class="value"><?= number_format($average_work_hours, 2) ?> hrs</div>
      </div>
      <div class="sidebar-item">
        <div class="label"><i class="fa-solid fa-money-bill-wave"></i> Rate/Hour</div>
        <div class="value"><?= isset($rate_per_hour) ? '₱' . number_format($rate_per_hour, 2) : 'N/A' ?></div>
      </div>
    </div>
  </section>
</aside>

<div class="content-wrapper">
  <div class="dashboard-box">
    <h2>Welcome <b><i><?php echo $first_name . ' ' . $last_name; ?>!!</i></b></h2>
    
    <!-- Attendance Recording Section -->
    <div class="dashboard-box">
      <h3>Record Attendance</h3>
      
      <div class="attendance-buttons">
        <?php if (!$has_timed_in_today): ?>
          <button id="time-in-btn" class="btn btn-primary">Time In</button>
        <?php elseif (!$has_timed_out_today): ?>
          <button id="time-out-btn" class="btn btn-warning">Time Out</button>
        <?php else: ?>
          <p>You have completed today's attendance</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="flex-container">
      <div class="stat-box blue-bg">
        <h3>Attendance Summary</h3>
        <div class="attendance-summary">
          <div class="attendance-count">
          <h3>Days Attended</h3>
          <p style="font-size: 24px;"><?php echo $total_presents; ?></p>
          </div>
          <div class="attendance-count">
          <h3>Late Arrivals </h3>
          <p style="font-size: 24px;"><?php echo $total_lates; ?></p>
          </div>
        </div>
        <p style="margin-top: 15px; text-align: center;">Total Number of Attendance: <?= $attendance_count ?></p>
      </div>

      <div class="stat-box" style="background: #2a628f;">
        <h3 style="background: #2a628f; color: white; padding: 10px; border-radius: 5px;">Salary Summary</h3>
        <div class="salary-box">
          <p>Payroll Period</p>
          <div class="month-year-range-box">
            <label style="text-align:left;">Select Month Range:</label>
            <div class="select-group">
              <select id="start-month">
                <option value="" disabled selected>Start Month</option>
                <?php for ($i = 1; $i <= 12; $i++) {
                  echo "<option value='$i'" . (($startMonth == $i) ? " selected" : "") . ">" . date('F', mktime(0, 0, 0, $i, 10)) . "</option>";
                } ?>
              </select>
              <select id="end-month">
                <option value="" disabled selected>End Month</option>
                <?php for ($i = 1; $i <= 12; $i++) {
                  echo "<option value='$i'" . (($endMonth == $i) ? " selected" : "") . ">" . date('F', mktime(0, 0, 0, $i, 10)) . "</option>";
                } ?>
              </select>
              <select id="year">
                <option value="" disabled selected>Year</option>
                <?php 
                  $currentYear = date('Y');
                  for ($i = $currentYear; $i >= 2000; $i--) {
                    echo "<option value='$i'" . (($year == $i) ? " selected" : "") . ">$i</option>";
                  }
                ?>
              </select>
              <button id="apply-date-range" class="btn btn-success">Apply</button>
            </div>
          </div>
        </div>
        <?php if ($salaryData): ?>
          <div class="salary-box">
            <h2>Total Salary: ₱<?php echo number_format($salaryData['salary'], 2); ?></h2>
          </div>
        <?php elseif (isset($_SESSION['payroll_message'])): ?>
          <div class="no-record-box">
            <h2><?php echo $_SESSION['payroll_message']; unset($_SESSION['payroll_message']); ?></h2>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="dashboard-box">
    <h3>Attendance Report</h3>
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($attendance_records)): ?>
          <?php foreach ($attendance_records as $record): ?>
            <tr>
              <td><?= htmlspecialchars($record['formatted_date']) ?></td>
              <td><?= htmlspecialchars($record['time_in_formatted']) ?></td>
              <td><?= htmlspecialchars($record['time_out_formatted']) ?></td>
              <td>
                <span class="status-badge <?= ($record['status'] === 'On Time') ? 'status-ontime' : 'status-late' ?>">
                  <?= $record['status'] ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No attendance records found for this employee.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Attendance recording
document.getElementById('time-in-btn')?.addEventListener('click', function() {
    recordAttendance('time_in');
});

document.getElementById('time-out-btn')?.addEventListener('click', function() {
    recordAttendance('time_out');
});

function recordAttendance(type) {
    fetch('record_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            employee_id: '<?= $employee_id ?>',
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while recording attendance');
    });
}

// Salary date range
document.getElementById('apply-date-range').addEventListener('click', function() {
    var startMonth = document.getElementById('start-month').value;
    var endMonth = document.getElementById('end-month').value;
    var year = document.getElementById('year').value;

    if (!startMonth || !endMonth || !year) {
        alert('Please select a valid date range.');
        return;
    }

    window.location.search = `?start_month=${startMonth}&end_month=${endMonth}&year=${year}`;
});
</script>
</body>
</html>