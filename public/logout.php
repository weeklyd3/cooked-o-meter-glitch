<?php
session_start();
$no_captcha = $_SESSION['no_captcha'];
$_SESSION = array();
$return = isset($_GET['return']) ? $_GET['return'] : 'index.php';
if ($no_captcha) $_SESSION['no_captcha'] = true;
header('Location: /' . $return);