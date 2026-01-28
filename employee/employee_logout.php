<?php
session_start();

$_SESSION = array();

session_destroy();

header("Location: employee_login.php");
exit();
?>