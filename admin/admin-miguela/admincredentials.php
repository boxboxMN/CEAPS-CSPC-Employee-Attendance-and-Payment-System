<?php
// Start the session and include the database connection file
session_start();
include 'db_conn.php';

// Get the username from the query string
$username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';

// Initialize variables
$message = "";
$admin_info = "";
$retrieved_username = "";

// Check if username is passed
if ($username) {
    // Fetch admin details from the database
    $stmt = $conn->prepare("SELECT username FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($retrieved_username);

    // If the admin exists in the database
    if ($stmt->fetch()) {
        $_SESSION['username'] = $retrieved_username;
        $message = "✅ Welcome, Admin <strong>$retrieved_username</strong>!";
        $admin_info = "<p>Please remember your admin credentials for future login.</p>";
    } else {
        $message = "⚠️ Invalid admin username.";
    }
} else {
    $message = "⚠️ Invalid request.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Credentials</title>
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>

  <?php if ($retrieved_username): ?>
  <script>
    // Redirect to dashboard after 3 seconds
    setTimeout(() => {
      window.location.href = "admin_dashboard.php";
    }, 3000);
  </script>
  <?php endif; ?>

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

    .redirecting {
      margin-top: 15px;
      font-size: 14px;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2><?= $message ?></h2>
    <?php if ($admin_info): ?>
        <p><?= $admin_info ?></p>
    <?php endif; ?>

    <a href="admin_login.php">Go to Login</a>
    <a href="admin_dashboard.php">View Admin Dashboard</a>

    <?php if ($retrieved_username): ?>
    <?php endif; ?>
  </div>
</body>
</html>
