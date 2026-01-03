<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <style>
        img {
            height: 95vh;
            width: 95vh;
            cursor: pointer;
            transition: all 0.6s ease-in-out;
        }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
            display: none;
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #004085;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            width: 330px;
            transition: opacity 0.5s ease-in-out;
        }
        button{
            font-family: 'Poppins', sans-serif;
            background-color: #004085; 
            color: white; padding: 10px; 
            border: none; 
            border-radius: 5px;
            margin-right: 5px; 
            cursor: pointer;
            width: 10em;
            transition: background 0.3s ease-in-out;
        }
        button:hover {
            background-color: #00224d;
        }
    </style>
</head>
<body>
    <div class="logo" onclick="toggleForm()">
        <img src="logo.png" alt="Logo" id="logoImg">
    </div>

    <form action="" method="post" class="box" id="loginForm">
        <h2>Welcome to <span style="color:rgb(0, 2, 118)">CEAPS</span> Portal!</h2>
        <div style="margin-top: 10px;">
            <a href="admin/admin_main.php"><button type="button" >ADMIN</button></a>
            <br>
            <br>
            <a href="employee/employeemain.php"><button type="button">EMPLOYEE</button></a>
        </div>
    </form>

    <script>
        let isFormVisible = false;
        
        function toggleForm() {
            let logo = document.getElementById("logoImg");
            let form = document.getElementById("loginForm");
            
            if (!isFormVisible) {
                logo.style.height = "150px";
                logo.style.width = "150px";
                form.style.display = "block";
                form.style.opacity = "1";
            } else {
                logo.style.height = "95vh";
                logo.style.width = "95vh";
                form.style.opacity = "0";
                setTimeout(() => form.style.display = "none", 500);
            }
            isFormVisible = !isFormVisible;
        }
    </script>
</body>
</html>