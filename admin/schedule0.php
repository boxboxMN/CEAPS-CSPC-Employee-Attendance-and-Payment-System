<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

$username = $_SESSION['username'];
include '../db_conn.php';

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
  margin-right: 35em;
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
  
    .employee-table {
      width:77%;
      margin-left: 17em;
      margin-top: 30px;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
    }

    .employee-table th,
    .employee-table td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }

    .employee-table th {
      background-color: #2a628f;
      color: white;
      font-weight: bold;
    }

    .employee-table td a {
      color:rgb(255, 255, 255);
      text-decoration: none;
    }

    .employee-table tr:hover {
    background-color: #f1f1f1;
  }
/* Edit Modal Styles */
#editModal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.6);
  z-index: 2000;
  justify-content: center;
  align-items: center;
}

#editModal > div {
  background: #fff;
  padding: 30px;
  border-radius: 10px;
  width: 400px;
  max-height: 90vh; /* takes up max 90% of the viewport height */
  box-shadow: 0 0 20px rgba(0,0,0,0.3);
  position: relative;
  overflow-y: auto;
}

#editModal h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #007BFF;
  font-size: 20px;
}

#editModal label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
  margin-top: 15px;
}

#editModal input[type="text"],
#editModal select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

#editModal button {
  margin-top: 20px;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
}

#editModal button[type="submit"] {
  background-color: #28a745;
  color: white;
}

#editModal button[type="button"] {
  background-color: #dc3545;
  color: white;
  margin-left: 10px;
}


  .alert-message.error {
      color: red;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
  }

  .edit-btn {
  background-color: #28a745; /* Green */
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.edit-btn:hover {
  background-color: #218838;
}

.delete-btn {
  background-color: #dc3545; /* Red */
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.delete-btn:hover {
  background-color: #c82333;
}
.box{
  height: 28em;
}

  </style>
</head>
<body>

  <!-- Header -->
  <header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Schedules List</h1>
    <form action="admin_main.php" method="POST">
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
<?php
if (isset($_SESSION['message'])) {
    echo '<div class="alert-message" style="margin-left: 15em;padding: 10px; background-color: #28a745; color: white; font-size: 16px; text-align:center;">' . 
         htmlspecialchars($_SESSION['message']) . 
         '</div>';
    unset($_SESSION['message']);
}
?>
<div style="display: flex; justify-content: flex-end; margin: 20px 20px 10px 0;">
  <label for="scheduleSearch" style="font-weight: 500; margin-right: 10px; padding-top:.5em;">Search:</label>
  <input type="text" id="scheduleSearch" onkeyup="searchSchedule()" placeholder="Search employee name or role..." 
  style="padding: 5px 10px; border-radius: 6px; border: 1px solid #ccc; width: 220px;height:1.8em; font-size: 14px; ">
</div>




<table class="employee-table">
  <thead>
    <tr>
      <th>Employee ID</th>
      <th>Name</th>
      <th>Role</th> 
      <th>Time In</th>
      <th>Time Out</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php
  // Role-to-ID mapping
  $roleIds = [
      "Software Engineer" => 1,
      "System Administrator" => 2,
      "Database Administrator" => 3,
      "Network Engineer" => 4,
      "Other" => 5
  ];

  $query = "SELECT schedule.id as schedule_id, employees.*, employees.role_id, roles.role, schedule.time_in, schedule.time_out
    FROM employees
    LEFT JOIN schedule ON employees.employee_id = schedule.employee_id
    LEFT JOIN roles ON employees.role_id = roles.role_id
    ORDER BY schedule.time_in DESC";


  $result = $conn->query($query);

  if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          $fullName = $row['first_name'] . " " . $row['last_name'];
          $role = $row['role'];
          $timeIn = $row['time_in'] ?? '';
          $timeOut = $row['time_out'] ?? '';
          
          $formattedTimeIn = $timeIn ? date("h:i:s  A", strtotime($timeIn)) : '';
          $formattedTimeOut = $timeOut ? date("h:i:s  A", strtotime($timeOut)) : '';
          
          echo "<tr>
          <td>{$row['employee_id']}</td>
          <td>{$fullName}</td>
          <td>{$role}</td>
          <td>{$formattedTimeIn}</td>
          <td>{$formattedTimeOut}</td>
          <td>
            <a href=\"javascript:void(0);\" class=\"edit-btn\" onclick=\"openEditModal(
              '{$row['employee_id']}',
              '{$row['first_name']}',
              '{$row['last_name']}',
              '{$role}',
              '{$formattedTimeIn}',
              '{$formattedTimeOut}'
            )\">Edit</a>
            <a href=\"delete_schedule.php?id={$row['schedule_id']}\" class=\"delete-btn\" onclick=\"return confirm('Are you sure you want to delete this schedule?')\">Delete</a>
          </td>
          </tr>";
          

      }
  } else {
      echo "<tr><td colspan='7'>No schedules found.</td></tr>";
  }
  ?>
  </tbody>
