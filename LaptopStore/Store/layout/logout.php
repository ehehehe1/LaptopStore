<?php
session_start();

// Hủy tất cả session
session_unset();
session_destroy();

// Chuyển hướng về trang chủ
header('Location: /LaptopStore-master/LaptopStore/Store/index.php');
exit;
?>