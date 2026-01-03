<?php
session_start();

include 'db_conn.php';

$sql = "SELECT COUNT(*) AS total FROM employees";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_employees = $row['total'];
$today = date('Y-m-d');

// Count ON TIME today
$on_time_query = "
SELECT COUNT(*) as on_time_today
FROM attendance a
JOIN schedule s ON a.employee_id = s.employee_id
WHERE a.date = '$today' AND a.time_in <= s.time_in
";
$on_time_result = mysqli_query($conn, $on_time_query);
$on_time_today = mysqli_fetch_assoc($on_time_result)['on_time_today'] ?? 0;

// Count LATE today
$late_query = "
SELECT COUNT(*) as late_today
FROM attendance a
JOIN schedule s ON a.employee_id = s.employee_id
WHERE a.date = '$today' AND a.time_in > s.time_in
";
$late_result = mysqli_query($conn, $late_query);
$late_today = mysqli_fetch_assoc($late_result)['late_today'] ?? 0;

// Calculate percentage
$total_today = $on_time_today + $late_today;
$on_time_percentage = $total_today > 0 ? ($on_time_today / $total_today) * 100 : 0;

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?logout=You have been successfully logged out.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #4361ee;
      --primary-light: #e6ebff;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --danger-color: #f72585;
      --warning-color: #f8961e;
      --info-color: #4895ef;
      --light-color: #f8f9fa;
      --dark-color: #212529;
      --gray-color: #6c757d;
      --border-color: #dee2e6;
      --white: #ffffff;
      --sidebar-bg: #1a1a2e;
      --sidebar-hover: #16213e;
      --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s ease;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fb;
      color: #333;
      line-height: 1.6;
      overflow-x: hidden;
    }
    
    /* Main Layout */
    .main-wrapper {
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar Styles */
    .main-sidebar {
      width: 280px;
      background: var(--sidebar-bg);
      color: var(--white);
      position: fixed;
      height: 100vh;
      z-index: 100;
      transition: var(--transition);
      box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .sidebar-brand {
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-brand img {
      width: 40px;
      margin-right: 10px;
    }
    
    .sidebar-brand h2 {
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0;
    }
    
    .user-panel {
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
    }
    
    .user-panel .image {
      margin-right: 15px;
    }
    
    .user-panel .image img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(255, 255, 255, 0.2);
    }
    
    .user-info h4 {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 3px;
    }
    
    .user-info .status {
      font-size: 0.8rem;
      color: #4cd137;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .user-info .status i {
      font-size: 0.6rem;
      margin-right: 5px;
    }
    
    .sidebar-menu {
      list-style: none;
      padding: 15px 0;
    }
    
    .sidebar-menu li {
      position: relative;
    }
    
    .sidebar-menu li a {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: var(--transition);
    }
    
    .sidebar-menu li a:hover,
    .sidebar-menu li.active a {
      color: var(--white);
      background-color: var(--sidebar-hover);
    }
    
    .sidebar-menu li a i {
      width: 24px;
      font-size: 1.1rem;
      margin-right: 12px;
      text-align: center;
    }
    
    .sidebar-menu li.active {
      border-left: 4px solid var(--primary-color);
    }
    
    /* Main Content Area */
    .main-content {
      margin-left: 280px;
      width: calc(100% - 280px);
      padding: 20px;
      transition: var(--transition);
    }
    
    /* Header Styles */
    .main-header {
      background: var(--white);
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: var(--card-shadow);
      position: sticky;
      top: 0;
      z-index: 90;
    }
    
    .page-title h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark-color);
      margin: 0;
    }
    
    .header-actions .btn {
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      transition: var(--transition);
    }
    
    .header-actions .btn i {
      margin-right: 8px;
    }
    
    .btn-logout {
      background: var(--danger-color);
      color: var(--white);
      border: none;
    }
    
    .btn-logout:hover {
      background: #d1145a;
      transform: translateY(-2px);
    }
    
    /* Card Styles */
    .card {
      background: var(--white);
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      overflow: hidden;
    }
    
    .card-header {
      padding: 18px 25px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--dark-color);
      margin: 0;
    }
    
    .card-body {
      padding: 25px;
    }
    
    /* Small Box Styles */
    .small-box {
      position: relative;
      color: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: var(--card-shadow);
      margin-bottom: 20px;
      transition: var(--transition);
    }
    
    .small-box:hover {
      transform: translateY(-5px);
    }
    
    .small-box .inner {
      padding: 10px;
    }
    
    .small-box .inner h3 {
      font-size: 38px;
      margin: 0 0 10px 0;
    }
    
    .small-box .inner p {
      font-size: 16px;
      margin: 0;
    }
    
    .small-box-footer {
      display: block;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      padding: 10px 0;
      text-align: center;
      background: rgba(0, 0, 0, 0.1);
      transition: var(--transition);
    }
    
    .small-box-footer:hover {
      color: var(--white);
      background: rgba(0, 0, 0, 0.2);
    }
    
    .small-box-footer i {
      margin-left: 5px;
    }
    
    .bg-aqua { background-color: var(--info-color); }
    .bg-green { background-color: var(--success-color); }
    .bg-yellow { background-color: var(--warning-color); }
    .bg-red { background-color: var(--danger-color); }
    
    /* Box Styles */

   
