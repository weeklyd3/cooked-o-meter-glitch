<?php
require_once 'library.php';
ob_start();
if (!isset($title)) $title = 'NO TITLE SET, flame the developer on twitter until he fixes this';
if (!isset($long_title)) $long_title = $title;
if (!isset($viewable_without_login)) $viewable_without_login = false;
if (!isset($calc_required)) $calc_required = false;
$calc_displayed = false;
session_start();
if (!isset($_SESSION['no_captcha'])) $_SESSION['no_captcha'] = false;
if (!isset($_SESSION['logged_in'])) $_SESSION['logged_in'] = false;
if (!isset($_SESSION['username'])) $_SESSION['username'] = null;
if (!isset($_SESSION['hash'])) $_SESSION['hash'] = null;
if ($_SESSION['logged_in']) $user = json_decode(file_get_contents(__DIR__ . "/../$data_path/users/" . $_SESSION['hash'] . '.json'), false);
if (!$_SESSION['logged_in'] && !$viewable_without_login) {
	$title = 'Please log in';
	$long_title = 'Login required';
} else {
	if ($calc_required && !isset($user->no_calc)) {
		$calc_displayed = true;
		$long_title = $title;
		if (isset($_POST['submit-calc'])) {
			if (abs($_POST['answer'] - $_SESSION['calc_answer']) < 0.01) {
				$calc_displayed = false;
				$calc_required = false;
				$user->no_calc = time();
				file_put_contents(__DIR__ . "/../$data_path/users/" . $_SESSION['hash'] . '.json', json_encode($user));
			}
		}
		if ($calc_displayed) $title = 'Additional authorization required';
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?=htmlspecialchars($title)?></title>
		<meta name="viewport" content="width=device-width,initial-scale=1" />
		<link rel="stylesheet" href="style.css" />
	</head>
	<body>
		<div class="header">
			<h1><?=htmlspecialchars($long_title)?></h1>
			<span class="float-right">
				<?php 
				if (!$_SESSION['logged_in']) {
					?>You aren't logged in.
					<a href="login.php">Log in</a>
					<a href="signup.php">Create account</a><?php
				} else {
					echo htmlspecialchars($_SESSION['username']);
					echo ' (';
					echo htmlspecialchars(substr($_SESSION['hash'], 0, 6));
					echo '...) ';
					?>
					<a href="login.php">Log in as other user</a>
					<a href="logout.php">Log out</a><?php
				}
				?>
			</span>
		</div>
		<div style="clear:both;"></div>
		<div class="content" id="main-content"><?php
		if (!$_SESSION['logged_in'] and !$viewable_without_login) {
			?>Please log in to view this site.<?php
			require_once 'footer.php';
			exit;
		}
		if ($calc_displayed) {
			$random = mt_rand() / mt_getrandmax();
			$random2 = mt_rand() / mt_getrandmax();
			$random3 = mt_rand() / mt_getrandmax();
			if (isset($_POST['answer']) && $_POST['answer'] != $_SESSION['calc_answer']) {
				?><div class="error">Sorry, that's wrong. The answer to <blockquote><?=$_SESSION['calc_html']?></blockquote> was <strong><?=$_SESSION['calc_answer']?></strong>. Your answer was <strong><?=htmlspecialchars($_POST['answer'])?></strong>.</div><?php
			}
			$equation_html = 'ERROR';
			if ($random < 1 / 3) {
				# just addition
				if ($random2 < 1 / 5) {
					$equation_html = 'Add: 1664 + 1664';
					$_SESSION['calc_answer'] = 3328;
				} else {
					$num1 = rand(1, 50);
					$num2 = rand(1, 50);
					$equation_html = "Add: $num1 + $num2";
					$_SESSION['calc_answer'] = $num1 + $num2;
				}
			} else if ($random < 2 / 3) {
				# ooh ur in for an OKAY ride
				$has_ln = $random2 < 0.7;
				if ($has_ln) {
					# d/dx ln|x| = 1/x, so x needs to be made up
					# of 2s and 5s to make the answer a terminating decimal
					$x = 2 ** (rand(0, 2)) * 5 ** (rand(0, 2));
				} else {
					$x = rand(-4, 4); # too easy
				}
				$degree = rand(2, 4);
				$coefficients = array();
				$equation_elements = array();
				$_SESSION['calc_answer'] = 0;
				for ($i = 0; $i <= $degree; $i++) {
					$coefficient = rand(-15, 15);
					array_unshift($coefficients, $coefficient);
					if ($i == 0 || ($coefficient != 1 && $coefficient != -1)) $term = $coefficient;
					else $term = '';
					if ($i) $term .= '<i>x</i>';
					if ($i > 1) $term .= "<sup>$i</sup>";
					if ($coefficient != 0 || $i == 0) array_unshift($equation_elements, $term);
					if ($i != 0) $_SESSION['calc_answer'] += $coefficient * $i * ($x ** ($i - 1));
				}
				$special_coefficient = rand(1, 15);
				if ($random3 < 0.5) $special_coefficient *= -1;
				$term = '';
				if ($special_coefficient != 1 && $special_coefficient != -1) $term = $special_coefficient;
				if ($has_ln) {
					$term .= 'ln(<i>x</i>)';
					$equation_elements[] = $term;
					$_SESSION['calc_answer'] += $special_coefficient / $x;
				}
				$equation_html = implode(' + ', $equation_elements);
				$equation_html = str_replace('+ -', '- ', $equation_html);
				$equation_html = 'Differentiate: <i><span class="frac"><sup>d</sup><span>&frasl;</span><sub>dx</sub></span></i> ' . $equation_html . " | <sub><i>x</i>=$x</sub>";
			} else {
				# A LOT OF PAIN!!!
				$degree = rand(2, 4);
				$terms = array();
				$coefficients = array();
				$_SESSION['calc_answer'] = 0;
				$one = rand(-4, 4);
				$two = rand(-4, 4);
				while ($one == $two) $two = rand(-4, 4);
				$upper = max($one, $two);
				$lower = min($one, $two);
				$equation_elements = array();
				for ($i = 0; $i <= $degree; $i++) {
					$coefficient = rand(-15, 15) * ($i + 1); # for integer coefficient when integrating
					array_unshift($coefficients, $coefficient);
					if ($coefficient != 1 && $coefficient != -1) $term = $coefficient;
					else $term = '';
					if ($i) $term .= '<i>x</i>';
					if ($i > 1) $term .= "<sup>$i</sup>";
					if ($coefficient != 0 || $i == 0) array_unshift($equation_elements, $term);
					$_SESSION['calc_answer'] += $coefficient / ($i + 1) * ($upper ** ($i + 1));
					$_SESSION['calc_answer'] -= $coefficient / ($i + 1) * ($lower ** ($i + 1));
				}
				$equation_html = implode(' + ', $equation_elements);
				$equation_html = str_replace('+ -', '- ', $equation_html);
				$equation_html = '<table style="display: inline;" cellspacing="0" cellpadding="0">' .
				'<tr><td align="left">' . $upper . '</td></tr><tr><td align="center"><code style="font-size: 35px;">∫</code> ' . $equation_html .  '' .'</td></tr><tr><td align="left">' . $lower . '<td></tr>' . 
				'</table>' . '</sup>';
			}
			$_SESSION['calc_html'] = $equation_html;
			?><p>This part of the site requires you to fill out the form below to access. Once you complete it, your account will never have to do this again.</p>
			<p>If you get a problem that you can't do, reload the page, and there will be a 1/3 chance you get an addition problem.</p>
			<p>(If it's any comfort, the answer is guaranteed to be an integer or a terminating decimal.)</p>
			<form method="post" class="table-display">
				<label>
					<span>No goofing off when you're not supposed to, ok?</span>
					<input type="checkbox" name="submit-calc" required="required" />
				</label>
				<label>
					<span>Solve this:</span>
					<span><?=$equation_html?></span>
				</label>
				<label>
					<span>Answer:</span>
					<input type="text" name="answer" required="required" />
				</label>
				<label>
					<span>Let's go!</span>
					<input type="submit" />
				</label>
			</form>
			<?php
			require_once 'footer.php';
			exit;
		}