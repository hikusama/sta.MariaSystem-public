<?php
include '../header.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['email_queue'])) {
    include 'eror.php';
    exit;
}
?>