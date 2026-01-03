<?php
// Start the session and include the database connection file
session_start();
include 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get the input data
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $address = htmlspecialchars($_POST['address']);
    $contact_number = htmlspecialchars($_POST['contact_number']);
    $role_id = htmlspecialchars($_POST['role_id']);
    $department = htmlspecialchars($_POST['department']);
    
    // Check if the role_id exists in the roles table
    $roleQuery = "SELECT role_id FROM roles WHERE role_id = ?";
    $stmt = $conn->prepare($roleQuery);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("Error: The selected role does not exist.");
    }

    // Generate a unique employee ID (6 characters mixed with numbers and letters)
    $employee_id = strtoupper(bin2hex(random_bytes(3)));

    // Generate password based on first name and 2 random digits
    $random_digits = rand(10, 99);
    $password = strtolower($first_name) . $random_digits;

    // Insert the data into the database
    $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, last_name, address, contact_number, department, role_id, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $employee_id, $first_name, $last_name, $address, $contact_number, $department, $role_id, $password);
    
    if ($stmt->execute()) {
        // Set the employee_id in session after successful registration
        $_SESSION['employee_id'] = $employee_id;
        
        // Redirect to employee credentials page with the employee_id in the query string
        header("Location: employeecredentials.php?employee_id=" . $employee_id);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
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


if ($stmt->fetch()) {
        // Welcome message
        
        $message = "Welcome, $first_name $last_name!";
        $employee_info = "Please remember your Employee Credentials for easy monitong
         <div style='text-align: left; margin-top: 10px;'>
          Employee ID: $employee_id<br>
          Password: $password
        </div>";
    } else {
        // If the employee ID is not found in the database
        $message = "⚠️ Invalid employee ID.";
        $employee_info = "";
    }