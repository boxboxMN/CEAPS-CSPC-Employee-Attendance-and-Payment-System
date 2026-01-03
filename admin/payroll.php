<?php
include '../db_conn.php'; // Include DB connection
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}


$startMonth = $_GET['start_month'] ?? null;
$endMonth = $_GET['end_month'] ?? null;
$year = $_GET['year'] ?? null;

$payrollData = [];

if ($startMonth && $endMonth && $year) {
    // Create start and end date based on selected month and year
    $startDate = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01'; // Start of startMonth
    $endDate = $year . '-' . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . '-01'; // Start of endMonth

    // Adjust endDate to last day of the end month
    $endDate = date("Y-m-t", strtotime($endDate)); // This will set the date to the last day of the month

    $stmt = $conn->prepare("
        SELECT 
            e.employee_id,
            CONCAT(e.first_name, ' ', e.last_name) AS full_name,
            r.role,
            s.time_in AS schedule_in,
            s.time_out AS schedule_out,
            COUNT(a.date) AS total_work_days,
           ROUND(SUM(TIME_TO_SEC(TIMEDIFF(a.time_out, a.time_in)))/3600, 2) AS total_work_hours,
          ROUND(SUM(TIME_TO_SEC(TIMEDIFF(a.time_out, a.time_in)))/3600 * r.rate_per_hour, 2) AS salary
        FROM attendance a
        JOIN employees e ON e.employee_id = a.employee_id
        JOIN roles r ON r.role_id = e.role_id
        JOIN schedule s ON s.employee_id = e.employee_id
        WHERE a.date BETWEEN ? AND ?
        GROUP BY e.employee_id
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    while ($row = $result->fetch_assoc()) {
        $payrollData[] = $row;
    }
}

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] == 'excel' && !empty($payrollData)) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="payroll_report_' . date('Y-m-d') . '.xls"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, array('Employee ID', 'Full Name', 'Role', 'Schedule', 'Total Work Days', 'Total Work Hours', 'Salary'), "\t");
    
    // Add data
    foreach ($payrollData as $row) {
        $schedule = date("h:i A", strtotime($row['schedule_in'])) . ' - ' . date("h:i A", strtotime($row['schedule_out']));
        $data = array(
            $row['employee_id'],
            $row['full_name'],
            $row['role'],
            $schedule,
            number_format($row['total_work_days'], 0) . ' days',
            number_format($row['total_work_hours'], 2) . ' hrs',
            '₱' . number_format($row['salary'], 2)
        );
        fputcsv($output, $data, "\t");
    }
    
    fclose($output);
    exit();
}

$username = $_SESSION['username'];
// Fetch admin details
$sql = "SELECT * FROM admin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?logout=You have been successfully logged out.");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background-color: #ecf0f5;
      color: #333;
    }
    .main-header {
    height: 45px; 
    border-bottom: 2px solid #2a628f;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 0 20px;
    position: relative;
    }
    
.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-title {
  font-size: 22px;
  font-weight: bold;
  margin: 0;
  color: #000;
  margin-right: 138.8vh;
}
.main-header form button {
      padding: 6px 14px;
      background-color: #dd4b39;;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .main-sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      color: white;
      background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(30, 29, 29, 0.9)),
                  url('bg1.png') no-repeat center center;
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
      width: 60px;
      height: 60px;
      border-radius: 50%;
      border: 2px solid white;
    }

    .user-panel .info span {
      display: block;
      font-weight: bold;
      font-size: 18px;
      margin-top: 10px;
    }

    .user-panel .info .status {
      font-size: 12px;
      color: #4cd137;
    }

    .sidebar-menu,
    .sidebar-menu ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-menu li {
      padding: 12px 25px;
      transition: background 0.3s ease;
    }

    .sidebar-menu li:hover,
    .sidebar-menu li.active {
      background: rgba(255,255,255,0.1);
    }

    .sidebar-menu li a {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
    }

    .sidebar-menu li a i {
      width: 20px;
      margin-right: 15px;
    }

    .treeview-menu {
      padding-left: 20px;
      display: none;
    }

    .treeview.active .treeview-menu {
      display: block;
    }
    .filter-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-left: 270px;
  margin-top: 20px;
  width: calc(100% - 290px);
}

