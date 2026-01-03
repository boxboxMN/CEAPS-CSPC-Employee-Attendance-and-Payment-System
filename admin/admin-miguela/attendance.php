<?php
session_start();
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

$host = "localhost";
$dbname = "wage_warriors";
$dbuser = "root";
$dbpass = "";
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    $attendanceData = [];
    $seenIds = [];

    foreach ($attendanceDataRaw as $row) {
        if (!in_array($row['id'], $seenIds)) {
            $timeIn = strtotime($row['time_in']);
            $scheduledIn = strtotime($row['schedule_in']);
            $row['status'] = ($timeIn <= $scheduledIn) ? 'On Time' : 'Late';
            $attendanceData[] = $row;
            $seenIds[] = $row['id'];
        }
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    
    /* Table Styles */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: var(--white);
      box-shadow: var(--card-shadow);
      border-radius: 10px;
      overflow: hidden;
    }
    
    .data-table th,
    .data-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    
    .data-table th {
      background-color: var(--primary-color);
      color: var(--white);
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    
    .data-table tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    /* Status Badges */
    .status-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: lowercase;
      margin-left: 8px;
    }
    
    .status-ontime {
      background-color: var(--success-color);
      color: var(--white);
    }
    
    .status-late {
      background-color: var(--danger-color);
      color: var(--white);
    }
    
    /* Button Styles */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      border: none;
      outline: none;
      text-decoration: none;
    }
    
    .btn-sm {
      padding: 5px 10px;
      font-size: 0.8rem;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      color: var(--white);
    }
    
    .btn-primary:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
    }
    
    .btn-success {
      background-color: var(--success-color);
      color: var(--white);
    }
    
    .btn-success:hover {
      background-color: #27ae60;
      transform: translateY(-2px);
    }
    
    .btn-danger {
      background-color: var(--danger-color);
      color: var(--white);
    }
    
    .btn-danger:hover {
      background-color: #d1145a;
      transform: translateY(-2px);
    }
    
    /* Search and Add Button Container */
    .table-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 20px;
    }
    
    .search-container {
      display: flex;
      align-items: center;
    }
    
    .search-container label {
      margin-right: 10px;
      font-weight: 500;
    }
    
    .search-container input {
      padding: 8px 12px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      font-size: 0.9rem;
    }
    
    /* Modal Styles */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      opacity: 0;
      visibility: hidden;
      transition: var(--transition);
    }
    
    .modal.show {
      opacity: 1;
      visibility: visible;
    }
    
    .modal-content {
      background: var(--white);
      border-radius: 10px;
      width: 100%;
      max-width: 600px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      transform: translateY(-50px);
      transition: var(--transition);
    }
    
    .modal.show .modal-content {
      transform: translateY(0);
    }
    
    .modal-header {
      padding: 18px 25px;
      background: var(--primary-color);
      color: var(--white);
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0;
    }
    
    .close-btn {
      background: none;
      border: none;
      color: var(--white);
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0;
      line-height: 1;
    }
    
    .modal-body {
      padding: 25px;
      max-height: 70vh;
      overflow-y: auto;
    }
    
    .modal-footer {
      padding: 15px 25px;
      background: #f8f9fa;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    /* Form Styles */
    .form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 15px;
    }
    
    .form-group {
      flex: 1;
      min-width: 200px;
    }
    
    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--dark-color);
    }
    
    .form-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      font-size: 0.95rem;
      transition: var(--transition);
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    /* Message Styles */
    .alert-message {
      padding: 12px 20px;
      margin-bottom: 20px;
      border-radius: 6px;
      display: flex;
      align-items: center;
    }
    
    .alert-success {
      background-color: rgba(46, 204, 113, 0.15);
      color: #27ae60;
      border-left: 4px solid #2ecc71;
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
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
      }
      
      .form-group {
        width: 100%;
      }
      
      .table-controls {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      
      .data-table {
        display: block;
        overflow-x: auto;
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
    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li class="active"><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
    
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
          <h1>Attendance Management</h1>
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
      
      <?php if (isset($_GET['message'])): ?>
        <div class="alert-message alert-success">
          <i class="fas fa-check-circle"></i>
          <?= htmlspecialchars($_GET['message']) ?>
        </div>
      <?php endif; ?>
      
      <!-- Table Controls -->
      <div class="table-controls">
        <button onclick="openAddAttendanceModal()" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add Attendance
        </button>
        
        <div class="search-container">
          <label for="employeeSearch">Search:</label>
          <input type="text" id="employeeSearch" placeholder="Search employee name...">
        </div>
      </div>
      
      <div id="noResultsMessage" style="display: none; text-align: center; padding: 20px; background: var(--white); border-radius: 6px; box-shadow: var(--card-shadow);">
        No matching records found
      </div>
      
      <!-- Attendance Table -->
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Employee ID</th>
              <th>Name</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($attendanceData)): ?>
              <?php foreach ($attendanceData as $row): ?>
                <tr>
                  <td><?= $row['formatted_date']; ?></td>
                  <td><?= $row['employee_id']; ?></td>
                  <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
                  <td><?= $row['time_in'] ? date("h:i:s A", strtotime($row['time_in'])) : '-'; ?></td>
                  <td><?= $row['time_out'] ? date("h:i:s A", strtotime($row['time_out'])) : '-'; ?></td>
                  <td>
                    <?php if (!empty($row['status'])): ?>
                      <span class="status-badge <?= ($row['status'] === 'On Time') ? 'status-ontime' : 'status-late'; ?>">
                        <?= $row['status']; ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button onclick="openAttendanceEditModal(
                      '<?= $row['id']; ?>',
                      '<?= $row['formatted_date']; ?>',
                      '<?= $row['employee_id']; ?>',
                      '<?= $row['first_name'] . ' ' . $row['last_name']; ?>',
                      '<?= $row['time_in']; ?>',
                      '<?= $row['time_out']; ?>',
                      '<?= $row['status']; ?>'
                    )" class="btn btn-success btn-sm">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="delete_attendance.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this attendance record?');">
                      <i class="fas fa-trash-alt"></i> Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align: center;">No attendance records found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Add Attendance Modal -->
  <div id="addAttendanceModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Add Attendance Record</h3>
        <button class="close-btn" onclick="closeAddAttendanceModal()">&times;</button>
      </div>
      <form method="post" action="add_attendance.php">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Date</label>
              <input type="date" class="form-control" name="date" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Employee</label>
              <select class="form-control" name="employee_id" required>
                <?php
                $empStmt = $pdo->query("SELECT employee_id, first_name, last_name FROM employees");
                while ($emp = $empStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $emp['employee_id'] . '">' . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Time In</label>
              <input type="text" class="form-control" id="add_time_in" name="time_in" placeholder="HH:MM:SS AM/PM" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Time Out</label>
              <input type="text" class="form-control" id="add_time_out" name="time_out" placeholder="HH:MM:SS AM/PM">
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-control" name="status" required>
              <option value="in">In</option>
              <option value="out">Out</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" onclick="closeAddAttendanceModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Attendance</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Edit Attendance Modal -->
  <div id="attendanceEditModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Edit Attendance Record</h3>
        <button class="close-btn" onclick="closeAttendanceModal()">&times;</button>
      </div>
      <form id="attendanceEditForm" method="post" action="save_attendance.php">
        <input type="hidden" id="edit_attendance_id" name="id">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Date</label>
              <input type="text" class="form-control" id="edit_date" name="date" readonly>
            </div>
            
            <div class="form-group">
              <label class="form-label">Status</label>
              <select class="form-control" name="status" id="edit_status" required>
                <option value="in">In</option>
                <option value="out">Out</option>
                <option value="Unknown">Unknown</option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Employee ID</label>
              <input type="text" class="form-control" id="edit_employee_id" name="employee_id" readonly>
            </div>
            
            <div class="form-group">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" id="edit_employee_name" readonly>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Time In</label>
              <input type="text" class="form-control" name="time_in" id="edit_time_in" placeholder="Select time (HH:MM:SS AM/PM)" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Time Out</label>
              <input type="text" class="form-control" name="time_out" id="edit_time_out" placeholder="Select time (HH:MM:SS AM/PM)">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" onclick="closeAttendanceModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    // Initialize flatpickr for time inputs
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
    
    flatpickr("#edit_time_in", {
      enableTime: true,
      noCalendar: true,
      enableSeconds: true,
      dateFormat: "h:i:S K",
      time_24hr: false
    });
    
    flatpickr("#edit_time_out", {
      enableTime: true,
      noCalendar: true,
      enableSeconds: true,
      dateFormat: "h:i:S K",
      time_24hr: false
    });
    
    // Modal functions
    function openAddAttendanceModal() {
      document.getElementById("addAttendanceModal").classList.add("show");
    }
    
    function closeAddAttendanceModal() {
      document.getElementById("addAttendanceModal").classList.remove("show");
    }
    
    function openAttendanceEditModal(id, date, employeeId, name, timeIn, timeOut, status) {
      document.getElementById("edit_attendance_id").value = id;
      document.getElementById("edit_date").value = date;
      document.getElementById("edit_employee_id").value = employeeId;
      document.getElementById("edit_employee_name").value = name;
      
      // Format time values
      function toTimeValue(timeStr) {
        return timeStr ? timeStr.slice(0, 8) : '';
      }
      
      document.getElementById("edit_time_in").value = toTimeValue(timeIn);
      document.getElementById("edit_time_out").value = toTimeValue(timeOut);
      document.getElementById("edit_status").value = status.toLowerCase();
      
      document.getElementById("attendanceEditModal").classList.add("show");
    }
    
    function closeAttendanceModal() {
      document.getElementById("attendanceEditModal").classList.remove("show");
    }
    
    // Search functionality
    document.getElementById("employeeSearch").addEventListener("keyup", function() {
      const searchValue = this.value.toLowerCase();
      const rows = document.querySelectorAll(".data-table tbody tr");
      let visibleCount = 0;
      
      rows.forEach(row => {
        const nameCell = row.cells[2]; // Name column
        const name = nameCell.textContent.toLowerCase();
        const match = name.includes(searchValue);
        row.style.display = match ? "" : "none";
        if (match) visibleCount++;
      });
      
      // Show/hide no results message
      document.getElementById("noResultsMessage").style.display = 
        (visibleCount === 0 && searchValue.length > 0) ? "block" : "none";
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal')) {
        closeAddAttendanceModal();
        closeAttendanceModal();
      }
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === "Escape") {
        closeAddAttendanceModal();
        closeAttendanceModal();
      }
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