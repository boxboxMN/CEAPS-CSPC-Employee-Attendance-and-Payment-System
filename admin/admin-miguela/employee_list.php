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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
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

    .sidebar-menu .treeview.active .treeview-menu {
        display: block;
    }

    .sidebar-menu .treeview > a > .float-right {
        transition: transform 0.3s ease;
        margin-left: auto;
    }

    .sidebar-menu .treeview.active > a > .float-right {
        transform: rotate(90deg);
    }

    /* Form Row Styles */
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    /* Password Toggle */
    .password-toggle {
      position: relative;
    }

    .password-toggle .toggle-icon {
      position: absolute;
      right: 10px;
      top: 35px;
      cursor: pointer;
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
    }
    
    @media (max-width: 768px) {
      .card-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .header-actions {
        margin-top: 10px;
      }
      
      .form-row {
        flex-direction: column;
        gap: 0;
      }
    }

    /* Enhanced Pagination Controls */
    .dataTables_wrapper .dataTables_paginate {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 25px;
        border-top: 1px solid var(--border-color);
    }

    .dataTables_wrapper .dataTables_info {
        padding: 15px 25px;
        font-size: 0.9rem;
        color: var(--gray-color);
        border-top: 1px solid var(--border-color);
    }

    .dataTables_wrapper .paginate_button {
        padding: 6px 12px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--dark-color);
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
    }

    .dataTables_wrapper .paginate_button:hover {
        background-color: var(--primary-light);
        color: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateY(-1px);
    }

    .dataTables_wrapper .paginate_button.current {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .dataTables_wrapper .paginate_button.disabled,
    .dataTables_wrapper .paginate_button.disabled:hover {
        color: #ccc;
        background-color: transparent;
        border-color: var(--border-color);
        cursor: not-allowed;
        transform: none;
    }

    .dataTables_wrapper .paginate_button.previous i,
    .dataTables_wrapper .paginate_button.next i {
        font-size: 0.9em;
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
        
        <li class="treeview active">
          <a href="#"><i class="fas fa-users"></i> Employees <i class="fas fa-angle-right float-right"></i></a>
          <ul class="treeview-menu">
            <li class="active"><a href="employee_list.php"><i class="fas fa-list"></i> Employee List</a></li>
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
          <h1>Employee Management</h1>
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
      
      <!-- Alert Messages -->
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?= htmlspecialchars($_GET['success']) ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($_GET['error']) ?>
        </div>
      <?php endif; ?>
      
      <!-- Employees Card -->
      <div class="card">
      
        
        <div class="card-body">
          <!-- Employees Table -->
          <div class="table-responsive">
            <table id="employees-table" class="data-table">
              <thead>
                <tr>
                  <th>Employee ID</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Role</th>
                  <th>Department</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // SQL query with a JOIN to get the role name
                $query = "SELECT employees.*, roles.role FROM employees 
                      LEFT JOIN roles ON employees.role_id = roles.role_id 
                      ORDER BY employees.employee_id DESC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    // Default to 'N/A' if no role is found
                    $role = isset($row['role']) ? $row['role'] : 'N/A';
                    echo "<tr>
                      <td>{$row['employee_id']}</td>
                      <td>{$row['first_name']}</td>
                      <td>{$row['last_name']}</td>
                      <td>{$role}</td>
                      <td>{$row['department']}</td>
                      <td>
                        <button class=\"btn btn-success btn-sm\" onclick=\"openEditModal(
                          '{$row['employee_id']}', 
                          '{$row['first_name']}', 
                          '{$row['last_name']}', 
                          '{$row['address']}', 
                          '{$row['contact_number']}', 
                          '{$row['role_id']}', 
                          '{$row['department']}', 
                          '{$row['password']}'
                        )\">
                          <i class=\"fas fa-edit\"></i> Edit
                        </button>
                        <a href=\"elist_delete.php?id={$row['employee_id']}\" class=\"btn btn-danger btn-sm\" onclick=\"return confirm('Are you sure you want to delete this employee?');\">
                          <i class=\"fas fa-trash-alt\"></i> Delete
                        </a>
                      </td>
                    </tr>";
                  }
                } else {
                  echo "<tr>
                    <td colspan=\"6\">
                      <div class=\"empty-state\">
                        <i class=\"fas fa-user-times\"></i>
                        <h4>No Employees Found</h4>
                        <p>There are currently no employees in the system.</p>
                      </div>
                    </td>
                  </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Edit Employee Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Edit Employee</h3>
        <button class="close-btn" onclick="closeEditModal()">&times;</button>
      </div>
      <form id="editForm" method="POST" action="elist_edit.php">
        <div class="modal-body">
          <input type="hidden" name="employee_id" id="employee_id">
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name:</label>
              <input type="text" class="form-control" name="first_name" id="first_name" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Last Name:</label>
              <input type="text" class="form-control" name="last_name" id="last_name" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Address:</label>
              <input type="text" class="form-control" name="address" id="address" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Contact Number:</label>
              <input type="text" class="form-control" name="contact_number" id="contact_number" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Role:</label>
              <select class="form-control" name="role_id" id="role_id" required>
                <option value="">Select Role</option>
                <?php
                  $roleQuery = "SELECT role_id, role FROM roles";
                  $roleResult = $conn->query($roleQuery);
                  while ($roleRow = $roleResult->fetch_assoc()) {
                    echo "<option value='{$roleRow['role_id']}'>{$roleRow['role']}</option>";
                  }
                ?>
              </select>
            </div>
            
            <div class="form-group">
              <label class="form-label">Department:</label>
              <select class="form-control" name="department" id="department" required>
                <option value="IT">IT</option>
              </select>
            </div>
          </div>
          
          <div class="form-group password-toggle">
            <label class="form-label">Password:</label>
            <input type="password" class="form-control" name="password" id="password" required>
            <i class="fas fa-eye toggle-icon" id="toggleEditPassword"></i>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" onclick="closeEditModal()">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Add Employee Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Add New Employee</h3>
        <button class="close-btn" onclick="closeAddModal()">&times;</button>
      </div>
      <form method="POST" action="elist_add.php">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Employee ID:</label>
            <input type="text" class="form-control" value="Will be auto-generated" readonly>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name:</label>
              <input type="text" class="form-control" name="first_name" id="add_first_name" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Last Name:</label>
              <input type="text" class="form-control" name="last_name" id="add_last_name" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Address:</label>
              <input type="text" class="form-control" name="address" id="add_address" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Contact Number:</label>
              <input type="text" class="form-control" name="contact_number" id="add_contact_number" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Role:</label>
              <select class="form-control" name="role_id" id="add_role_id" required>
                <option value="">Select Role</option>
                <?php
                  $roleQuery = "SELECT role_id, role FROM roles";
                  $roleResult = $conn->query($roleQuery);
                  while ($roleRow = $roleResult->fetch_assoc()) {
                    echo "<option value='{$roleRow['role_id']}'>{$roleRow['role']}</option>";
                  }
                ?>
              </select>
            </div>
            
            <div class="form-group">
              <label class="form-label">Department:</label>
              <select class="form-control" name="department" id="add_department" required>
                <option value="IT">IT</option>
              </select>
            </div>
          </div>
          
          <div class="form-group password-toggle">
            <label class="form-label">Password:</label>
            <input type="password" class="form-control" name="password" id="add_password" required>
            <i class="fas fa-eye toggle-icon" id="togglePassword"></i>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" onclick="closeAddModal()">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Add Employee
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      // Initialize DataTable
      $('#employees-table').DataTable({
        "pagingType": "simple_numbers",
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search employees...",
          "lengthMenu": "Show _MENU_ entries",
          "info": "Showing _START_ to _END_ of _TOTAL_ entries",
          "infoEmpty": "Showing 0 to 0 of 0 entries",
          "infoFiltered": "(filtered from _MAX_ total entries)",
          "paginate": {
            "previous": '<i class="fas fa-chevron-left"></i>',
            "next": '<i class="fas fa-chevron-right"></i>'
          }
        }
      });
      
      // Treeview toggle functionality
      $('.treeview > a').on('click', function(e) {
        e.preventDefault();
        var parent = $(this).parent();
        var icon = $(this).find('.float-right');
        
        // Toggle active class
        parent.toggleClass('active');
        
        // Rotate the icon
        if (parent.hasClass('active')) {
          icon.css('transform', 'rotate(90deg)');
        } else {
          icon.css('transform', 'rotate(0deg)');
        }
        
        // Toggle the submenu
        parent.find('> .treeview-menu').slideToggle(200);
        
        // Close other open treeviews
        $('.treeview').not(parent).removeClass('active')
          .find('> .treeview-menu').slideUp(200);
        $('.treeview').not(parent).find('> a > .float-right').css('transform', 'rotate(0deg)');
      });
      
      // Edit modal functions
      window.openEditModal = function(id, firstName, lastName, address, contactNumber, roleId, department, password) {
        $('#employee_id').val(id);
        $('#first_name').val(firstName);
        $('#last_name').val(lastName);
        $('#address').val(address);
        $('#contact_number').val(contactNumber);
        $('#role_id').val(roleId);
        $('#department').val(department);
        $('#password').val(password);
        $('#editModal').addClass('show');
      };
      
      window.closeEditModal = function() {
        $('#editModal').removeClass('show');
      };
      
      // Add modal functions
      window.openAddModal = function() {
        $('#addModal').addClass('show');
      };
      
      window.closeAddModal = function() {
        $('#addModal').removeClass('show');
      };
      
      // Password toggle functionality
      $('#togglePassword').on('click', function() {
        const passwordInput = $('#add_password');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
      });
      
      $('#toggleEditPassword').on('click', function() {
        const passwordInput = $('#password');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
      });
      
      // Close modals when clicking outside
      $(document).click(function(event) {
        if ($(event.target).hasClass('modal')) {
          closeEditModal();
          closeAddModal();
        }
      });
      
      // Close modals with Escape key
      $(document).keyup(function(e) {
        if (e.key === "Escape") {
          closeEditModal();
          closeAddModal();
        }
      });
    });
  </script>
</body>
</html>