<?php
session_start();
include 'db_conn.php';

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($username) && !empty($password)) {
        // Query for the username
        $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            // Check password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['username'] = $username;
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Your password is incorrect.";
            }
        } else {
            $error = "Admin not found.";
        }

        $stmt->close();
    } else {
        $error = "Please fill in both fields.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 60%;
            max-width: 800px;
            border: 2px solid #004085;
        }

        .logo img {
            height: 200px;
            width: auto;
        }

        .form-container {
            text-align: center;
            width: 50%;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        span {
            color: rgb(0, 2, 118);
        }

        form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            font-family: 'Poppins', sans-serif;
            background-color: #004085; 
            color: white; 
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s ease-in-out;
        }

        button:hover {
            background-color: #00224d;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
            }

            .logo img {
                height: 150px;
                margin-bottom: 20px;
            }

            .form-container {
                width: 100%;
            }
        }
        .success-box {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
                padding: 10px 15px;
                border-radius: 5px;
                margin-bottom: 15px;
                font-size: 14px;
}

    </style>
</head>
<body>
    <div class="container">
        <!-- Logo on the Left -->
        <div class="logo">
            <a href="admin_main.php"><img src="logo.png" alt="Logo" id="logoImg"></a>
        </div>

        <!-- Admin Login Form on the Right -->
        <div class="form-container">
            <h2>Admin <span>Login</span></h2>
            <?php if (isset($_GET['logout'])): ?>
                 <div class="success-box"><?= htmlspecialchars($_GET['logout']) ?></div>
            <?php endif; ?>



            <?php if (!empty($error)): ?>
                <p class="error-message"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>

