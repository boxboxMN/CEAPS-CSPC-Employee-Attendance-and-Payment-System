<?php
// Start the session at the top of the page
session_start();
include 'db_conn.php';

// Check if the employee_id is set in the session
if (isset($_SESSION['employee_id'])) {
    // If employee_id is set in the session, use it
    $employee_id = $_SESSION['employee_id'];
} elseif (isset($_GET['employee_id'])) {
    // If employee_id is passed via URL, use that
    $employee_id = $_GET['employee_id'];
} else {
    // If neither is set, display an error message
    echo "Employee ID is not set in session! Please log in.";
    exit; // Stop further execution if employee_id is not found
}

// Fetch the employee details based on employee_id
$stmt = $conn->prepare("SELECT first_name, last_name, employee_id, password FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($first_name, $last_name, $employee_id, $password);

// If the employee ID exists in the database
if ($stmt->fetch()) {
    // Welcome message and employee information
    $message = "Welcome, $first_name $last_name!";
    $_SESSION['employee_id'] = $employee_id;
    $employee_info = "Please remember your Employee Credentials for easy monitoring.
     <div style='text-align: left; margin-top: 10px;'>
      Employee ID: $employee_id<br>
      Password: $password
    </div>";
} else {
    // If the employee ID is not found in the database
    $message = "⚠️ Invalid employee ID.";
    $employee_info = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Credentials</title>
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: linear-gradient(90deg, 
          rgb(91, 24, 121),  
          rgb(130, 130, 130), 
          rgb(252, 252, 252), 
          rgb(78, 80, 221), 
          rgb(83, 62, 206)
      );
      background-size: 500% 500%;
      animation: smoothRGB 8s infinite linear;
      overflow: hidden;
    }

    @keyframes smoothRGB {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    body::after {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-image: url('bg1.png');
      background-size: cover;
      background-position: center;
      opacity: 75%;
      z-index: -1;
    }
    .box {
      background: #fff;
      color: #333;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      text-align: center;
    }

    .box h2 {
      margin-bottom: 15px;
    }

    .box a {
      text-decoration: none;
      color: white;
      background-color: #004085;
      padding: 10px 20px;
      border-radius: 5px;
      display: inline-block;
      margin: 10px 5px 0 5px;
    }

    .box a:hover {
      background-color: #00224d;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2><?= $message ?></h2>
    <?php if ($employee_info): ?>
        <p><?= $employee_info ?></p>
    <?php endif; ?>

    <a href="employeelogin.php">Go to Login</a>
    <a href="employeedashboard.php">View Employee Dashboard</a>
  </div>
</body>
</html>
