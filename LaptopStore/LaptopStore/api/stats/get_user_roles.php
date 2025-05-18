<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ob_start();

if (isset($_SESSION['user']['MACV'])) {
    ob_end_clean();
    echo json_encode(['macv' => $_SESSION['user']['MACV']]);
} else {
    ob_end_clean();
    echo json_encode(['macv' => null]);
}
?>