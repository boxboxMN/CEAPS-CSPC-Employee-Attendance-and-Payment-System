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
  margin-right: 150vh;
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

    .top-bar {
      display: flex;
      justify-content: space-between;
      margin-top: 10px;
      margin-left:37vh;
    }

    .top-bar button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
    }

    
    .controls {
      display: flex;
      justify-content: space-between;
      margin-top: 15px;
      margin-left:37vh;
    }

    .controls select, .controls input[type="text"] {
      padding: 6px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-right:10vh;


    }

    table {
      width: 80%;
      border-collapse: collapse;
      margin-left:36vh;
      margin-top:5vh;
    }

    thead {
      background-color: #f5f5f5;
      border-bottom: 2px solid #31628b;
    }

    th, td {
      padding: 14px 16px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      font-weight: 600;
      color: #333;
    }

    td {
      color: #444;
    }

    tr:hover {
      background-color: #f0f8ff;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
      margin-right: 5px;
    }

    .edit-btn {
      background-color: #28a745;
      color: white;
    }

    .delete-btn {
      background-color: #dc3545;
      color: white;
    }

    .pagination {
      margin-top: 10px;
      margin-right:10vh;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .pagination button {
      background-color: #fff;
      border: 1px solid #ccc;
      padding: 5px 10px;
      margin: 0 2px;
      border-radius: 4px;
      cursor: pointer;
    }

    .pagination .active {
      background-color: #007bff;
      color: white;
      border-color: #007bff;
    }

    .sort-icon {
      font-size: 12px;
      color: #888;
      margin-left: 5px;
      cursor: pointer;
    }

  </style>
</head>
<body>
  

  <!-- Header -->
  <header class="main-header">
  <div class="header-container">
    <h1 class="dashboard-title">Position</h1>
     <form method="get" action="admin_main.php" style="display: inline;">
    <input type="hidden" name="logout" value="true">
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

      <li><a href="position.php"><i class="fa fa-suitcase"></i> Role</a></li>
      <li><a href="payroll.php"><i class="fa fa-money"></i> Payroll</a></li>
    </ul>
  </section>
</aside>

<div class="table-container">
    <div class="top-bar">
    <button onclick="openModal()">+ New</button>
    </div>

    <div class="controls">
      <div>
        Show
        <select>
          <option>10</option>
          <option>25</option>
          <option>50</option>
        </select>
      
      </div>
      <div>
        Search:
        <input type="text" id="searchInput" placeholder="Search position or rate per hour" style="margin-bottom: 10px; padding: 5px; width: 240px;">
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Position Title <span class="sort-icon">⇅</span></th>
          <th>Rate per Hour <span class="sort-icon">⇅</span></th>
          <th>Tools</th>
        </tr>
      </thead>
      <tbody id="positionTableBody">
        <tr>
          <td>Software Engineer</td>
          <td>500.00</td>
          <td>
            <button class="btn edit-btn">Edit</button>
            <button class="btn delete-btn">Delete</button>
          </td>
        </tr>
        <tr>
          <td>Systems Administrator</td>
          <td>500.00</td>
          <td>
            <button class="btn edit-btn">Edit</button>
            <button class="btn delete-btn">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="pagination">
      <div>Showing 1 to 2 of 2 entries</div>
      <div>
        <button>Previous</button>
        <button class="active">1</button>
        <button>Next</button>
      </div>
    </div>
  </div>

  <div id="addPositionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div style="background:#fff; padding:20px; border-radius:8px; width:300px; position:relative;">
    <h3 style="margin-top:0;">Add Position</h3>
    <form id="positionForm" method="POST" action="add_position.php">
      <label for="title">Position Title</label><br>
      <input type="text" id="title" name="title" required style="width:100%; padding:8px; margin:5px 0;"><br>

      <label for="rate">Rate per Hour</label><br>
      <input type="number" id="rate" name="rate" required step="0.01" style="width:100%; padding:8px; margin:5px 0;"><br>

      <button type="submit" style="margin-top:10px; background:#28a745; color:white; padding:8px 12px; border:none; border-radius:4px; cursor:pointer;">Save</button>
      <button type="button" onclick="closeModal()" style="margin-top:10px; background:#dc3545; color:white; padding:8px 12px; border:none; border-radius:4px; cursor:pointer; float:right;">Cancel</button>
    </form>
  </div>
</div>

<div id="editPositionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div style="background:#fff; padding:20px; border-radius:8px; width:300px; position:relative;">
    <h3 style="margin-top:0;">Edit Position</h3>
    <form id="editPositionForm">
      <input type="hidden" id="editId">
      <label for="editTitle">Position Title</label><br>
      <input type="text" id="editTitle" name="editTitle" required readonly style="width:100%; padding:8px; margin:5px 0;"><br>

      <label for="editRate">Rate per Hour</label><br>
      <input type="number" id="editRate" name="editRate" required step="0.01" style="width:100%; padding:8px; margin:5px 0;"><br>

      <button type="submit" style="margin-top:10px; background:#28a745; color:white; padding:8px 12px; border:none; border-radius:4px; cursor:pointer;">Update</button>
      <button type="button" onclick="closeEditModal()" style="margin-top:10px; background:#dc3545; color:white; padding:8px 12px; border:none; border-radius:4px; float:right;">Cancel</button>
    </form>
  </div>
</div>


<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
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
  </script>

<script>
function openModal() {
  document.getElementById('addPositionModal').style.display = 'flex';
}

function closeModal() {
  document.getElementById('addPositionModal').style.display = 'none';
}
</script>

<script>
document.getElementById("positionForm").addEventListener("submit", function(e) {
  e.preventDefault(); // prevent default form submission

  const title = document.getElementById("title").value;
  const rate = document.getElementById("rate").value;

  fetch('add_position.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `title=${encodeURIComponent(title)}&rate=${encodeURIComponent(rate)}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      const tableBody = document.getElementById("positionTableBody");

      // Create new row
      const newRow = document.createElement("tr");
      newRow.innerHTML = `
        <td>${data.title}</td>
        <td>${data.rate}</td>
        <td>
          <button class="btn edit-btn">Edit</button>
          <button class="btn delete-btn">Delete</button>
        </td>
      `;
      tableBody.appendChild(newRow);

      // Clear and close modal
      document.getElementById("positionForm").reset();
      closeModal();
    } else {
      alert("Error: " + data.message);
    }
  })
  .catch(err => {
    alert("Something went wrong.");
    console.error(err);
  });
});
</script>