/* Remove any max-width constraints if they exist */
.box {
    width: 100%;
    margin: 0;
  }

  /* Adjust the small boxes to match */
  .small-box {
    height: 100%;
  }
    .box {
      background: var(--white);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: var(--card-shadow);
      width: 100%;
    }
    
    .box-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 20px;
    }
    
    .box-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0;
    }
    
     /* Add this to fix the row spacing */
  .content {
    padding: 0;
  }

  /* Update the row styles */
  .row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
    width: calc(100% + 20px);
  }



     /* Update the column styles */
  .col-lg-3, .col-xs-6, .col-lg-12 {
    padding: 0 10px;
    margin-bottom: 20px;
  }
    
    .col-lg-3 {
    flex: 0 0 25%;
    max-width: 25%;
  }

  .col-lg-12 {
    flex: 0 0 100%;
    max-width: 100%;
  }
    
    @media (max-width: 992px) {
      .col-lg-3 {
        flex: 0 0 50%;
        max-width: 50%;
      }
    }
    
    @media (max-width: 768px) {
      .col-lg-3, .col-xs-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }
    
    /* Chart Styles */
    .chart {
      width: 100%;
      height: 350px;
      position: relative;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 992px) {
      .main-sidebar {
        transform: translateX(-100%);
      }
      
      .main-sidebar.show {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        width: 100%;
      }
    }

    /* Treeview Styles */
.sidebar-menu .treeview-menu {
    display: none;
    list-style: none;
    padding-left: 30px;
    background-color: rgba(0, 0, 0, 0.1);
}

.sidebar-menu .treeview-menu li a {
    padding: 10px 15px;
    font-size: 0.9rem;
}

.sidebar-menu .treeview.active > .treeview-menu {
    display: block;
}

.sidebar-menu .treeview > a > .float-right {
    transition: transform 0.3s ease;
}

.sidebar-menu .treeview.active > a > .float-right {
    transform: rotate(180deg);
}
  </style>
</head>
<body>
  <div class="main-wrapper">
    <!-- Sidebar -->
    <aside class="main-sidebar">
      <div class="sidebar-brand">
        <img src="logo.png" alt="Company Logo">
        <h2>ADMIN</h2>
      </div>
      
      <div class="user-panel">
        <div class="image">
          <img src="avatar.png" alt="User Image">
        </div>
        <div class="user-info">
          <h4><?= htmlspecialchars($username) ?></h4>
          <span class="status"><i class="fas fa-circle"></i> Online</span>
        </div>
      </div>
      
      <ul class="sidebar-menu">
    <li class="active"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
    
    <li class="treeview">
        <a href="#"><i class="fas fa-users"></i> Employees <i class="fas fa-angle-down float-right"></i></a>
        <ul class="treeview-menu">
            <li><a href="employee_list.php"><i class="fas fa-list"></i> Employee List</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
        </ul>
    </li>
    
    <li><a href="role.php"><i class="fas fa-user-tie"></i> Roles</a></li>
    <li><a href="payroll.php"><i class="fas fa-money-bill-wave"></i> Payroll</a></li>
</ul>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <header class="main-header">
        <div class="page-title">
          <h1>Dashboard</h1>
        </div>
        <div class="header-actions">
          <form method="get" action="admin_dashboard.php">
            <input type="hidden" name="logout" value="true">
            <button type="submit" class="btn btn-logout">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </div>
      </header>
      
      <!-- Dashboard Content -->
      <!-- Previous code remains the same until the dashboard content section -->

      <!-- Dashboard Content -->
      <section class="content">
        <div class="row">
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3><?php echo $total_employees; ?></h3>
                <p>Total Employees</p>
              </div>
              <a href="employee_list.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
              <div class="inner">
                <h3><?php echo number_format($on_time_percentage, 2); ?><sup style="font-size: 20px">%</sup></h3>
                <p>On Time Percentage</p>
              </div>
              <a href="attendance.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3><?php echo $on_time_today; ?></h3>
                <p>On Time Today</p>
              </div>
              <a href="attendance.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
              <div class="inner">
                <h3><?php echo $late_today; ?></h3>
                <p>Late Today</p>
              </div>
              <a href="attendance.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">Monthly Attendance Report</h3>
                <div class="box-tools">
                  <form class="form-inline">
                    <label>Select Year:</label>
                    <select class="form-control" id="select_year">
                      <option value="2025" selected>2025</option>
                      <option value="2024">2024</option>
                      <option value="2023">2023</option>
                    </select>
                  </form>
                </div>
              </div>
              <div class="box-body">
                <div class="chart">
                  <canvas id="barChart" style="height:350px"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Rest of the code remains the same -->

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

    let myChart;

    $(function () {
      const ctx = $('#barChart').get(0).getContext('2d');

      function fetchAndRenderChart(year) {
        $.get('get_attendance_summary.php', { year }, function (data) {
          const parsedData = JSON.parse(data);
          if (myChart) {
            myChart.data.datasets[0].data = parsedData.late;
            myChart.data.datasets[1].data = parsedData.ontime;
            myChart.update();
          } else {
            myChart = createChart(ctx, parsedData);
          }
        });
      }

      function createChart(ctx, data) {
        return new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                     'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
              {
                label: 'Late',
                backgroundColor: 'rgba(210, 214, 222, 1)',
                data: data.late
              },
              {
                label: 'Ontime',
                backgroundColor: '#00a65a',
                data: data.ontime
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
              display: true,
              position: 'top'
            }
          }
        });
      }

      // Initial render
      fetchAndRenderChart($('#select_year').val());

      $('#select_year').change(function () {
        fetchAndRenderChart($(this).val());
      });
    });

    // Treeview functionality
document.querySelectorAll('.treeview > a').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.parentElement;
        parent.classList.toggle('active');
        
        // Close other open treeviews
        document.querySelectorAll('.sidebar-menu .treeview').forEach(sibling => {
            if (sibling !== parent && sibling.classList.contains('active')) {
                sibling.classList.remove('active');
            }
        });
    });
});
  </script>
</body>
</html>