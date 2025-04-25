<?php
session_start();
if (!isset($_GET['score']) ||
    !isset($_GET['name'])) {
	?>Isn't set up correctly. You should include <code>score</code> and <code>name</code> URL parameters.<?php
	exit();
}
header('Content-Type: image/svg+xml');
$image = file_get_contents('meter2.svg');
# we do a little editing
$datetext = date("F j, Y, g:i:s A ") . 'UTC';
$image = str_replace('$CLASSNAME', htmlspecialchars($_GET['name']), $image);
$image = str_replace('$DATETIME', $datetext, $image);
$translate = min(250, max(-250, ($_GET['score'] - 100) / 100 * 250)) / 3.779528;
$image = str_replace('transform="translate(0,0)" arrow="true"', 'transform="translate(' . htmlspecialchars($translate) . ',0)" arrow="true"', $image);
$image = str_replace('transform="translate(0,-6.9522241)" arrow="true"', 'transform="translate(' . htmlspecialchars($translate) . ',-6.9522241)" arrow="true"', $image);
echo $image;