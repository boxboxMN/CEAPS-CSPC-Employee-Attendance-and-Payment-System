<?php
session_start();
include 'timezone.php';
include 'db_conn.php';

$current_time = date('Y-m-d h:i A');
$current_date = date('Y-m-d');

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee'];
    $password = $_POST['password'];
    $status = $_POST['status'];
    $date = $_POST['date'] ?? $current_date;
    $time = $_POST['time_in'] ?? date('h:i A');

    // Validate employee
    $stmt = $conn->prepare("SELECT first_name, last_name, password FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $first_name = $employee['first_name'];
        $last_name = $employee['last_name'];
        $stored_password = $employee['password'];

        if ($stored_password === $password) {
            $datetime = "$date $time";

            if ($status === 'in') {
                $query = "INSERT INTO attendance (employee_id, time_in, status, date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssss', $employee_id, $datetime, $status, $date);
            } else {
                $query = "UPDATE attendance SET time_out = ?, status = ? WHERE employee_id = ? AND time_out IS NULL AND date = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssss', $datetime, $status, $employee_id, $date);
            }

            if ($stmt->execute()) {
                header("Location: confirmation.php?employee=$employee_id&status=$status");
                exit();
            } else {
                $error = "Database error: " . $stmt->error;
            }

        } else {
            $error = "Wrong password.";
        }

    } else {
        $error = "Employee ID not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.40/moment-timezone-with-data.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    .container {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-box {
      width: 350px;
      padding: 10px 30px 10px 30px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
      border: 2px solid #004085;
      margin-bottom: 1px;
      height:90%;
    }

    .login-logo p {
      font-size: 20px;
      color: #004085;
      font-weight: bold;
    }

    .logo img {
      height: 140px;
      padding:0px;
      margin:0px;
    }

    .form-group {
      margin-bottom: 5px;
    }

    .form-control {
      width: 21em;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
    }
    .form-box {
      width: 20em;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
    }

    .button-container {
      display: flex;
      justify-content: center;
      margin-top: 8px;
    }

    button {
      background-color: #004085;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      width: 100%;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #00224d;
    }

    .alert {
      display: none;
      margin-top: 20px;
      padding: 8px;
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
      display: block;
      margin-top:2px;
      margin-bottom:2px;
    }

    .picker-container {
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .picker-container .form-group {
      flex: 8;
    }

    .picker-container input[type="date"],
    .picker-container input[type="time"] {
      width: 100%;
      max-width: 80%;
    }
    .success-box {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 14px;
}

  </style>
</head>
<body>
<div class="container">
    <div class="login-box">
      <div class="logo">
        <a href="../main.php"><img src="logo.png" alt="Logo" id="logoImg"></a>
      </div>

      <?php
        // Display current date and time
        echo "<p id='time' class='bold' style='font-size:14px; margin:2px; padding:0;'>$current_time</p>";
      ?>

      <!-- Admin Login Form on the Right -->
      <div class="form-container">
            <h3 style="margin:5px 5px 0px 5px; padding:2px 2px 0px 2px;">Employee Login</h3>
      </div>
      <h5 class="login-box-msg" style="margin:0px; padding:0px;">Enter Employee ID</h5>
      <?php if (isset($_GET['logout'])): ?>
                 <div class="success-box"><?= htmlspecialchars($_GET['logout']) ?></div>
      <?php endif; ?>
      <form action="employeelogin.php" method="POST">
        <div class="form-group">
        <br>
          <select class="form-control" name="status">
            <option value="in">Time In</option>
            <option value="out">Time Out</option>
          </select>
        </div>
        
        <!-- Date and Time Picker Container -->
        <div class="picker-container">
          <div class="form-group">
            <input type="date" class="form-control" name="date" required>
          </div>
          <div class="form-group">
            <input type="time" class="form-control" name="time_in" required>
          </div>
        </div>

        <!-- Employee ID -->
        <div class="form-group">
          <input type="text" class="form-box" name="employee" placeholder="Enter Employee ID" required>
        </div>

              <!-- Password Field with toggle -->
      <div class="form-group password-group" style="position: relative;">
        <input type="password" class="form-box" name="password" id="passwordInput" placeholder="Enter Password" required>
        <span id="togglePassword" class="fa fa-eye" style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></span>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="display: block;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
        <!-- Button -->
        <div class="button-container">
          <button type="submit">Login</button>
        </div>

      </form>
    </div>
  </div>

</body>
</html>
<script>
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('passwordInput');

  togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
  });
</script>
