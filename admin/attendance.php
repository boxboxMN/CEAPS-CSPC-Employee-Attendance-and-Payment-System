<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];
include '../db_conn.php';

// Fetch admin details
$sql = "SELECT * FROM admin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Debugging: check if data is fetched
if (!$row) {
    die("<p style='color:red;'>Error: Admin user not found for username: $username</p>");
}

// Log out
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?logout=You have been successfully logged out.");
    exit();
}

// Database credentials
$host = "localhost";
$dbname = "wage_warriors";
$dbuser = "root";
$dbpass = "";
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';

try {
    // ✅ Establish PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Query attendance data
    $stmt = $pdo->query("
        SELECT 
            a.id,
            a.employee_id,
            e.first_name,
            e.last_name,
            a.time_in,
            a.time_out,
            s.time_in AS schedule_in,
            DATE_FORMAT(a.date, '%M %d, %Y') AS formatted_date,
            a.date
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        JOIN schedule s ON a.employee_id = s.employee_id
        ORDER BY a.date DESC, a.time_in DESC
    ");

    $attendanceDataRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Process data
    $attendanceData = [];
    $seenIds = [];

    foreach ($attendanceDataRaw as $rowItem) {
        if (!in_array($rowItem['id'], $seenIds)) {
            $timeIn = strtotime($rowItem['time_in']);
            $scheduledIn = strtotime($rowItem['schedule_in']);
            $rowItem['status'] = ($timeIn <= $scheduledIn) ? 'On Time' : 'Late';
            $attendanceData[] = $rowItem;
            $seenIds[] = $rowItem['id'];
        }
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
      margin-right: 129vh;
    }

    .main-header form button {
      padding: 6px 14px;
      background-color: #dd4b39;
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
    .attendance-table {
      width: 77%;
      margin-left: 17em;
    margin-top: 30px;
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    }

    .attendance-table th,
    .attendance-table td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }

    .attendance-table th {
      background-color: #2a628f;
      color: white;
      font-weight: bold;
    }

    .attendance-table td a {
      color:rgb(255, 255, 255);
      text-decoration: none;
    }

    .attendance-table tr:hover {
    background-color: #f1f1f1;
  }
  .status-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  text-transform: lowercase;
  margin-left: 8px;
  vertical-align: middle;
}

.status-ontime {
  background-color: #ffc107; /* Amber/Yellow like your example */
  color: #000;
}

.status-late {
  background-color: #dc3545;
  color: #fff;
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
#attendanceEditModal {
  display: none;
  position: fixed;
  top: -10px;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.6);
  z-index: 2000;
  justify-content: center;
  align-items: center;
}

#attendanceEditModal .modal-content {
  background-color: white;
  padding: 30px;
  border-radius: 12px;
  width: 500px;
  position: relative;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

#attendanceEditModal .modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

#attendanceEditModal h2 {
  margin: 0;
}

#attendanceEditModal .close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

/* Form input styling */
#attendanceEditModal input, 
#attendanceEditModal select, 
#attendanceEditModal textarea {
  width:80%;
  padding: 10px;
  margin: 5px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}

/* Button styles */
#attendanceEditModal button {
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

#attendanceEditModal button[type="submit"] {
  background-color: #2a628f;
  color: white;
  border: none;
}

#attendanceEditModal button[type="button"] {
  background-color: #ccc;
  margin-left: 10px;
  border: none;
}

#attendanceEditModal button:hover {
  opacity: 0.9;
}

#attendanceEditModal button[type="submit"]:hover {
  background-color: #1a4978;
}

#attendanceEditModal button[type="button"]:hover {
  background-color: #999;
}

/* Search Bar Styles */
#employeeSearch {
  padding: 5px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  width: 220px;
  font-size: 14px;
}

#employeeSearch:focus {
  border-color: #2a628f;
  outline: none;
}

/* No result message */
#noResultsMessage {
  text-align: center;
  color: #888;
  margin-top: 10px;
  display: none;
}

/* Modal backdrop fade */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
}
.form-row {
  display: flex;
  justify-content: space-between;
  gap: 5px;
  margin-bottom: 15px;
  flex-wrap: wrap;
}

.form-group {
  width: 48%;
}

.form-group label {
  font-weight: bold;
  font-size: 14px;
  display: block;
  margin-bottom: 5px;
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

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Attendance List</h1>
    <form method="get" action="admin_dashboard.php" style="display: inline;">
      <input type="hidden" name="logout" value="true">
      <button type="submit" class="logout-btn">Logout</button>
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
$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
} elseif (isset($_GET['success'])) {
    $message = htmlspecialchars($_GET['success']);
} elseif (isset($_GET['edit']) && $_GET['edit'] === 'success') {
    $message = 'Attendance record updated successfully!';
}

