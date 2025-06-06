<?php
session_start();

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html");
    exit();
}

$user_name = isset($_SESSION['fname']) ? $_SESSION['fname'] : 'Guest';
$profile_image = isset($_SESSION['img']) ? $_SESSION['img'] : 'images/default.png';
$address = isset($_SESSION['address']) ? $_SESSION['address'] : '';
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';
$region = isset($_SESSION['region']) ? $_SESSION['region'] : '';
?>