</table>



<!-- Edit Modal -->
<div id="editModal" style="display:none;">
  <div class="box">
    <h3>Edit Schedule</h3>
    <form id="editForm" method="post" action="save_schedule.php">
      <input type="hidden" id="edit_employee_id" name="employee_id">
      <label>First Name:</label>
      <input type="text" name="first_name" id="edit_first_name" required readonly>
      <label>Last Name:</label>
      <input type="text" name="last_name" id="edit_last_name" required readonly>
      <label>Role:</label>
      <input type="text" name="role" id="edit_role" required readonly>
      <label>Time in:</label>
      <input type="text" name="time_in" id="edit_time_in" required>
      <label>Time out:</label>
      <input type="text" name="time_out" id="edit_time_out" required>

      <button type="submit">Save</button>
      <button type="button" onclick="closeModal()">Cancel</button>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
function openEditModal(id, firstName, lastName, role, timeIn = '', timeOut = '') {
    document.getElementById("edit_employee_id").value = id;
    document.getElementById("edit_first_name").value = firstName;
    document.getElementById("edit_last_name").value = lastName;
    document.getElementById("edit_role").value = role;
    
    document.getElementById("edit_time_in").value = timeIn;
    document.getElementById("edit_time_out").value = timeOut;

    document.getElementById("editModal").style.display = "flex";
}

function updateTimeFields() {
    const schedule = document.getElementById("edit_schedule").value;
    const [timeIn, timeOut] = schedule.split(" - ");
    document.getElementById("edit_time_in").value = timeIn.trim();
    document.getElementById("edit_time_out").value = timeOut.trim();
}


function closeModal() {
    document.getElementById("editModal").style.display = "none";
}

function searchSchedule() {
  const input = document.getElementById("scheduleSearch");
  const filter = input.value.toLowerCase();
  const table = document.querySelector(".employee-table tbody");
  const rows = table.getElementsByTagName("tr");
  let found = false;

  for (let i = 0; i < rows.length; i++) {
    const cols = rows[i].getElementsByTagName("td");
    let rowMatch = false;
    
    for (let j = 0; j < cols.length - 1; j++) { // exclude last col (action buttons)
      if (cols[j] && cols[j].textContent.toLowerCase().includes(filter)) {
        rowMatch = true;
        break;
      }
    }

    if (rowMatch) {
      rows[i].style.display = "";
      found = true;
    } else {
      rows[i].style.display = "none";
    }
  }

  // Optional: Show "No result found" row
  const noResultRow = document.getElementById("noResultRow");
  if (!found) {
    if (!noResultRow) {
      const row = document.createElement("tr");
      row.id = "noResultRow";
      row.innerHTML = `<td colspan="7" style="text-align:center; color: red;">No result found.</td>`;
      table.appendChild(row);
    }
  } else {
    if (noResultRow) noResultRow.remove();
  }
}
flatpickr("#edit_time_in", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i:S K", // 12-hour format with seconds + AM/PM
    time_24hr: false
  });

  flatpickr("#edit_time_out", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i:S K",
    time_24hr: false
  });


</script>
</body>
</html>