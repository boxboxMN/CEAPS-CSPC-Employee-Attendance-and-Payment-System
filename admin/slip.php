<?php
require_once 'db_conn.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
// Fetch data from the view
$payrollData = [];
try {
    $stmt = $pdo->query("SELECT * FROM payroll_view");
    $payrollData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching payroll data: " . $e->getMessage();
}

$username = $_SESSION['username'];
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
    .month-year-range-box {
  background: #fff;
  border: 1px solid #ccc;
  padding: 20px;
  margin: 20px 0;
  margin-left: 270px;
  width: fit-content;
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

  </style>
</head>
<body>

  <!-- Header -->
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
      <div class="image">
        <img src="avatar.png" alt="User Image">
      </div>
      <div class="info">
        <span><?= htmlspecialchars($username) ?></span>
        <div class="status"><i class="fa fa-circle"></i> Online</div>
      </div>
    </div>

    <ul class="sidebar-menu">
      <li><a href="admin_dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="attendance.php"><i class="fa fa-calendar-check"></i> Attendance</a></li>
      <!-- Dropdown Employees menu -->
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

 <!-- Payroll Card -->
 <div class="card">
    <div class="card-body">
      <!-- Calendar Controls -->
      <div class="calendar-controls">
        <i class="fas fa-calendar"></i>
        <input type="text" id="date-range" placeholder="Select date range" value="<?= htmlspecialchars($date_range) ?>" readonly>
        <button class="btn btn-success" id="process-payroll">
          <i class="fas fa-calculator"></i> Process Payroll
        </button>
      </div>

      <!-- Month-Year Range Selector (hidden initially) -->
      <div class="month-year-range-box" id="monthYearRangeSelector">
  <label>Select Date Range:</label>
  <div class="select-group">
    <select id="start-month">
      <option value="" disabled selected>Start Month</option>
      <?php for ($i = 1; $i <= 12; $i++) {
        echo "<option value='$i'>" . date('F', mktime(0, 0, 0, $i, 10)) . "</option>";
      } ?>
    </select>

    <select id="end-month">
      <option value="" disabled selected>End Month</option>
      <?php for ($i = 1; $i <= 12; $i++) {
        echo "<option value='$i'>" . date('F', mktime(0, 0, 0, $i, 10)) . "</option>";
      } ?>
    </select>

    <select id="year">
      <option value="" disabled selected>Year</option>
      <?php 
        $currentYear = date('Y');
        for ($i = 2000; $i <= $currentYear; $i++) {
          echo "<option value='$i'>$i</option>";
        }
      ?>
    </select>

    <button id="apply-date-range" class="btn btn-success">Apply</button>
  </div>
</div>

<!-- Payroll Data Table -->
<div style="margin-left:270px; margin-top: 20px; padding: 20px;">
  <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; background-color: white; border-collapse: collapse; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <thead style="background-color: #2a628f; color: white;">
      <tr>
        <th>Employee ID</th>
        <th>Full Name</th>
        <th>Role</th>
        <th>Schedule</th>
        <th>Total Work Hours</th>
        <th>Salary</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($payrollData)) : ?>
        <?php foreach ($payrollData as $row) : ?>
          <tr>
            <td><?= htmlspecialchars($row['employee_id']) ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['schedule']) ?></td>
            <td><?= number_format($row['total_work_hours'], 2) ?> hrs</td>
            <td>₱<?= number_format($row['salary'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else : ?>
        <tr>
          <td colspan="6">No payroll records found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
     
<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
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
  document.getElementById('date-range').addEventListener('click', function() {
      document.getElementById('monthYearRangeSelector').style.display = 'block';
    });

    document.getElementById('apply-date-range').addEventListener('click', function() {
      var startMonth = document.getElementById('start-month').value;
      var endMonth = document.getElementById('end-month').value;
      var year = document.getElementById('year').value;

      if (!startMonth || !endMonth || !year) {
        alert('Please select a valid date range.');
        return;
      }

      document.getElementById('date-range').value = `${startMonth}-${endMonth} ${year}`;
      document.getElementById('monthYearRangeSelector').style.display = 'none';
    });
  </script>
</body>
</html>