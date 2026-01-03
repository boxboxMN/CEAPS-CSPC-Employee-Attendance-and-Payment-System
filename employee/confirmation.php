<?php
session_start();
include 'timezone.php';
include 'db_conn.php';

$employee = isset($_GET['employee']) ? htmlspecialchars($_GET['employee']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';

$first_name = "";
$last_name = "";
$employee_exists = false;

if ($employee && ($status === "in" || $status === "out")) {
    // Initialize $stmt only after checking the employee and status
    $stmt = $conn->prepare("SELECT first_name, last_name FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s", $employee);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $employee_exists = true;
        $stmt->bind_result($first_name, $last_name);
        $stmt->fetch();

        // ✅ Set session
        $_SESSION['employee_id'] = $employee;
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirmation</title>
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
    /* Background Layer with Animated Gradient */
    body::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(90deg, 
          rgb(91, 24, 121),  
          rgb(130, 130, 130), 
          rgb(252, 252, 252), 
          rgb(78, 80, 221), 
          rgb(83, 62, 206)
      );
      background-size: 500% 500%;
      animation: smoothRGB 8s infinite linear;
      z-index: -2;
    }

    /* Image Overlay with 10% Opacity */
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

    @keyframes smoothRGB {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .box {
      background: #fff;
      color: #333;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      text-align: center;
      z-index: 1;
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

    /* Alert styling */
    .alert {
      display: none;
      margin-top: 20px;
      padding: 10px;
      border-radius: 5px;
    }

    .alert-success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
  </style>
</head>
<body>
  <div class="box">
    <?php if ($employee && $employee_exists && $status === "in"): ?>
      <h2>✅ Welcome, <?= htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name) ?>!</h2>
      <small>Employee ID: <?= htmlspecialchars($employee) ?></small>
      <div class="alert alert-success">
        <span>Employee ID: <?= htmlspecialchars($employee) ?> marked as "Time In" successfully.</span>
      </div>
    <?php elseif ($employee && $employee_exists && $status === "out"): ?>
      <h2>⏳ Goodbye, <?= htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name) ?>!</h2>
      <small>Employee ID: <?= htmlspecialchars($employee) ?></small>
      <div class="alert alert-success">
        <span>Employee ID: <?= htmlspecialchars($employee) ?> marked as "Time Out" successfully.</span>
      </div>
    <?php elseif ($employee && !$employee_exists): ?>
      <h2>⚠️ Employee ID doesn't exist in the system.</h2>
      <div class="alert alert-danger">
        <span>Invalid Employee ID: <?= htmlspecialchars($employee) ?>. Please try again.</span>
      </div>
    <?php else: ?>
      <h2>⚠️ Invalid request.</h2>
    <?php endif; ?>

    <br>
    <a href="employeelogin.php">Go Back</a>
    <a href="employeedashboard.php">View Employee Dashboard</a>
  </div>
</body>
</html>