<?php
$data_path = file_get_contents(__DIR__ . '/DATA_PATH');
function check_captcha() {
	if ($_SESSION['no_captcha'] && !isset($_POST['captcha_required'])) return 0;
	$captcha_1 = $_POST['captcha'] == '' ? 0 : $_POST['captcha'];
	$captcha_2 = $_POST['captcha2'] == '' ? 0 : $_POST['captcha2'];
	$captcha_3 = $_POST['captcha3'] == '' ? 0 : $_POST['captcha3'];
	$captcha_4 = $_POST['captcha4'] == '' ? 0 : $_POST['captcha4'];
	if ($captcha_4 == 0) {
		?><div class="error">In the CAPTCHA, your solution divides by zero. Please do another CAPTCHA. <?php if ($_POST['captcha']  == '' ||
				   $_POST['captcha2'] == '' ||
				   $_POST['captcha3'] == '' ||
				   $_POST['captcha4'] == '') { ?> (Note that empty number fields in the CAPTCHA are assumed to be zero.)<?php } ?></div><?php
		return 1;
	}
	$captcha_sqrt = $_POST['captcha_sqrt'] ? true : false;
	$captcha_i    = $_POST['captcha_imaginary'] ? true : false;
	$captcha_real = $captcha_1 / $captcha_4;
	$captcha_right = $captcha_3;
	if ($captcha_sqrt) $captcha_right = sqrt($captcha_right);
	$captcha_right *= $captcha_2;
	$captcha_right /= $captcha_4;
	$check1 = check_captcha_answer($captcha_real, $captcha_right, $captcha_i);
	$check2 = check_captcha_answer($captcha_real, -$captcha_right, $captcha_i);
	if (!$check1 || !$check2) {
		?><div class="error">CAPTCHA was not done correctly. <?php if ($_POST['captcha']  == '' ||
		$_POST['captcha2'] == '' ||
		$_POST['captcha3'] == '' ||
		$_POST['captcha4'] == '') { ?> (Note that empty number fields in the CAPTCHA are assumed to be zero.)<?php } ?></div><?php
		return 2;
	}
}
function check_captcha_answer($real, $right, $imaginary) {
	$real_part = $real;
	$imag_part = 0;
	if ($imaginary) $imag_part = $right;
	else $real_part += $right;
	$real_out = $real_part ** 2 - $imag_part ** 2;
	$imag_out = 2 * $real_part * $imag_part;
	$real_out *= $_SESSION['quadratic'][0];
	$imag_out *= $_SESSION['quadratic'][0];
	$real_out += $real_part * $_SESSION['quadratic'][1];
	$imag_out += $imag_part * $_SESSION['quadratic'][1];
	$real_out += $_SESSION['quadratic'][2];
	$small_value = 0.01;
	$result = abs($real_out) < $small_value && abs($imag_out) < $small_value;
	if ($result && $_POST['captcha_inhibit']) $_SESSION['no_captcha'] = true;
	return $result;
}
function log_message($message) {
	$data_path = file_get_contents(__DIR__ . '/DATA_PATH');
	$time = time();
	$ip = $_SERVER['REMOTE_ADDR'];
	$port = $_SERVER['REMOTE_PORT'];
	$uri = $_SERVER['REQUEST_URI'];
	$entry = "[$time] ($ip:$port) $uri: $message\n";
	file_put_contents(__DIR__ . "/../$data_path/log.log", $entry, FILE_APPEND);
}
function account_login($hash) {
	$data_path = file_get_contents(__DIR__ . '/DATA_PATH');
	$user = json_decode(file_get_contents(__DIR__ . "/../$data_path/users/$hash.json"), false);
	$_SESSION['logged_in'] = true;
	$_SESSION['hash'] = $hash;
	$_SESSION['username'] = $user->username;
	$username = $user->username;
	log_message("Account $username ($hash) logged in");
}
function make_x_url($text) {
	$url = "https://x.com/intent/post?url=" . $text;
	return $url;
}
function make_x_link($text, $button) {
	?><a href="<?php echo htmlspecialchars(make_x_url($text)); ?>"><?=$button?></a><?php
}