<script>
  // Open Edit Modal and Populate Values
  function closeEditModal() {
    document.getElementById('editPositionModal').style.display = 'none';
  }

  function openEditModal(title, rate, id) {
    document.getElementById('editTitle').value = title;
    document.getElementById('editRate').value = rate;
    document.getElementById('editId').value = id;
    document.getElementById('editPositionModal').style.display = 'flex';
  }

  // Edit Button Event
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-btn')) {
      const row = e.target.closest('tr');
      const title = row.cells[0].innerText;
      const rate = row.cells[1].innerText;
      const id = row.dataset.id;
      openEditModal(title, rate, id);
    }
  });

  document.getElementById("editPositionForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const id = document.getElementById("editId").value;
    const rate = document.getElementById("editRate").value;

    fetch('update_position.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(id)}&rate=${encodeURIComponent(rate)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        // Update the row on the page
        const row = document.querySelector(`tr[data-id='${id}']`);
        row.cells[1].innerText = parseFloat(rate).toFixed(2);
        closeEditModal();
      } else {
        alert('Update failed: ' + data.message);
      }
    })
    .catch(err => {
      alert('Something went wrong.');
      console.error(err);
    });
  });

  // Delete Button Confirmation and Request
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('delete-btn')) {
    const row = e.target.closest('tr');
    const id = row.dataset.id;
    const title = row.cells[0].innerText;

    if (confirm(`Are you sure you want to delete the position "${title}"? This action cannot be undone.`)) {
      fetch('delete_position.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(id)}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          row.remove(); // Remove the row from the table
        } else {
          alert('Delete failed: ' + data.message);
        }
      })
      .catch(err => {
        alert('Something went wrong.');
        console.error(err);
      });
    }
  }
});


document.getElementById('searchInput').addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('tbody tr');

  rows.forEach(row => {
    const position = row.cells[0].textContent.toLowerCase();
    const rate = row.cells[1].textContent.toLowerCase();
    const match = position.includes(filter) || rate.includes(filter);

    row.style.display = match ? '' : 'none';
  });
});


</script>


</body>
</html>