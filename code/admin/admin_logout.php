<?php
session_start();
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_logged_in']);
header("Location: admin_login.php");
exit();
?>