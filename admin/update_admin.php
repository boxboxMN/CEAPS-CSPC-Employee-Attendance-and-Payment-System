<?php
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['admin_id'];
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];

    $update_photo = false;
    $update_password = false;
    $fileName = '';

    // Handle image upload if present
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedExts)) {
            $fileName = uniqid('IMG_', true) . '.' . $fileExt;
            move_uploaded_file($fileTmp, 'uploads/' . $fileName);
            $update_photo = true;
        }
    }

    // Handle password if provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_password = true;
    }

    // Build SQL dynamically based on what needs updating
    if ($update_password && $update_photo) {
        $stmt = $conn->prepare("UPDATE admin SET username=?, first_name=?, last_name=?, password=?, photo=? WHERE id=?");
        $stmt->bind_param("sssssi", $username, $first_name, $last_name, $hashed_password, $fileName, $id);
    } elseif ($update_password) {
        $stmt = $conn->prepare("UPDATE admin SET username=?, first_name=?, last_name=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $first_name, $last_name, $hashed_password, $id);
    } elseif ($update_photo) {
        $stmt = $conn->prepare("UPDATE admin SET username=?, first_name=?, last_name=?, photo=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $first_name, $last_name, $fileName, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET username=?, first_name=?, last_name=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $first_name, $last_name, $id);
    }

    if ($stmt->execute()) {
        echo "success";
        exit;
    } else {
        echo "Error: " . $stmt->error;
        exit;
    }
}
?>
