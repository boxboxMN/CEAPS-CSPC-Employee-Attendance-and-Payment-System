<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];

// Database connection
include 'db_conn.php';

// Initialize variables
$payroll_data = [];
$date_range = '';
$start_date = '';
$end_date = '';

// Check if date range is submitted
if (isset($_GET['date_range'])) {
    $date_range = $_GET['date_range'];
    $dates = explode(' - ', $date_range);
    if (count($dates) == 2) {
        $start_date = date('Y-m-d', strtotime($dates[0]));
        $end_date = date('Y-m-d', strtotime($dates[1]));
        
        // Fetch payroll data from database
        $query = "
            SELECT 
                CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                e.employee_id,
                SUM(TIMESTAMPDIFF(HOUR, a.time_in, IFNULL(a.time_out, NOW()))) * r.rate_per_hour AS gross,
                0 AS cash_advance,  -- Temporary placeholder since payroll_advances table doesn't exist
                SUM(TIMESTAMPDIFF(HOUR, a.time_in, IFNULL(a.time_out, NOW()))) * r.rate_per_hour AS net_pay
            FROM 
                employees e
            JOIN 
                attendance a ON e.employee_id = a.employee_id
            JOIN 
                roles r ON e.role_id = r.role_id
            WHERE 
                a.date BETWEEN ? AND ?
            GROUP BY 
                e.employee_id, e.first_name, e.last_name, r.rate_per_hour
            ORDER BY 
                employee_name
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $payroll_data = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?logout=You have been successfully logged out.");
    exit();
}

if (isset($_GET['generate_payslip']) && isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];
    $date_range = $_GET['date_range'];
    $dates = explode(' - ', $date_range);
    
    if (count($dates) == 2) {
        $start_date = date('Y-m-d', strtotime($dates[0]));
        $end_date = date('Y-m-d', strtotime($dates[1]));
        
        // Store data in session for payslip.php
        $_SESSION['payslip_data'] = [
            'employee_id' => $employee_id,
            'date_range' => $date_range,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        header("Location: payslip.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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
        
        .treeview-menu {
            padding-left: 20px;
            display: none;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .treeview.active .treeview-menu {
            display: block;
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
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        /* Calendar Controls */
        .calendar-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .calendar-controls i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }
        
        .calendar-controls input {
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.95rem;
            min-width: 250px;
        }
        
        .calendar-controls button {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }
        
        .calendar-controls .btn-success {
            background-color: #2ecc71;
            color: var(--white);
        }
        
        .calendar-controls .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        
        .calendar-controls .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .calendar-controls .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
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
        
        /* DataTables Customization */
        .dataTables_wrapper {
            padding: 0;
            margin-bottom: 15px;
        }
        
        .dataTables_filter {
            margin-bottom: 15px;
        }
        
        .dataTables_filter label {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .dataTables_filter input {
            margin-left: 10px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
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
        
        /* Error Message */
        .error-message {
            color: var(--danger-color);
            padding: 12px 20px;
            background-color: rgba(247, 37, 133, 0.1);
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-left: 4px solid var(--danger-color);
        }
        
        .error-message i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        /* Sort Icon */
        .sort-icon {
            font-size: 12px;
            margin-left: 5px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
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
            .calendar-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dataTables_wrapper {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dataTables_filter {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .dataTables_filter input {
                width: 100%;
                margin-left: 0;
                margin-top: 5px;
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

.sidebar-menu .treeview > a > .fa-angle-down {
    transition: transform 0.3s ease;
    margin-left: auto;
}

.sidebar-menu .treeview.active > a > .fa-angle-down {
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
          <h1>Payroll Management</h1>
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
      
      <?php if (isset($error)): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <!-- Payroll Card -->
      <div class="card">
        <div class="card-body">
          <!-- Calendar Controls -->
          <div class="calendar-controls">
            <i class="fas fa-calendar"></i>
            <input type="text" id="date-range" placeholder="Select date range" value="<?= htmlspecialchars($date_range) ?>">
            <button class="btn btn-success" id="process-payroll">
              <i class="fas fa-calculator"></i> Process Payroll
            </button>
          </div>
          
          <!-- DataTables Controls -->
          <div class="dataTables_wrapper">
            
          
          <!-- Payroll Table -->
          <div class="table-responsive">
            <table id="payroll-table" class="data-table">
              <thead>
                <tr>
                  <th>Employee Name <span class="sort-icon">⇅</span></th>
                  <th>Employee ID <span class="sort-icon">⇅</span></th>
                  <th>Gross Pay <span class="sort-icon">⇅</span></th>
                  <th>Cash Advance <span class="sort-icon">⇅</span></th>
                  <th>Net Pay <span class="sort-icon">⇅</span></th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($payroll_data)): ?>
                  <?php foreach ($payroll_data as $row): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['employee_name']) ?></td>
                      <td><?= htmlspecialchars($row['employee_id']) ?></td>
                      <td>₱<?= number_format($row['gross'], 2) ?></td>
                      <td>₱<?= number_format($row['cash_advance'], 2) ?></td>
                      <td>₱<?= number_format($row['net_pay'], 2) ?></td>
                      <td>
                      <a href="?generate_payslip=true&employee_id=<?= urlencode($row['employee_id']) ?>&date_range=<?= urlencode($date_range) ?>">
                          <i class="fas fa-file-invoice-dollar"></i> Payslip
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6">
                      <div class="empty-state">
                        <i class="fas fa-money-bill-wave"></i>
                        <h4>No Payroll Data Found</h4>
                        <p>Select a date range to view payroll records.</p>
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

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#date-range').daterangepicker({
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false
        });

        // Update input when dates are selected
        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            window.location.href = 'payroll.php?date_range=' + 
                picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
        });

        // Initialize DataTable
        var table = $('#payroll-table').DataTable({
            responsive: true,
            paging: false, // Disable pagination
            lengthChange: false, // Disable length change
            searching: true,
            ordering: true,
            info: false, // Disable info display
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search payroll..."
            }
        });
        
        // Search functionality
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });
        
        // Process Payroll button
        $('#process-payroll').click(function() {
            if ($('#date-range').val()) {
                // Redirect to process payroll page with date range
                window.location.href = 'process_payroll.php?date_range=' + encodeURIComponent($('#date-range').val());
            } else {
                alert('Please select a date range first');
            }
        });
    });
  </script>
</body>
</html>