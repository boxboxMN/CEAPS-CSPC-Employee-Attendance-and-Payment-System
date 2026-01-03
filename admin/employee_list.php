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
      margin-right: 132.5vh;
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
      width: 77%;
      margin-left: 19em;
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
  margin-top:20px;
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

<!-- Sidebar -->
  <!-- Sidebar -->
  <aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="profile-container">
        <!-- Clickable image -->
        <div style.display='block' style="cursor:pointer;">
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
        <ul class="treeview-menu">
          <li><a href="employee_list.php">Employee list</a></li>
          <li><a href="schedule.php">Schedules</a></li>
        </ul>
      </li>

      <li><a href="role.php"><i class="fa fa-suitcase"></i> Role</a></li>
      <li><a href="payroll.php"><i class="fa-solid fa-money-check-dollar" style=" color: white"></i> Payroll</a></li>
    </ul>
  </section>
</aside>

<?php if (isset($_GET['success'])): ?>
    <div id="successMessage" style="margin-left: 15em;padding: 10px; background-color: #28a745; color: white; font-size: 16px; text-align:center">
      <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
  <div id="errorMessage" style="margin-left: 15em;padding: 10px; background-color:rgb(172, 18, 59); color: white; font-size: 16px; text-align:center">
      <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>



<div class="form-row">
        <div class="form-group">
            <div style="margin: 20px 16.5em 0; padding: 6px 12px; border-radius: 4px;">
            <button onclick="openAddModal()" style="padding: 6px 12px; border-radius: 4px; background-color: #2a628f; color: white; border: none; cursor: pointer; height:2.5em; width:10em;">
                <i class="fa fa-plus"></i> Add Employee
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
    <!-- Add a row for "No Results" message -->
    <tr id="noResultsRow" style="display: none;">
      <td colspan="6" style="text-align: center; color: #888;">No result found.</td>
    </tr>

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
      echo "<tr><td colspan='6' style='text-align: center; color: #888;'>No employees found.</td></tr>";
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
      <!-- Password with show/hide -->
      <div style="grid-column: span 2; position: relative;">
            <label for="password"><strong>Password</strong></label>
          <input type="password" name="password" id="password" required
               style="width:90%; padding:10px 40px 10px 10px; border:1px solid #ccc; border-radius:6px;">
          <i class="fa fa-eye" id="toggleEditPassword" style="position: absolute; right: 20px; top: 38px; cursor: pointer; color: #666;"></i>
      </div>

      <div class="form-row modal-actions">
      <button type="submit" class="save-btn">Save Changes</button>
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Employee Modal -->
<div id="addModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center; font-family: 'Segoe UI', sans-serif;">
  <div style="background:#fff; padding:30px; border-radius:12px; width:600px; position:relative; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin: 0;">Add Employee</h2>
      <button onclick="closeAddModal()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="elist_add.php" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

      <!-- Employee ID -->
      <div style="grid-column: span 2;">
        <label for="generated_employee_id"><strong>Employee ID</strong></label>
        <input type="text" id="generated_employee_id" value="Will be auto-generated" readonly
               style="width:95%; padding:10px; border:1px solid #ccc; border-radius:6px;">
      </div>

      <!-- Last Name -->
  
      <div>
        <label for="add_first_name"><strong>first Name</strong></label>
        <input type="text" name="first_name" id="add_first_name" required
               style="width:90%; padding:10px; border:1px solid #ccc; border-radius:6px;">
      </div>
      <div>
        <label for="add_last_name"><strong>Last Name</strong></label>
        <input type="text" name="last_name" id="add_last_name" required
               style="width:90%; padding:10px; border:1px solid #ccc; border-radius:6px;">
      </div>

      <!-- Address -->
      <div>
        <label for="add_address"><strong>Address</strong></label>
        <input type="text" name="address" id="add_address" required
               style="width:90%; padding:10px; border:1px solid #ccc; border-radius:6px;">
      </div>

      <!-- Contact Number -->
      <div>
        <label for="add_contact_number"><strong>Contact Number</strong></label>
        <input type="text" name="contact_number" id="add_contact_number" required
               style="width:90%; padding:10px; border:1px solid #ccc; border-radius:6px;">
      </div>

      <!-- Role -->
      <div>
        <label for="add_role_id"><strong>Role</strong></label>
        <select name="role_id" id="add_role_id" required
                style="width:96%; padding:10px; border:1px solid #ccc; border-radius:6px;">
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

      <!-- Department -->
      <div>
        <label for="add_department"><strong>Department</strong></label>
        <select name="department" id="add_department" required
                style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
          <option value="IT">IT</option>
        </select>
      </div>

      <!-- Password (with toggle) -->
      <div style="grid-column: span 2; position: relative;">
        <label for="add_password"><strong>Password</strong></label>
        <input type="password" name="password" id="add_password" required
               style="width:89%; padding:10px 40px 10px 10px; border:1px solid #ccc; border-radius:6px;">
        <i class="fa fa-eye" id="togglePassword" style="position: absolute; right: 20px; top: 38px; cursor: pointer; color: #666;"></i>
      </div>

      <!-- Buttons -->
      <div style="grid-column: span 2; text-align: right; margin-top: 10px;">
        <button type="submit" style="background: #0d6efd; color: white; padding: 10px 20px; border: none; border-radius: 6px; margin-right: 10px; cursor: pointer;">Add Employee</button>
        <button type="button" onclick="closeAddModal()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
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
  // Live search filter
document.getElementById('employeeSearch').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('.employee-table tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
      const firstName = row.children[1].textContent.toLowerCase();
      const lastName = row.children[2].textContent.toLowerCase();
      if (firstName.includes(searchValue) || lastName.includes(searchValue)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    document.getElementById('noResultsMessage').style.display = visibleCount === 0 ? 'block' : 'none';
  });

  function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
  document.getElementById('addModal').style.display = 'none';
}
document.getElementById('employeeSearch').addEventListener('input', function () {
  const searchValue = this.value.toLowerCase();
  const rows = document.querySelectorAll('.employee-table tbody tr');
  let visibleCount = 0;

  rows.forEach(row => {
    if (row.id !== 'noResultsRow') { // Don't check the "No result found" row
      const firstName = row.children[1].textContent.toLowerCase();
      const lastName = row.children[2].textContent.toLowerCase();
      if (firstName.includes(searchValue) || lastName.includes(searchValue)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    }
  });

  const noResultsRow = document.getElementById('noResultsRow');
  noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
});

  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('add_password');

  togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
  });
  
  const toggleEditPassword = document.getElementById('toggleEditPassword');
  const editPasswordInput = document.getElementById('password');

  toggleEditPassword.addEventListener('click', function () {
    const type = editPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    editPasswordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
  });

// Function to hide the message after 2 seconds
function hideMessageAfterDelay(messageId) {
  const messageElement = document.getElementById(messageId);
  if (messageElement) {
    setTimeout(() => {
      messageElement.style.display = 'none';
    }, 2000); // 2000ms = 2 seconds
  }
}

// Check if success or error message exists and hide after 2 seconds
<?php if (isset($_GET['success'])): ?>
  hideMessageAfterDelay('successMessage');
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
  hideMessageAfterDelay('errorMessage');
<?php endif; ?>


</script>
</body>
</html>