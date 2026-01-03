<?php
session_start();
session_destroy();
header("Location: ../employee/employeelogin.php?logout=You have been successfully logged out.");

exit();
?>