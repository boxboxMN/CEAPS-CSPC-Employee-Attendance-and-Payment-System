<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];
include '../db_conn.php'; // Include database connection

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

// Query to fetch roles
$sql = "SELECT * FROM roles";
$result = $conn->query($sql);

// Check if the query was successful
if ($result === false) {
    die("Error: " . $conn->error);
}


?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Role Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
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
      margin-right:141vh;
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
    /* Shared styles for both role and employee tables */
  .role-table {
    width: 77%;
    margin-left: 19em;
    margin-top: 30px;
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
  }
  .role-table th,
  .role-table td {
    border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
  }
  .role-table th {
      background-color: #2a628f;
      color: white;
      font-weight: bold;
    }
  .role-table tr:hover {
    background-color: #f1f1f1;
  }

  .role-table td a {
    color:rgb(255, 255, 255);
      text-decoration: none;
    }
  .role-table td a:hover {
    text-decoration: underline;
  }
  .role-table table {
  border-radius: 8px;
  overflow: hidden;
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}
  
      #editModal {
    position: fixed;
    top: 50%;
    left: 60%; /* Shift to the right */
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 30px 25px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    z-index: 2000;
    display: none;
    width: 300px;
  }

  #editModal label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
  }

  #editModal input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
  }

  #editModal button {
    margin-top: 15px;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  #editModal button[type="submit"] {
    background-color: #2a628f;
    color: white;
  }

  #editModal button[type="button"] {
    background-color: #ccc;
    margin-left: 10px;
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
.form-row {
  display: flex;
  justify-content: space-between;
  gap: 5px;
  margin-bottom: 15px;
  flex-wrap: wrap;
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
    <h1 class="dashboard-title">Roles List</h1>
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



<?php if (isset($_GET['message'])): ?>
  <div id="successMessage" style="margin-left: 15em; padding: 10px; background-color: #28a745; color: white; font-size: 16px; text-align:center">
    <?= htmlspecialchars($_GET['message']) ?>
  </div>
<?php elseif (isset($_GET['error'])): ?>
  <div id="errorMessage" style="margin-left: 15em; padding: 10px; background-color:rgb(170, 31, 31); color: white; font-size: 16px; text-align:center">
    <?= htmlspecialchars($_GET['error']) ?>
  </div>
<?php endif; ?>



<div class="form-row">
        <div class="form-group">
            <div style="margin: 20px 16.5em 0; padding: 6px 12px; border-radius: 4px;">
            <button onclick="openAddModal()" style="padding: 6px 12px; border-radius: 4px; background-color: #2a628f; color: white; border: none; cursor: pointer; height:2.5em; width:10em;">
                <i class="fa fa-plus"></i> Add Role
              </button>
            </div>
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: flex-end; margin: 20px 20px 10px 0;">
              <label for="employeeSearch" style="font-weight: 500; margin-right: 10px; padding-top:.5em;">Search:</label>
              <input type="text" id="employeeSearch" onkeyup="searchRole()"placeholder="Search employee name..." 
                    style="padding: 5px 10px; border-radius: 6px; border: 1px solid #ccc; width: 220px;height:1.8em; font-size: 14px; ">
            </div>
    
        </div>
</div>


<!-- Add Role Modal -->
<div id="addModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.4); z-index: 1000;">
  <div style="background-color: white; border-radius: 10px; max-width: 400px; margin: 100px auto; padding: 30px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); position: relative;">
    <h2 style="margin-bottom: 20px;">Add Role</h2>
    <button onclick="closeAddModal()" style="position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
    
    <form id="addForm" method="POST" action="radd.php">
      <div style="margin-bottom: 15px;">
        <label for="new_role_title" style="display: block; font-weight: 500; margin-bottom: 5px;">Role Title:</label>
        <input type="text" name="role" id="new_role_title" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
      </div>
      
      <div style="margin-bottom: 20px;">
        <label for="new_rate_per_hour" style="display: block; font-weight: 500; margin-bottom: 5px;">Rate per Hour:</label>
        <input type="number" name="rate_per_hour" id="new_rate_per_hour" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeAddModal()" style="background-color: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px;">Cancel</button>
        <button type="submit" style="background-color: #0d6efd; color: white; padding: 8px 16px; border: none; border-radius: 5px;">Save</button>
      </div>
    </form>
  </div>
</div>


<!-- Table for roles -->
<div class="role-table">
  <table>
    <thead>
      <tr>
        <th>Role Title <span class="sort-icon"></span></th>
        <th>Rate per Hour <span class="sort-icon"></span></th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['rate_per_hour']) ?></td>
            <td>
              <button class="btn edit-btn" onclick="openEditModal(<?= $row['role_id'] ?>, '<?= addslashes($row['role']) ?>', <?= $row['rate_per_hour'] ?>)">Edit</button>
              <button class="btn delete-btn" onclick="deleteRole(<?= $row['role_id'] ?>)">Delete</button>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <!-- No roles found message as a table row -->
        <tr>
          <td colspan="3" style="text-align: center; color: #888;">No roles found.</td>
        </tr>
      <?php endif; ?>
      
      <!-- Row for No result found (to be displayed dynamically) -->
      <tr id="noResultsMessage" style="display: none; background-color: #f9f9f9;">
        <td colspan="3" style="text-align: center; color: #888; padding: 15px 0; font-size: 14px;">
          No result found.
        </td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none;">
    <form id="editForm" method="POST" action="redit.php">
        <input type="hidden" name="role_id" id="role_id">
        <label for="role_title">Role Title:</label>
        <input type="text" name="role" id="role_title" required><br>
        <label for="rate_per_hour">Rate per Hour:</label>
        <input type="number" name="rate_per_hour" id="rate_per_hour" required><br>
        <button type="submit">Save Changes</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
  // Toggle sidebar menu
  function toggleEmployeeMenu() {
    var menu = document.querySelector('.treeview-menu');
    var arrow = document.getElementById('employee-arrow');
    menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
    arrow.classList.toggle('fa-angle-left');
    arrow.classList.toggle('fa-angle-down');
  }

  function openEditModal(id, role, rate) {
    document.getElementById('role_id').value = id;
    document.getElementById('role_title').value = role;
    document.getElementById('rate_per_hour').value = rate;
    document.getElementById('editModal').style.display = 'block';
  }

  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }

  function deleteRole(roleId) {
    if (confirm("Are you sure you want to delete this role?")) {
      window.location.href = `rdelete.php?role_id=${roleId}`;
    }
  }

  function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
  }

  function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
  }

  function searchRole() {
    const input = document.getElementById("employeeSearch");
    const filter = input.value.toLowerCase();
    const table = document.querySelector(".role-table tbody");
    const rows = table.getElementsByTagName("tr");
    let found = false;

    for (let i = 0; i < rows.length; i++) {
      const roleCell = rows[i].getElementsByTagName("td")[0];

      if (roleCell && roleCell.textContent.toLowerCase().includes(filter)) {
        rows[i].style.display = "";
        found = true;
      } else {
        rows[i].style.display = "none";
      }
    }

    document.getElementById("noResultsMessage").style.display = found ? "none" : "table-row"; // Use "table-row" to ensure it's a valid table row
  }
     // Function to hide the message after a certain time
  function hideMessage(elementId) {
    setTimeout(function() {
      document.getElementById(elementId).style.display = 'none';
    }, 2000); // 2000 milliseconds = 2 seconds
  }
  
  // Check if there is a success or error message and hide them after 5 seconds
  <?php if (isset($_GET['message'])): ?>
    hideMessage('successMessage');
  <?php elseif (isset($_GET['error'])): ?>
    hideMessage('errorMessage');
  <?php endif; ?>
</script>
</body>
</html>

