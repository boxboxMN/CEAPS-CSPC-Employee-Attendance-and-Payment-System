<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        /* Container for logo and form */
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
            border: 2px solid  #004085;
        }

        /* Logo Styling */
        .logo img {
            height: 200px;
            width: auto;
        }
        /* Form Styling */
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

        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        button {
            font-family: 'Poppins', sans-serif;
            background-color: #004085; 
            color: white; 
            padding: 12px;
            border: none;
            border-radius: 5px;
            margin-bottom: 15px;
            cursor: pointer;
            width: 180px;
            font-size: 16px;
            transition: background 0.3s ease-in-out;
        }

        button:hover {
            background-color: #00224d;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo on the Left -->
        <div class="logo">
            <a href="../main.php"><img src="logo.png" alt="Logo" id="logoImg"></a>
        </div>

        <!-- Form on the Right -->
        <div class="form-container">
            <h2>Welcome <span>CEAPS</span> Admin!</h2>
            <div class="button-container">
            <a href="admin_login.php"><button type="button">LOGIN</button></a>
            <a href="admin_register.php"><button type="button">REGISTER</button></a>
            </div>
        </div>
    </div>
</body>
</html>