if ($message) {
    echo '
    <div id="flash-message" style="background-color: #28a745; color: white; padding: 15px; text-align: center;">
        ' . $message . '
    </div>
    <script>
        setTimeout(function() {
            var msg = document.getElementById("flash-message");
            if (msg) {
                msg.style.display = "none";
            }
        }, 2000); // 2000 milliseconds = 2 seconds
    </script>
    ';
}
?>


<!--Add Attendance BUTTON -->
<div class="form-row">
        <div class="form-group">
            <div style="margin: 20px 16.5em 0; padding: 6px 12px; border-radius: 4px;">
              <button onclick="openAddAttendanceModal()" style="padding: 6px 12px; border-radius: 4px; background-color: #2a628f; color: white; border: none; cursor: pointer; height:2.5em; width:10em;">
                <i class="fa fa-plus"></i> Add Attendance
              </button>
            </div>
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: flex-end; margin: 20px 20px 10px 0;">
              <label for="employeeSearch" style="font-weight: 500; margin-right: 10px; padding-top:.5em;">Search:</label>
              <input type="text" id="employeeSearch" placeholder="Search employee name..." 
                    style="padding: 5px 10px; border-radius: 6px; border: 1px solid #ccc; width: 220px;height:1.8em; font-size: 14px; ">
            </div>
        </div>
</div>


<!-- Add Attendance Modal -->
<div id="addAttendanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center;">


  <div style="background:#fff; padding:30px; border-radius:12px; width:600px; position:relative; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin: 0;">Add Attendance</h2>
      <button onclick="closeAddAttendanceModal()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
    </div>

    <form method="post" action="add_attendance.php" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
      
      <!-- Date -->
      <div style="grid-column: span 2;">
        <label for="add_date"><strong>Date:</strong></label>
        <input type="date" id="add_date" name="date" required style="width:95%; padding:10px; border:1px solid #ccc; border-radius:6px;" />
      </div>

      <!-- Employee -->
      <div style="grid-column: span 2;">
        <label for="employee_id"><strong>Employee:</strong></label>
        <select name="employee_id" id="employee_id" required style="width:99%; padding:10px; border:1px solid #ccc; border-radius:6px;">
          <?php
          $empStmt = $pdo->query("SELECT employee_id, first_name, last_name FROM employees");
          while ($emp = $empStmt->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value="' . $emp['employee_id'] . '">' . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . '</option>';
          }
          ?>
        </select>
      </div>

      <!-- Time In -->
      <div>
        <label for="add_time_in"><strong>Time In:</strong></label>
        <input type="text" id="add_time_in" name="time_in" placeholder="HH:MM:SS AM/PM" style="width:90%; padding:10px; border:1px solid #ccc; border-radius:6px;" />
      </div> 

      <!-- Time Out -->
      <div>
        <label for="add_time_out"><strong>Time Out:</strong></label>
        <input type="text" id="add_time_out" name="time_out" placeholder="HH:MM:SS AM/PM" style="width:90%; padding:10px; border:1px solid #ccc; border-radius:6px;" />
      </div> </b>

      <!-- Status -->
      <div style="grid-column: span 2;">
        <label for="add_status"><strong>Status:</strong></label>
        </br>
        <select name="status" id="add_status" required style="width:50%; padding:10px; border:1px solid #ccc; border-radius:6px;">
          <option value="in">In</option>
          <option value="out">Out</option>
        </select>
      </div>

      <!-- Buttons -->
      <div style="grid-column: span 2; text-align: right; margin-top: 10px;">
        <button type="submit" style="background: #0d6efd; color: white; padding: 10px 20px; border: none; border-radius: 6px; margin-right: 10px; cursor: pointer;">Save</button>
        <button type="button" onclick="closeAddAttendanceModal()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
      </div>

    </form>
  </div>
</div>

<!--ADMIN EDIT Modal -->
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

<div class="content">
<table class="attendance-table">
  <thead>
    <tr>
      <th>Date</th>
      <th>Employee ID</th>
      <th>Name</th>
      <th>Time In</th>
      <th>Time Out</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
<?php if (!empty($attendanceData)): ?>
  <?php foreach ($attendanceData as $row): ?>
    <tr>
      <td><?= $row['formatted_date']; ?></td>
      <td><?= $row['employee_id']; ?></td>
      <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
      <td>
        <?= $row['time_in'] ? date("h:i:s A", strtotime($row['time_in'])) : ''; ?>
        <?php if (!empty($row['status'])): ?>
          <span class="status-badge <?= ($row['status'] === 'On Time') ? 'status-ontime' : 'status-late'; ?>">
            <?= $row['status']; ?>
          </span>
        <?php endif; ?>
      </td>
      <td><?= $row['time_out'] ? date("h:i:s A", strtotime($row['time_out'])) : ''; ?></td>
      <td class="actions">
        <a href="javascript:void(0);" onclick="openAttendanceEditModal(
            '<?= $row['id']; ?>',
            '<?= $row['formatted_date']; ?>',
            '<?= $row['employee_id']; ?>',
            '<?= $row['first_name'] . ' ' . $row['last_name']; ?>',
            '<?= $row['time_in']; ?>',
            '<?= $row['time_out']; ?>',
            '<?= $row['status']; ?>'
          )" class="edit-btn">Edit</a>
        <a href="delete_attendance.php?id=<?= $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this attendance record?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <!-- PHP fallback for no attendance data -->
  <tr>
    <td colspan="6" style="text-align: center; color: #888;">No attendance records available.</td>
  </tr>
