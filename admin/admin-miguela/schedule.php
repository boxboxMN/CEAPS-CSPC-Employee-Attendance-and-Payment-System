<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];
include '../db_conn.php'; // Include database connection

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?logout=You have been successfully logged out.");
    exit();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get the employee details for the confirmation message
    $stmt = $conn->prepare("SELECT e.first_name, e.last_name FROM schedule s JOIN employees e ON s.employee_id = e.employee_id WHERE s.id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['first_name'] . ' ' . $row['last_name'];
    $stmt->close();
    
    // Delete the schedule
    $stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Schedule for '$name' has been deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting schedule: " . $conn->error;
    }
    $stmt->close();
    
    header("Location: schedule.php");
    exit();
}

// Handle edit action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    
    // Convert time format to 24-hour for database storage
    $time_in_24 = date("H:i:s", strtotime($time_in));
    $time_out_24 = date("H:i:s", strtotime($time_out));
    
    // Check if schedule exists for this employee
    $check_stmt = $conn->prepare("SELECT id FROM schedule WHERE employee_id = ?");
    $check_stmt->bind_param("s", $employee_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing schedule
        $update_stmt = $conn->prepare("UPDATE schedule SET time_in = ?, time_out = ? WHERE employee_id = ?");
        $update_stmt->bind_param("sss", $time_in_24, $time_out_24, $employee_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Schedule updated successfully!";
        } else {
            $_SESSION['message'] = "Error updating schedule: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        // Insert new schedule
        $insert_stmt = $conn->prepare("INSERT INTO schedule (employee_id, time_in, time_out) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $employee_id, $time_in_24, $time_out_24);
        
        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "Schedule created successfully!";
        } else {
            $_SESSION['message'] = "Error creating schedule: " . $conn->error;
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    header("Location: schedule.php");
    exit();
}

// Query to fetch schedules
$sql = "SELECT s.id as schedule_id, e.*, r.role, s.time_in, s.time_out 
        FROM employees e 
        LEFT JOIN schedule s ON e.employee_id = s.employee_id 
        LEFT JOIN roles r ON e.role_id = r.role_id 
        ORDER BY e.first_name";
$result = $conn->query($sql);

// Check if the query was successful
if ($result === false) {
    die("Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
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
  justify-content: center; /* Center the entire row horizontally */
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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

.user-info {
  text-align: left; /* Keep text aligned left within its container */
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
    
    /* Table Styles */
    .table-responsive {
      overflow-x: auto;
    }
    
    .data-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .data-table thead th {
      background-color: var(--primary-color);
      color: var(--white);
      padding: 12px 15px;
      font-weight: 500;
      text-align: left;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    
    .data-table tbody td {
      padding: 12px 15px;
      border-bottom: 1px solid var(--border-color);
      vertical-align: middle;
    }
    
    .data-table tbody tr:last-child td {
      border-bottom: none;
    }
    
    .data-table tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    /* Button Styles */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      border: none;
      outline: none;
    }
    
    .btn-sm {
      padding: 6px 12px;
      font-size: 0.8rem;
    }
    
    .btn i {
      margin-right: 6px;
      font-size: 0.9em;
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
      background-color: #2ecc71;
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
    
    .btn-warning {
      background-color: var(--warning-color);
      color: var(--white);
    }
    
    .btn-warning:hover {
      background-color: #e67e22;
      transform: translateY(-2px);
    }
    
    /* Alert Styles */
    .alert {
      padding: 12px 20px;
      border-radius: 6px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }
    
    .alert i {
      margin-right: 10px;
      font-size: 1.2rem;
    }
    
    .alert-success {
      background-color: rgba(46, 204, 113, 0.15);
      color: #27ae60;
      border-left: 4px solid #2ecc71;
    }
    
    .alert-danger {
      background-color: rgba(231, 76, 60, 0.15);
      color: #c0392b;
      border-left: 4px solid var(--danger-color);
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
    }
    
    .empty-state i {
      font-size: 3.5rem;
      color: #e0e0e0;
      margin-bottom: 15px;
    }
    
    .empty-state h4 {
      font-size: 1.2rem;
      color: var(--gray-color);
      margin-bottom: 10px;
    }
    
    .empty-state p {
      color: #adb5bd;
      font-size: 0.95rem;
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
      max-width: 500px;
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
    .form-group {
      margin-bottom: 20px;
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
    
    /* Confirmation Modal */
    .confirmation-modal .modal-body {
      text-align: center;
      padding: 30px;
    }
    
    .confirmation-modal .icon {
      font-size: 3.5rem;
      color: var(--danger-color);
      margin-bottom: 15px;
    }
    
    .confirmation-modal .message {
      font-size: 1.1rem;
      margin-bottom: 20px;
      line-height: 1.6;
    }
    
    /* DataTables Customization */
    /* Update the DataTables length control styles */
.dataTables_length {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
}

.dataTables_length label {
  display: flex;
  align-items: center;
  margin-bottom: 0;
  gap: 8px;
}

.dataTables_length select {
  margin: 0;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  height: 34px;
}

/* Keep the existing filter styles but ensure consistency */
.dataTables_filter {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
}

.dataTables_filter label {
  display: flex;
  align-items: center;
  margin-bottom: 0;
  gap: 8px;
}

.dataTables_filter input {
  margin-left: 0;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  height: 34px;
}
    /* Pagination */
    .pagination-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 25px;
      border-top: 1px solid var(--border-color);
    }
    
    .pagination-info {
      font-size: 0.9rem;
      color: var(--gray-color);
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
      
      .menu-toggle {
        display: block;
      }
    }
    
    @media (max-width: 768px) {
      .card-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .header-actions {
        margin-top: 10px;
      }
      
      .dataTables_wrapper {
        flex-direction: column;
      }
      
      .dataTables_length,
      .dataTables_filter {
        width: 100%;
        margin-bottom: 10px;
      }
    }

    .time-input-group {
      display: flex;
      gap: 10px;
    }
    
    .time-input {
      flex: 1;
    }
    
    /* Make the modal a bit wider for time inputs */
    .modal-content {
      max-width: 600px;
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
        <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        
        <li class="treeview">
          <a href="#"><i class="fas fa-users"></i> Employees <i class="fas fa-angle-down float-right"></i></a>
          <ul class="treeview-menu">
            <li><a href="employee_list.php"><i class="fas fa-list"></i> Employee List</a></li>
            <li class="active"><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
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
          <h1>Schedule Management</h1>
        </div>
        <div class="header-actions">
          <form method="get" action="schedule.php">
            <input type="hidden" name="logout" value="true">
            <button type="submit" class="btn btn-logout">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </div>
      </header>
      
      <!-- Alert Messages -->
      <?php if (isset($_SESSION['message'])): ?>
        <div class="alert <?= strpos($_SESSION['message'], 'success') !== false ? 'alert-success' : 'alert-danger' ?>">
          <i class="fas <?= strpos($_SESSION['message'], 'success') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
      <?php endif; ?>
      
      <!-- Schedule Card -->
      <div class="card">
        <div class="card-body">
          <!-- DataTables Controls -->
          <div class="dataTables_wrapper">
            <div class="dataTables_length">
              <label>
                Show 
                <select name="schedule-table_length" aria-controls="schedule-table" class="form-control">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select> 
                entries
              </label>
            </div>
            <div class="dataTables_filter">
              <label>
                Search:
                <input type="search" id="searchInput" placeholder="Search schedules...">
              </label>
            </div>
          </div>
          
          <!-- Schedule Table -->
          <div class="table-responsive">
            <table id="schedule-table" class="data-table">
              <thead>
                <tr>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Role</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $fullName = $row['first_name'] . " " . $row['last_name'];
                        $role = $row['role'];
                        $timeIn = $row['time_in'] ?? '';
                        $timeOut = $row['time_out'] ?? '';
                        
                        $formattedTimeIn = $timeIn ? date("h:i A", strtotime($timeIn)) : 'N/A';
                        $formattedTimeOut = $timeOut ? date("h:i A", strtotime($timeOut)) : 'N/A';
                        
                        echo "<tr>
                                <td>{$row['employee_id']}</td>
                                <td>{$fullName}</td>
                                <td>{$role}</td>
                                <td>{$formattedTimeIn}</td>
                                <td>{$formattedTimeOut}</td>
                                <td>
                                  <button class='btn btn-success btn-sm' onclick=\"openEditModal(
                                    '{$row['employee_id']}',
                                    '{$row['first_name']}',
                                    '{$row['last_name']}',
                                    '{$role}',
                                    '{$formattedTimeIn}',
                                    '{$formattedTimeOut}'
                                  )\">
                                    <i class='fas fa-edit'></i> Edit
                                  </button>
                                  <button class='btn btn-danger btn-sm' onclick=\"showDeleteModal('{$row['schedule_id']}', '{$row['employee_id']}')\">
                                    <i class='fas fa-trash-alt'></i> Delete
                                  </button>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr>
                            <td colspan='6'>
                              <div class='empty-state'>
                                <i class='fas fa-calendar-times'></i>
                                <h4>No Schedules Found</h4>
                                <p>There are currently no employee schedules in the system.</p>
                              </div>
                            </td>
                          </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="pagination-controls">
            <div class="pagination-info" id="custom-info">
              Showing 0 to 0 of 0 entries
            </div>
            <div>
              <button id="prev-page" class="btn btn-primary">
                <i class="fas fa-chevron-left"></i> Previous
              </button>
              <button id="next-page" class="btn btn-primary">
                Next <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Edit Schedule</h3>
        <button class="close-btn" onclick="closeModal()">&times;</button>
      </div>
      <form id="editForm" method="post" action="schedule.php">
        <div class="modal-body">
          <input type="hidden" id="edit_employee_id" name="employee_id">
          
          <div class="form-group">
            <label class="form-label">First Name:</label>
            <input type="text" class="form-control" name="first_name" id="edit_first_name" required readonly>
          </div>
          
          <div class="form-group">
            <label class="form-label">Last Name:</label>
            <input type="text" class="form-control" name="last_name" id="edit_last_name" required readonly>
          </div>
          
          <div class="form-group">
            <label class="form-label">Role:</label>
            <input type="text" class="form-control" name="role" id="edit_role" required readonly>
          </div>
          
          <div class="form-group">
            <label class="form-label">Time In:</label>
            <input type="text" class="form-control time-input" name="time_in" id="edit_time_in" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Time Out:</label>
            <input type="text" class="form-control time-input" name="time_out" id="edit_time_out" required>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" onclick="closeModal()">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Modal -->
  <div id="deleteModal" class="modal confirmation-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Confirm Deletion</h3>
        <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div class="icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="message">Are you sure you want to delete the schedule for <strong>Employee ID: <span id="employeeIdToDelete"></span></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
          <i class="fas fa-times"></i> Cancel
        </button>
        <a id="deleteConfirmBtn" href="#" class="btn btn-danger">
          <i class="fas fa-trash-alt"></i> Delete
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    $(document).ready(function() {
  // Check and destroy existing DataTable if needed
  if ($.fn.DataTable.isDataTable('#schedule-table')) {
    $('#schedule-table').DataTable().destroy();
  }

  // Initialize DataTable
  var table = $('#schedule-table').DataTable({
    "paging": true,
    "pageLength": 10,
    "lengthChange": false,
    "searching": true,
    "info": false,
    "dom": '<"top"l>rt<"bottom"ip>',
    "pagingType": "simple"
  });

  // Hide default pagination
  $('.dataTables_paginate').hide();

  // Initialize time pickers
  flatpickr("#edit_time_in", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K",
    time_24hr: false
  });

  flatpickr("#edit_time_out", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K",
    time_24hr: false
  });
  
  // Update custom info
  function updateCustomInfo() {
    var info = table.page.info();
    var start = info.recordsTotal === 0 ? 0 : info.start + 1;
    var end = info.end;
    var total = info.recordsTotal;
    
    $('#custom-info').text(`Showing ${start} to ${end} of ${total} entries`);
    
    // Disable/enable pagination buttons
    $('#prev-page').prop('disabled', info.page === 0);
    $('#next-page').prop('disabled', info.page === info.pages - 1);
  }
  
  // Initial info
  updateCustomInfo();
  
  // Pagination buttons
  $('#next-page').click(function() {
    table.page('next').draw('page');
    updateCustomInfo();
  });
  
  $('#prev-page').click(function() {
    table.page('previous').draw('page');
    updateCustomInfo();
  });
  
  // Show entries dropdown
  $('select[name="schedule-table_length"]').on('change', function() {
    table.page.len(this.value).draw();
    updateCustomInfo();
  });
  
  // Search functionality
  $('#searchInput').on('keyup', function() {
    table.search(this.value).draw();
  });
  
  // Table redraw event
  table.on('draw', function() {
    updateCustomInfo();
  });
});

// Modal functions remain unchanged
function openEditModal(id, firstName, lastName, role, timeIn = 'N/A', timeOut = 'N/A') {
  document.getElementById("edit_employee_id").value = id;
  document.getElementById("edit_first_name").value = firstName;
  document.getElementById("edit_last_name").value = lastName;
  document.getElementById("edit_role").value = role;
  document.getElementById("edit_time_in").value = timeIn === 'N/A' ? '' : timeIn;
  document.getElementById("edit_time_out").value = timeOut === 'N/A' ? '' : timeOut;
  document.getElementById("editModal").classList.add("show");
}

function closeModal() {
  document.getElementById("editModal").classList.remove("show");
}

function showDeleteModal(scheduleId, employeeId) {
  document.getElementById('employeeIdToDelete').textContent = employeeId;
  document.getElementById('deleteConfirmBtn').href = `schedule.php?delete_id=${scheduleId}`;
  document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('show');
}

// Close modals when clicking outside
$(document).click(function(event) {
  if ($(event.target).hasClass('modal')) {
    closeModal();
    closeDeleteModal();
  }
});

// Close modals with Escape key
$(document).keyup(function(e) {
  if (e.key === "Escape") {
    closeModal();
    closeDeleteModal();
  }
});
  </script>
</body>
</html>