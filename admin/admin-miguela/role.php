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
    
    // First, get the role name for the confirmation message
    $stmt = $conn->prepare("SELECT role FROM roles WHERE role_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = $result->fetch_assoc()['role'];
    $stmt->close();
    
    // Delete the role
    $stmt = $conn->prepare("DELETE FROM roles WHERE role_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Role '$role' has been deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting role: " . $conn->error;
    }
    $stmt->close();
    
    header("Location: role.php");
    exit();
}

// Query to fetch roles
$sql = "SELECT * FROM roles";
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
  <title>Role Management</title>
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
    .dataTables_wrapper {
      padding: 0 25px;
      margin-bottom: 15px;
    }
    
    .dataTables_length,
    .dataTables_filter {
      margin-bottom: 15px;
    }
    
    .dataTables_length label,
    .dataTables_filter label {
      display: flex;
      align-items: center;
      font-size: 0.9rem;
    }
    
    .dataTables_length select {
      margin: 0 8px;
      padding: 6px;
      border-radius: 6px;
      border: 1px solid var(--border-color);
    }
    
    .dataTables_filter input {
      margin-left: 10px;
      padding: 6px 12px;
      border-radius: 6px;
      border: 1px solid var(--border-color);
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
  </style>
</head>
<body>
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
            <a href="#"><i class="fas fa-users"></i> Employees <i class="fas fa-angle-right float-right"></i></a>
            <ul class="treeview-menu">
                <li><a href="employee_list.php"><i class="fas fa-list"></i> Employee List</a></li>
                <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
            </ul>
        </li>
        
        <li class="active"><a href="role.php"><i class="fas fa-user-tie"></i> Roles</a></li>
        <li><a href="payroll.php"><i class="fas fa-money-bill-wave"></i> Payroll</a></li>
      </ul>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <header class="main-header">
        <div class="page-title">
          <h1>Role Management</h1>
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
      <?php if (isset($_SESSION['message'])): ?>
        <div class="alert <?= strpos($_SESSION['message'], 'success') !== false ? 'alert-success' : 'alert-danger' ?>">
          <i class="fas <?= strpos($_SESSION['message'], 'success') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
      <?php endif; ?>
      
      <!-- Roles Card -->
      <div class="card">
        
        <div class="card-body">
          
          <!-- Roles Table -->
          <div class="table-responsive">
            <table id="roles-table" class="data-table">
              <thead>
                <tr>
                  <th>Role Title</th>
                  <th>Rate per Hour</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['role']) ?></td>
                      <td>$<?= number_format($row['rate_per_hour'], 2) ?></td>
                      <td>
                        <button class="btn btn-success btn-sm" onclick="openEditModal(
                          <?= $row['role_id'] ?>, 
                          '<?= addslashes($row['role']) ?>', 
                          <?= $row['rate_per_hour'] ?>
                        )">
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(
                          <?= $row['role_id'] ?>, 
                          '<?= addslashes($row['role']) ?>'
                        )">
                          <i class="fas fa-trash-alt"></i> Delete
                        </button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3">
                      <div class="empty-state">
                        <i class="fas fa-user-tie"></i>
                        <h4>No Roles Found</h4>
                        <p>There are currently no roles defined in the system.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Edit Role Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Edit Role</h3>
        <button class="close-btn" onclick="closeEditModal()">&times;</button>
      </div>
      <form id="editForm" method="POST" action="redit.php">
        <div class="modal-body">
          <input type="hidden" name="role_id" id="role_id">
          
          <div class="form-group">
            <label class="form-label">Role Title:</label>
            <input type="text" class="form-control" name="role" id="role_title" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Rate per Hour:</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" class="form-control" name="rate_per_hour" id="rate_per_hour" step="0.01" min="0" required>
            </div>
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
  
  <!-- Delete Confirmation Modal -->
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
        <p class="message" id="deleteMessage">Are you sure you want to delete this role?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" onclick="deleteRole()">
          <i class="fas fa-trash-alt"></i> Delete
        </button>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script>
   $(document).ready(function() {
    // Initialize DataTable with custom layout
    var table = $('#roles-table').DataTable({
        "pagingType": "simple_numbers",
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search roles...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "previous": '<i class="fas fa-chevron-left"></i>',
                "next": '<i class="fas fa-chevron-right"></i>'
            }
        },
        "initComplete": function() {
            // Add custom classes to pagination buttons
            $('.paginate_button.previous').addClass('btn btn-sm');
            $('.paginate_button.next').addClass('btn btn-sm');
        }
    });
        // Update custom info
        function updateCustomInfo() {
            var info = table.page.info();
            $('#custom-info').text(
                `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`
            );
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
        
        // Update info when page changes
        table.on('draw', function() {
            updateCustomInfo();
        });
        
        // Search functionality
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
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
        window.openEditModal = function(id, role, rate) {
            $('#role_id').val(id);
            $('#role_title').val(role);
            $('#rate_per_hour').val(rate);
            $('#editModal').addClass('show');
        };
        
        window.closeEditModal = function() {
            $('#editModal').removeClass('show');
        };
        
        // Delete confirmation functions
        let roleToDelete = null;
        let roleNameToDelete = '';
        
        window.confirmDelete = function(id, roleName) {
            roleToDelete = id;
            roleNameToDelete = roleName;
            $('#deleteMessage').text(`Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`);
            $('#deleteModal').addClass('show');
        };
        
        window.closeDeleteModal = function() {
            roleToDelete = null;
            roleNameToDelete = '';
            $('#deleteModal').removeClass('show');
        };
        
        window.deleteRole = function() {
            if (roleToDelete) {
                window.location.href = `role.php?delete_id=${roleToDelete}`;
            }
        };
        
        // Close modals when clicking outside
        $(document).click(function(event) {
            if ($(event.target).hasClass('modal')) {
                closeEditModal();
                closeDeleteModal();
            }
        });
        
        // Close modals with Escape key
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                closeEditModal();
                closeDeleteModal();
            }
        });
    });
  </script>
</body>
</html>