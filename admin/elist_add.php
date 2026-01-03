<?php
include '../db_conn.php';

// Only proceed if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Properly get the numeric max from employee_id
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(employee_id, 4) AS UNSIGNED)) AS max_num FROM employees");
    $row = $result->fetch_assoc();
    $maxNum = $row['max_num'];

    // Generate new unique employee ID
    $num = $maxNum ? $maxNum + 1 : 1;
    $newId = 'EMP' . str_pad($num, 3, '0', STR_PAD_LEFT);

    // Get all valid role_ids from roles table
    $roleResult = $conn->query("SELECT role_id FROM roles");
    $roleIds = [];
    while ($r = $roleResult->fetch_assoc()) {
        $roleIds[] = $r['role_id'];
    }

    if (empty($roleIds)) {
        die("No roles found in the roles table. Please insert roles first.");
    }

    // Sanitize & fetch form inputs
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $address = $_POST['address'] ?? '';
    $contactNumber = $_POST['contact_number'] ?? '';
    $roleId = $_POST['role_id'] ?? '';
    $department = $_POST['department'] ?? '';
    $password = $_POST['password'] ?? ''; // Plaintext password

    // Validate roleId
    if (!in_array($roleId, $roleIds)) {
        die("Invalid role ID provided.");
    }

    // Prepare & insert into DB
    $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, last_name, address, contact_number, role_id, department, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $newId, $firstName, $lastName, $address, $contactNumber, $roleId, $department, $password);

    if ($stmt->execute()) {
        header("Location: employee_list.php?success=Employee Added successfully");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