.month-year-range-box {
  background: #fff;
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.month-year-range-box label {
  font-weight: bold;
  font-size: 16px;
  display: block;
  margin-bottom: 10px;
  color: #2a628f;
}

.select-group {
  display: flex;
  align-items: center;
  gap: 15px;
}

.select-group select,
.select-group button {
  padding: 6px 12px;
  font-size: 14px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.select-group button {
  background-color: #2ecc71;
  color: white;
  border: none;
  cursor: pointer;
  transition: background 0.3s ease;
}

.select-group button:hover {
  background-color: #27ae60;
}

.export-btn a {
  display: inline-block;
  padding: 8px 15px;
  background-color: #2a628f;
  color: white;
  text-decoration: none;
  border-radius: 5px;
  transition: background 0.3s ease;
}

.export-btn a:hover {
  background-color: #1d4b6f;
}
.user-panel {
  text-align: center;
  padding: 20px 0;
}
.profile-container {
  text-align: center;
  padding: 20px;
}

.profile-pic {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #fff;
  transition: 0.3s;
}

.profile-pic:hover {
  transform: scale(1.05);
}

.username {
  font-weight: bold;
  margin-top: 10px;
}

.status-dot {
  height: 10px;
  width: 10px;
  background-color: #00cc44;
  border-radius: 50%;
  display: inline-block;
}

.status {
  font-size: 14px;
  color: green;
}

  </style>
</head>
<body>
<header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Payroll List</h1>
    <form action="admin_main.php" method="POST">
      <form method="get" action="admin_dashboard.php" style="display: inline;">
        <input type="hidden" name="logout" value="true">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
  </div>
</header>


  <!-- Sidebar -->
  <aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="profile-container">
        <!-- Clickable image -->
        <div style.display='block' style="cursor:pointer;">
          <?php if (!empty($row['photo'])): ?>
            <img class="profile-pic" src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Admin Photo">
          <?php else: ?>
            <img class="profile-pic" src="default-profile.png" alt="Default Photo">
          <?php endif; ?>
        </div>
        <div class="username"><?= htmlspecialchars($row['username']) ?></div>
        <div class="status"><span class="status-dot"></span> Online</div>
      </div>
    </div>

    <ul class="sidebar-menu">
      <li><a href="admin_dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="attendance.php"><i class="fa fa-calendar-check"></i> Attendance</a></li>
      <li class="treeview" id="employee-menu">
        <a href="javascript:void(0);" onclick="toggleEmployeeMenu()">
          <i class="fa fa-users"></i> <span>Employees</span>
          <i id="employee-arrow" class="fa fa-angle-left" style="margin-left:auto;"></i>
        </a>
        <ul class="treeview-menu" style="display: none;">
          <li><a href="employee_list.php">Employee list</a></li>
          <li><a href="schedule.php">Schedules</a></li>
        </ul>
      </li>
      <li><a href="role.php"><i class="fa fa-suitcase"></i> Role</a></li>
      <li><a href="payroll.php"><i class="fa-solid fa-money-check-dollar" style=" color: white"></i> Payroll</a></li>
    </ul>
  </section>
</aside>
<div class="filter-container" style="display: flex; justify-content: space-between; align-items: center; margin-left: 270px; margin-top: 20px; width: calc(100% - 290px);">
  <div class="month-year-range-box">
    <label>Select Month Range:</label>
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
         $currentYear = 2025;  // Set the current year to 2025
         for ($i = 2025; $i >= 2023; $i--) {
             echo "<option value='$i'" . (($year == $i) ? " selected" : "") . ">$i</option>";
         }         
        ?>
      </select>
      <button id="apply-date-range" class="btn btn-success">Apply</button>
    </div>
  </div>

  <?php if (!empty($payrollData)) : ?>
  <div class="export-btn">
    <a href="?export=excel&start_month=<?= $startMonth ?>&end_month=<?= $endMonth ?>&year=<?= $year ?>">
      <i class="fas fa-file-excel"></i> Export to Excel
    </a>
  </div>
  <?php endif; ?>
</div>



<div style="margin-left:270px; margin-top: 20px; padding: 20px;">
  <table border="0" cellpadding="10" cellspacing="0" style="width: 100%; background-color: white; border-collapse: separate; border-spacing: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden;">
    <thead style="background-color: #2a628f; color: white;">
      <tr>
        <th style="border-top-left-radius: 8px;">Employee ID</th>
        <th>Full Name</th>
        <th>Role</th>
        <th>Schedule</th>
        <th>Total Work Days</th>
        <th>Total Work Hours</th>
        <th style="border-top-right-radius: 8px;">Salary</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($payrollData)) : ?>
      <?php foreach ($payrollData as $row) : ?>
        <tr>
          <td><?= htmlspecialchars($row['employee_id']) ?></td>
          <td><?= htmlspecialchars($row['full_name']) ?></td>
          <td><?= htmlspecialchars($row['role']) ?></td>
          <td>
          <?= date("h:i A", strtotime($row['schedule_in'])) ?> - 
          <?= date("h:i A", strtotime($row['schedule_out'])) ?>
          </td>

          <td><?= number_format($row['total_work_days'], 0) ?> days</td>
          <td><?= number_format($row['total_work_hours'], 2) ?> hrs</td>
          <td>₱<?= number_format($row['salary'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
      <tr><td colspan="7">No payroll records found for the selected Month range.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
   function toggleEmployeeMenu() {
  const menu = document.querySelector('#employee-menu .treeview-menu');
  const arrow = document.getElementById('employee-arrow');
  if (menu.style.display === 'block') {
    menu.style.display = 'none';
    arrow.classList.remove('fa-angle-down');
    arrow.classList.add('fa-angle-left');
  } else {
    menu.style.display = 'block';
    arrow.classList.remove('fa-angle-left');
    arrow.classList.add('fa-angle-down');
  }
}
  document.getElementById('apply-date-range').addEventListener('click', function() {
    var startMonth = document.getElementById('start-month').value;
    var endMonth = document.getElementById('end-month').value;
    var year = document.getElementById('year').value;

    if (!startMonth || !endMonth || !year) {
      alert('Please select a valid date range.');
      return;
    }

    // Reload page with GET params
    const queryParams = new URLSearchParams({
      start_month: startMonth,
      end_month: endMonth,
      year: year
    });

    window.location.search = queryParams.toString();
  });
</script>

</body>
</html>