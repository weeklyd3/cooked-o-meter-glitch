<?php
session_start();
header("Content-Type: image/png");
$string = $_SESSION['quadratic'][0] . 'x +' . $_SESSION['quadratic'][1] . 'x+' . $_SESSION['quadratic'][2];
$length_of_first = strlen($_SESSION['quadratic'][0] . 'x');
$string = str_replace('+-', '-', $string);
$imgPath = 'rainbow_gradient.jpeg';
$image = imagecreatefromjpeg($imgPath);
$color = imagecolorallocate($image, 255, 255, 255);
$fontSize = 4;
$x = 0;
$y = 0;
imagestring($image, $fontSize, $x, $y, $string, $color);
imagestring($image, 4, 0, -2, str_repeat(' ', $length_of_first) . '2', $color);
imagepng($image);
imagedestroy($image);