<?php
$quadratic = [
	rand(-10, 10),
	rand(-10, 10),
	rand(-10, 10)
];
while ($quadratic[0] == 0 && $quadratic[1] == 0) {
	$quadratic = [
		rand(-10, 10),
		rand(-10, 10),
		rand(-10, 10)
	];
}
$_SESSION['quadratic'] = $quadratic;
$captcha_html = $quadratic[0] . '<i>x</i><sup>2</sup> + ' . $quadratic[1] . '<i>x</i> + ' . $quadratic[2];
$captcha_html = str_replace('+ -', '- ', $captcha_html);
$captcha_text = $_SESSION['quadratic'][0] . 'x^2+' . $_SESSION['quadratic'][1] . 'x+' . $_SESSION['quadratic'][2];
$captcha_text = str_replace('+-', '-', $captcha_text);