<?php
$title = 'Create an account';
$viewable_without_login = true;
require_once 'elements/header.php';
$signup_error = 0;
function check_signup() {
	$data_path = file_get_contents(__DIR__ . '/elements/DATA_PATH');
	$code = $_POST['signup_code'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	# 'cooked'
	if (!password_verify($code, '$2y$10$P472Zs0UksKAcqMqnOBXJ.CUgEfuUkmbeWLaCH3QDJ7bXGuh/YeXO')) {
		?><div class="error">Invalid secret code.</div><?php
		return 1;
	}
	if (check_captcha() != 0) return 2;
	if ($password != $password2) {
		?><div class="error">You gave me two different passwords.</div><?php
		return 3;
	}
	$username_hash = hash('sha256', $username);
	if (is_file("$data_path/users/$username_hash.json")) {
		?><div class="error">Someone with the same username or a colliding hash username already exists. You either entered an existing user, or you just witnessed an event <a href="https://stackoverflow.com/a/4014407/15578194">unimaginably unlikely.</a></div><?php
		return 4;
	}
	$user = array(
		"password" => password_hash($password, PASSWORD_DEFAULT),
		"username" => $username,
		"created" => time()
	);
	mkdir("$data_path/saves/$username_hash");
	file_put_contents("$data_path/users/$username_hash.json", json_encode($user));
	log_message("User $username ($username_hash) created");
	account_login($username_hash);
	header('Location: /index.php');
	return 0;
}
if (isset($_POST['signup'])) $signup_error = check_signup();

require_once 'elements/captcha_calculation.php';

?>
I recommend making your username the same as your (school) email (i.e. first name, last name, graduation year) or twitter username so people know who you are. Besides, you can't even view this without an account, which you need a secret code from the developer to create
<form class="table-display" method="post">
	<label>
		<span>Username:</span>
		<input type="text" name="username" required="required" value="<?php 
		if (isset($_POST['username']) && $signup_error != 4) echo htmlspecialchars($_POST['username']);
		?>" />
		<?php if ($signup_error == 4) { ?><span class="bold-red"><<< Bad data was entered here!</span><?php } ?>
	</label>
	<label>
		<span>Password:</span>
		<input type="password" name="password" required="required" value="<?php 
		if (isset($_POST['password']) && $signup_error != 3) echo htmlspecialchars($_POST['password']);
		?>" autocomplete="new-password" />
		<?php if ($signup_error == 3) { ?><span class="bold-red"><<< Bad data was entered here!</span><?php } ?>
	</label>
	<label>
		<span>Password again:</span>
		<input type="password" name="password2" required="required" value="<?php 
		if (isset($_POST['password2']) && $signup_error != 3) echo htmlspecialchars($_POST['password2']);
		?>" />
		<?php if ($signup_error == 3) { ?><span class="bold-red"><<< Bad data was entered here!</span><?php } ?>
	</label>
	<label>
		<span>Secret signup code:</span>
		<input type="text" name="signup_code" required="required" value="<?php 
		if (isset($_POST['signup_code']) && $signup_error != 1) echo htmlspecialchars($_POST['signup_code']);
		?>" />
		<?php if ($signup_error == 1) { ?><span class="bold-red"><<< Bad data was entered here!</span><?php } ?>
	</label>
	<label>
		<span>CAPTCHA:</span>
		<span class="input-cell">
			(<input type="number" name="captcha" class="no-input-cell" size="3" /> 
			plus minus 
			<input type="number" name="captcha2" class="no-input-cell" size="3" value="1" />
			<select name="captcha_imaginary">
				<option value="0"> </option>
				<option value="1">i</option>
			</select>
			<select name="captcha_sqrt">
				<option value="1">sqrt(</option>
				<option value="0">    (</option>
			</select>
			<input type="number" name="captcha3" class="no-input-cell" size="3" />)) / 
			<input type="number" name="captcha4" class="no-input-cell" size="3" />
		</span>
	</label>
<?php
if ($_SESSION['no_captcha']) {
	?><label>
		<span>I like to solve quadratics</span>
		<input type="checkbox" name="captcha_required" />
	</label>
	<input type="hidden" name="captcha_inhibit" value="0" />
	<?php
} else { ?>
	<label>
		<span>Don't bug me again with math</span>
		<input type="checkbox" name="captcha_inhibit" checked="true" />
	</label>
<input type="hidden" name="captcha_required" value="1" />
<?php } ?>
<input type="hidden" name="signup" value="1" />
<input type="submit" value="Create account!" />
</form>
<details>
	<summary>I don't get how to enter this CAPTCHA</summary>
	<table>
		<tr>
			<th>Math</th>
			<th>CAPTCHA input</th>
		</tr>
		<tr>
			<td><img src="captcha_examples/example1.png" alt="(1 plus minus 2sqrt3) / 5" /></td>
			<td>
				(<kbd class="fake-input">1</kbd> 
				plus minus 
				<kbd class="fake-input">2</kbd>
				<kbd class="fake-select">&nbsp;</kbd>
				<kbd class="fake-select">sqrt(</kbd>
				<kbd class="fake-input">3</kbd>)) / 
				<kbd class="fake-input">5</kbd>
			</td>
		</tr>
		<tr>
			<td><img src="captcha_examples/example2.png" alt="(1 plus minus 2) / 5" /></td>
			<td>
				(<kbd class="fake-input">1</kbd> 
				plus minus 
				<kbd class="fake-input">2</kbd>
				<kbd class="fake-select">&nbsp;</kbd>
				<kbd class="fake-select">&nbsp;&nbsp;&nbsp;&nbsp;(</kbd>
				<kbd class="fake-input">&nbsp;</kbd>)) / 
				<kbd class="fake-input">5</kbd>
			</td>
		</tr>
		<tr>
			<td><img src="captcha_examples/example3.png" alt="(1 plus minus 2i) / 5" /></td>
			<td>
				(<kbd class="fake-input">1</kbd> 
				plus minus 
				<kbd class="fake-input">2</kbd>
				<kbd class="fake-select">i</kbd>
				<kbd class="fake-select">sqrt(</kbd>
				<kbd class="fake-input">1</kbd>)) / 
				<kbd class="fake-input">5</kbd>
			</td>
		</tr>
		<tr>
			<td><img src="captcha_examples/example4.png" alt="(1 plus minus 2isqrt3) / 5" /></td>
			<td>
				(<kbd class="fake-input">1</kbd> 
				plus minus 
				<kbd class="fake-input">2</kbd>
				<kbd class="fake-select">i</kbd>
				<kbd class="fake-select">sqrt(</kbd>
				<kbd class="fake-input">3</kbd>)) / 
				<kbd class="fake-input">5</kbd>
			</td>
		</tr>
		<tr>
			<td><img src="captcha_examples/example5.png" alt="1 plus minus 2" /></td>
			<td>
				(<kbd class="fake-input">1</kbd> 
				plus minus 
				<kbd class="fake-input">2</kbd>
				<kbd class="fake-select">&nbsp;</kbd>
				<kbd class="fake-select">&nbsp;&nbsp;&nbsp;&nbsp;(</kbd>
				<kbd class="fake-input">&nbsp;</kbd>)) / 
				<kbd class="fake-input">1</kbd>
			</td>
		</tr>
		<tr>
			<td><img src="captcha_examples/example6.png" alt="1" /></td>
			<td>
				(<kbd class="fake-input">1</kbd> 
				plus minus 
				<kbd class="fake-input">&nbsp;</kbd>
				<kbd class="fake-select">&nbsp;</kbd>
				<kbd class="fake-select">&nbsp;&nbsp;&nbsp;&nbsp;(</kbd>
				<kbd class="fake-input">&nbsp;</kbd>)) / 
				<kbd class="fake-input">1</kbd>
			</td>
		</tr>
	</table>
</details>
<?php
if ($_SESSION['no_captcha']) {
	?><p>Welcome back! Solving the CAPTCHA is not required since you already successfully completed a captcha in this browser session. Browser sessions typically expire upon closing the browser. Check the box above if you still want to be nagged if you put in a wrong or missing answer.</p><?php
} else { ?>
<p>If you solve this CAPTCHA successfully and tick the checkbox again, you will not be required to solve it again this browser session. Browser sessions typically expire upon closing the browser.</p>
<p>To prove your name is not Cesar M, please find the roots of this quadratic and enter everything above. It does not need to be simplified. Equation:</p>
<?php } ?>
<img src="elements/captcha_image.php" alt="<?=$captcha_text?>" />
<details>
	<summary>看不懂这个照片！！！看不懂這個照片！！！</summary>
	<?=$captcha_html?>
</details>
<?php
require_once 'elements/footer.php';