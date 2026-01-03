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
      background-color: #d9534f;
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
      width: 75%;
      margin-left: 18em;
      margin-top: 30px;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
    }
    .employee-table thead {
      background-color: #2a628f;
      color: white;
    }
    .employee-table th, .employee-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .employee-table tr:hover {
      background-color: #f1f1f1;
    }
    .employee-table td a {
      color:rgb(255, 255, 255);
      text-decoration: none;
      
    }

    .form-row {
  display: flex;
  justify-content: space-between;
  gap: 15px;
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

input, select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

.modal-actions {
  justify-content: flex-end;
  gap: 10px;
}

.cancel-btn, .save-btn {
  padding: 10px 20px;
  border: none;
  font-size: 14px;
  cursor: pointer;
  border-radius: 5px;
}

.cancel-btn {
  background-color: #ccc;
}

.save-btn {
  background-color: #2a628f;
  color: white;
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

  </style>
</head>
<body>
<header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Employee List</h1>
    <form method="get" action="admin_dashboard.php" style="display: inline;">
      <input type="hidden" name="logout" value="true">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</header>

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
        <ul class="treeview-menu">
          <li><a href="employee_list.php">Employee list</a></li>
          <li><a href="schedule.php">Schedules</a></li>
        </ul>
      </li>

      <li><a href="role.php"><i class="fa fa-suitcase"></i> Role</a></li>
      <li><a href="payroll.php"><i class="fa fa-money"></i> Payroll</a></li>
    </ul>
  </section>
</aside>

<?php if (isset($_GET['success'])): ?>
    <div style="margin-left: 18em; color: green; padding: 10px;">
      <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div style="margin-left: 18em; color: red; padding: 10px;">
      <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<table class="employee-table">
  <thead>
    <tr>
      <th>Employee ID</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Role</th>
      <th>Department</th>
      <th>Action</th>
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
            <a href=\"javascript:void(0);\" class=\"edit-btn\" onclick=\"openEditModal('{$row['employee_id']}', '{$row['first_name']}', '{$row['last_name']}', '{$row['address']}', '{$row['contact_number']}', '{$row['role_id']}', '{$row['department']}', '{$row['password']}')\">Edit</a>
        
            <a href=\"elist_delete.php?id={$row['employee_id']}\" class=\"delete-btn\" onclick=\"return confirm('Are you sure you want to delete this employee?');\">Delete</a>
          </td>
        </tr>";
      }
    } else {
      echo "<tr><td colspan='6'>No employees found.</td></tr>";
    }
    
    ?>
  </tbody>
</table>

<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.6); z-index:2000; justify-content:center; align-items:center;">
  <div style="background:#fff; padding:30px; border-radius:8px; position:relative; margin:auto; width:50%; max-width:650px;">
    <span onclick="closeModal()" style="position:absolute; top:10px; right:10px; font-size:24px; cursor:pointer;">&times;</span>
    <h3>Edit Employee</h3>
    
    <form id="editForm" method="POST" action="elist_edit.php">
      <input type="hidden" name="employee_id" id="employee_id">

      <div class="form-row">
        <div class="form-group">
          <label for="first_name">First Name</label>
          <input type="text" name="first_name" id="first_name" required>
        </div>
        <div class="form-group">
          <label for="last_name">Last Name</label>
          <input type="text" name="last_name" id="last_name" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" name="address" id="address" required>
        </div>
        <div class="form-group">
          <label for="contact_number">Contact Number</label>
          <input type="text" name="contact_number" id="contact_number" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="role_id">Role</label>
          <select name="role_id" id="role_id" required>
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
          <label for="department">Department</label>
          <select name="department" id="department" required>
            <option value="IT">IT</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>

      <div class="form-row modal-actions">
      <button type="submit" class="save-btn">Save Changes</button>
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleEmployeeMenu() {
  const employeeMenu = document.getElementById('employee-menu');
  const arrow = document.getElementById('employee-arrow');
  console.log("Menu toggle clicked");
  employeeMenu.classList.toggle('active');
  arrow.classList.toggle('fa-angle-left');
  arrow.classList.toggle('fa-angle-down');
}


  function openEditModal(id, firstName, lastName, address, contactNumber, role, department, password) {
    document.getElementById('employee_id').value = id;
    document.getElementById('first_name').value = firstName;
    document.getElementById('last_name').value = lastName;
    document.getElementById('address').value = address;
    document.getElementById('contact_number').value = contactNumber;
    document.getElementById('role_id').value = role_id;
    document.getElementById('department').value = department;
    document.getElementById('password').value = password;
    document.getElementById('editModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('editModal').style.display = 'none';
  }
</script>
</body>
</html>
