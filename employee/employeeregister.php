<?php
// Include the database connection file
include 'db_conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get the input data
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $address = htmlspecialchars($_POST['address']);
    $contact_number = htmlspecialchars($_POST['contact_number']);
    $role_id = htmlspecialchars($_POST['role_id']); // Corrected this line
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Register</title>
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
            border: 2px solid  #004085;
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

        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            text-align: left;
            width: 50%;
        }

        label {
            font-weight: bold;
            font-size: 14px;
            display: block;
        }

        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 15px;
        }

        button {
            font-family: 'Poppins', sans-serif;
            background-color: #004085; 
            color: white; 
            padding: 12px;
            border: none;
            border-radius: 5px;
            margin-bottom: 10px;
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
                width: 90%;
            }

            .logo img {
                height: 150px;
                margin-bottom: 20px;
            }

            .form-container {
                width: 100%;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
        <a href="../main.php"><img src="logo.png" alt="Logo" id="logoImg"></a>
        </div>
        <div class="form-container">
            <h2><span>Register</span> an Account</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first-name">First Name</label>
                        <input type="text" id="first-name" name="first_name" placeholder="Enter first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" name="last_name" placeholder="Enter last name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" placeholder="Enter your address" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="tel" id="contact" name="contact_number" placeholder="Enter your contact number" required>
                    </div>
                </div>
                
                <div class="form-row">
                <div class="form-group">
                    <label for="role_id">Role</label>
                    <select name="role_id" id="role_id" required>
                        <option value="">Select Role</option>
                        <?php
                        $roleQuery = "SELECT role_id, role FROM roles";
                        $roleResult = $conn->query($roleQuery);
                        while ($roleRow = $roleResult->fetch_assoc()) {
                            echo "<option value='{$roleRow['role_id']}'>{$roleRow['role']}</option>";
                        }
                        ?>
                    </select>
                </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="IT">IT</option>
                        </select>
                    </div>
                </div>

                <div class="button-container">
                    <button type="submit">Register</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