<?php endif; ?>

<!-- Always present row for JS filtering -->
<tr id="noResultsMessage" style="display: none;">
  <td colspan="6" style="text-align: center; color: #888;">No result found.</td>
</tr>
</tbody>


</table>

<!-- Attendance Edit Modal -->
<div id="attendanceEditModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.6); z-index:2000; justify-content:center; align-items:center;">
  <div style="background:#fff; padding:20px; border-radius:10px; width:500px; position:relative;">
    <h3 style="text-align:center; margin:10px; padding-bottom:10px;">Edit Attendance</h3>
    <form id="attendanceEditForm" method="post" action="save_attendance.php">
      <input type="hidden" id="edit_attendance_id" name="id">

      <div class="form-row">
        <div class="form-group">
        <label>Date:</label>
        <input type="text" id="edit_date" name="date" readonly>
        </div>
        <div class="form-group">
        <label>Status:</label>
          <select name="status" id="edit_status" required>
            <option value="in">in</option>
            <option value="out">out</option>
            <option value="Unknown">Unknown</option>
          </select>
          </div>
      </div>

      <div class="form-row">
        <div class="form-group">
        <label>Employee ID:</label>
        <input type="text" id="edit_employee_id" name="employee_id" readonly>
        </div>
        <div class="form-group">
        <label>Name:</label>
        <input type="text" id="edit_employee_name" readonly>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
        <label>Time In:</label>
        <input type="text" name="time_in" id="edit_time_in"
        placeholder="Select time (HH:MM:SS AM/PM)">
        </div>
        <div class="form-group">
        <label>Time Out:</label>
        <input type="text" name="time_out" id="edit_time_out"
        placeholder="Select time (HH:MM:SS AM/PM)"/>
        </div>
  </div>

      <button type="submit" >Save Changes</button>
      <button type="button" onclick="closeAttendanceModal()">Cancel</button>
    </form>
  </div>
</div>

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
function openAttendanceEditModal(id, date, employeeId, name, timeIn, timeOut, status) {
  document.getElementById("edit_attendance_id").value = id;
  document.getElementById("edit_date").value = date;
  document.getElementById("edit_employee_id").value = employeeId;
  document.getElementById("edit_employee_name").value = name;

  // Format: "HH:MM:SS" → "HH:MM"
  function toTimeValue(timeStr) {
    return timeStr ? timeStr.slice(0, 5) : '';
  }

  document.getElementById("edit_time_in").value = toTimeValue(timeIn);
  document.getElementById("edit_time_out").value = toTimeValue(timeOut);

  document.getElementById("edit_status").value = status;
  document.getElementById("attendanceEditModal").style.display = "flex";
}

  function closeAttendanceModal() {
    document.getElementById("attendanceEditModal").style.display = "none";
  }
  flatpickr("#edit_time_in", {
  enableTime: true,
  noCalendar: true,
  enableSeconds: true,        // ✅ enables seconds
  dateFormat: "h:i:S K",      // ✅ includes seconds in AM/PM format
  time_24hr: false
});

flatpickr("#edit_time_out", {
  enableTime: true,
  noCalendar: true,
  enableSeconds: true,
  dateFormat: "h:i:S K",
  time_24hr: false
});
document.getElementById("employeeSearch").addEventListener("keyup", function () {
  const searchValue = this.value.toLowerCase();
  const rows = document.querySelectorAll(".attendance-table tbody tr");
  const noResultsRow = document.getElementById("noResultsMessage");

  let visibleCount = 0;

  rows.forEach(row => {
    if (row.id === "noResultsMessage") return;

    const nameCell = row.cells[2]; // Adjust index if needed
    const name = nameCell.textContent.toLowerCase();
    const match = name.includes(searchValue);

    row.style.display = match ? "" : "none";
    if (match) visibleCount++;
  });

  noResultsRow.style.display = (visibleCount === 0) ? "table-row" : "none";
});


function openAddAttendanceModal() {
  document.getElementById("addAttendanceModal").style.display = "flex";
}

function closeAddAttendanceModal() {
  document.getElementById("addAttendanceModal").style.display = "none";
}
flatpickr("#add_time_in", {
  enableTime: true,
  noCalendar: true,
  enableSeconds: true,
  dateFormat: "h:i:S K",
  time_24hr: false
});

flatpickr("#add_time_out", {
  enableTime: true,
  noCalendar: true,
  enableSeconds: true,
  dateFormat: "h:i:S K",
  time_24hr: false
});


</script>