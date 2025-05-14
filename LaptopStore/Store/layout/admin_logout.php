<?php
session_start();
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
header("Location: /LaptopStore/LaptopStore/layout/admin_login.php");
exit;
