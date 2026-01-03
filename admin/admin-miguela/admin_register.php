<?php
// Include the database connection file
include 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form inputs
    $username = htmlspecialchars($_POST['username']); // Changed from admin_id to username
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $address = htmlspecialchars($_POST['address']);
    $role = htmlspecialchars($_POST['role']);
    $password = $_POST['password'];

    // Check required fields
    if (empty($username) || empty($first_name) || empty($last_name) || empty($address) || empty($role) || empty($password)) {
        echo "All fields are required!";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the admin table (now using username instead of admin_id)
    $stmt = $conn->prepare("INSERT INTO admin (username, first_name, last_name, address, role, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $username, $first_name, $last_name, $address, $role, $hashed_password);

    if ($stmt->execute()) {
        // Redirect to admin credentials page with username
        header("Location: admincredentials.php?username=" . urlencode($username));
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #e5e5f7;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background-image: url('bg1.png');
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

    .register-container {
      background: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      display: flex;
      max-width: 900px;
      width: 90%;
    }

    .register-container img {
      width: 220px;
      height: auto;
      margin-top: 50px;
    }

    .form-wrapper {
      margin-left: 30px;
      width: 100%;
    }

    h2 {
      margin-bottom: 20px;
      color: #1a1a7a;
      margin-left:20vh;
    }

    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px 20px;
    }

    input, select {
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 100%;
    }

    .full-width {
      grid-column: span 2;
      display: flex;
      justify-content: center;
    }

    button {
      background-color: #003d91;
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #002d6b;
    }

    @media (max-width: 768px) {
      .register-container {
        flex-direction: column;
        align-items: center;
      }

      .form-wrapper {
        margin-left: 0;
        margin-top: 20px;
      }

      form {
        grid-template-columns: 1fr;
      }

      .full-width {
        grid-column: span 1;
      }
    }
  </style>
</head>
<body>

  <div class="register-container">
    <div class="logo">
    <a href="admin_main.php"><img src="logo.png" alt="CSPC Logo"></a>
    </div>
    <div class="form-wrapper">
  <h2><span style="color:#1a1a7a;">Register</span> an Account</h2>
  <form method="POST">
  <input type="text" name="first_name" placeholder="Enter first name" required>
  <input type="text" name="last_name" placeholder="Enter last name" required>
  <input type="text" name="address" placeholder="Enter your address" required>
  <input type="text" name="contact_number" placeholder="Enter your contact number" required>

  <input type="password" name="password" placeholder="Enter password" required>

  <input type="text" name="username" placeholder="Enter username" required> <!-- 👈 Added this line -->
  <select name="role" required>
    <option value="" disabled selected>Select Role</option>
    <option value="Admin">Admin</option>
  </select>

  <div class="full-width">
    <button type="submit">Register</button>
  </div>
</form>
