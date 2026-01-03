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
// Fetch admin details
$sql = "SELECT * FROM admin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

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
  margin-right: 38em;
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

    .content-wrapper {
      margin-left: 250px;
      padding: 20px;
    }

    .content-header h1 {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .col-lg-3, .col-xs-6 {
      flex: 1 1 22%;
      min-width: 200px;
    }

    .small-box {
      position: relative;
      background-color: #00c0ef;
      color: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .small-box.bg-green { background-color: #00a65a; }
    .small-box.bg-yellow { background-color: #f39c12; }
    .small-box.bg-red { background-color: #dd4b39; }

    .small-box .inner h3 {
      font-size: 38px;
      margin: 0;
    }

    .small-box .inner p {
      font-size: 16px;
      margin: 10px 0 0;
    }

    .small-box-footer {
      display: block;
      color: white;
      text-decoration: none;
      margin-top: 15px;
    }

    .box {
      background: #fff;
      border-radius: 5px;
      padding: 20px;
      margin-bottom:10px;
      margin-top: 15px;
      width: 160vh;
      height: 92%;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      margin-left:2vh;
    }

    .box-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .box-title {
      font-size: 18px;
    }

    .form-inline label {
      margin-right: 10px;
    }

    .form-inline select {
      padding: 5px 10px;
    }

    .chart {
      width: 100%;
      height: 350px;
    }

    #legend {
      text-align: center;
      margin-top: 10px;
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

/* Modal backdrop */
#editModal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 9999;
  padding-top: 60px;
  font-family: 'Poppins', sans-serif;
}

/* Modal content box */
#editModal .modal-content {
  background: #fff;
  margin: auto;
  padding: 30px 25px;
  width: 400px;
  border-radius: 12px;
  position: relative;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

/* Modal heading */
#editModal h3 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 22px;
  color: #333;
}

/* Form inputs */
#editModal form label {
  display: block;
  margin-top: 15px;
  margin-bottom: 6px;
  font-weight: 500;
  color: #444;
}

#editModal form input[type="text"],
#editModal form input[type="password"],
#editModal form input[type="file"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  box-sizing: border-box;
}

#editModal form input[type="file"] {
  padding: 6px;
}

/* Buttons */
#editModal form button {
  padding: 10px 18px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  margin-top: 20px;
  cursor: pointer;
  margin-right: 10px;
}

/* Submit Button */
#editModal form button[type="submit"] {
  background-color: #003d91;
  color: white;
}

#editModal form button[type="submit"]:hover {
  background-color: #002d6b;
}

/* Cancel Button */
#editModal form button[type="button"] {
  background-color: #ccc;
  color: #333;
}

#editModal form button[type="button"]:hover {
  background-color: #bbb;
}
.update-success {
  display: none;
  background-color: #e6f9ea;
  color: #2e7d32;
  border: 1px solid #a5d6a7;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  margin-top: 15px;
  text-align: center;
  animation: fadeIn 0.4s ease-in-out;
}

.check-icon {
  margin-right: 8px;
  font-size: 18px;
  color: #2e7d32;
  vertical-align: middle;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
  </style>
</head>
<body>

  <!-- Header -->
  <header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Admin</h1>
    
    <form method="get" action="admin_dashboard.php" style="display: inline;">
    <input type="hidden" name="logout" value="true">
    <button type="submit" class="logout-btn">Logout</button>
</form>

    

    </form>
  </div>
</header>


  <!-- Sidebar -->
  <!-- Sidebar -->
  <aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="profile-container">
        <!-- Clickable image -->
        <div onclick="document.getElementById('editModal').style.display='block'" style="cursor:pointer;">
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


<!-- Modal -->
<!-- Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:98%; 
  background-color: rgba(0,0,0,0.6); z-index:9999; padding-top:10px;">
  <div style="background:white; margin:auto; padding:3px 20px 10px 20px; width:400px; border-radius:10px; position:relative;">
    <h3>Edit Admin Info</h3>
    <form id="editAdminForm" action="update_admin.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="admin_id" value="<?= $row['id'] ?>">

      <label>Username:</label>
      <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>

      <label>First Name:</label>
      <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>" required>

      <label>Last Name:</label>
      <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" required>

      <label>Password:</label>
      <input type="password" name="password" placeholder="Enter new password">

      <label>Update Photo:</label>
      <input type="file" name="photo">
      
      <div id="updateMessage" class="update-success">
  <span class="check-icon">&#10003;</span> Successfully updated admin details!
</div>

      <div style="margin-top:3px;">
        <button type="submit">Save Changes</button>
        <button type="button" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

  <!-- Main Content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Dashboard</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
            <h3><?php echo $total_employees; ?></h3>
              <p>Total Employees</p>
            </div>
            <a href="employee_list.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
            <h3><?php echo number_format($on_time_percentage, 2); ?><sup style="font-size: 20px">%</sup></h3>
            <p>On Time Percentage</p>
            </div>
            <a href="attendance.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
            <h3><?php echo $on_time_today; ?></h3>
            <p>On Time Today</p>

            </div>
            <a href="attendance.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-red">
            <div class="inner">
            <h3><?php echo $late_today; ?></h3>
            <p>Late Today</p>
            </div>
            <a href="attendance.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Monthly Attendance Report</h3>
              <div class="box-tools pull-right">
                <form class="form-inline">
                  <label>Select Year:</label>
                  <select class="form-control input-sm" id="select_year">
                    <option value="2025" selected>2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                  </select>
                </form>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <br>
                <div id="legend" class="text-center"></div>
                <canvas id="barChart" style="height:350px"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
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
document.getElementById('editAdminForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  fetch('update_admin.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    if (data.trim() === 'success') {
      document.getElementById('updateMessage').style.display = 'block';
      setTimeout(() => {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('updateMessage').style.display = 'none';
        location.reload(); // Optional: reload to update sidebar photo/details
      }, 1500);
    } else {
      alert('Update failed: ' + data);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Something went wrong!');
  });
});

</script>

</body>
</html>
