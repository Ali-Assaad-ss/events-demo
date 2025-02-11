<?php
session_start();
// unset session variable
unset($_SESSION['user']);
// redirect to login page
header('Location: ../login.php');
exit;
?>