<?php
require_once 'elements/library.php';
if (is_dir("$data_path/setup")) die("Already set up");
mkdir("$data_path/classes", 0777, true);
mkdir("$data_path/saves", 0777, true);
mkdir("$data_path/users", 0777, true);
mkdir("$data_path/revs", 0777, true);
mkdir("$data_path/lastrev", 0777, true);
mkdir("$data_path/setup", 0777, true);
$time = time();
$ip = $_SERVER['REMOTE_ADDR'];
$port = $_SERVER['REMOTE_PORT'];
$uri = $_SERVER['REQUEST_URI'];
$entry = "[$time] ($ip:$port) $uri: Set up!\n";
file_put_contents("$data_path/setup/info", $entry);
file_put_contents("$data_path/class_index.json", "[]");
log_message('Setup complete');