<?php
$title = 'How cooked am I?';
require_once 'elements/library.php';
$class_valid = false;
if (!isset($_GET['class']) || !ctype_xdigit($_GET['class']) || !is_file("$data_path/classes/" . $_GET['class'] . '.json')) $class_valid = false;
else $class_valid = true;
if ($class_valid) {
	$class = json_decode(file_get_contents("$data_path/classes/" . $_GET['class'] . '.json'));
	$title = $class->name;
	if ($class->teacher) {
		$title .= " - " . $class->teacher;
	}
	$title .= ' - ';
	$title .= substr($_GET['class'], 0, 6);
} else $class = null;
require_once 'elements/header.php';
if (!$class_valid) {
	$title = 'Choose a class';
	?><p>You did not select a valid class. Choose one and click to open in the Cooked-O-Meter.</p>
	<form class="table-display">
		<label>
			<span>Choose a class from the list:</span>
			<select name="class">
				<?php 
				$index = json_decode(file_get_contents("$data_path/class_index.json"));
				foreach ($index as $class) {
					?><option value ="<?=htmlspecialchars($class[0])?>"><?=htmlspecialchars(substr($class[0], 0, 6) . ' - ' . $class[1] . ' - ' . $class[2])?></option><?php
				}
				?>
			</select>
		</label>
		<label>
			<span>View class:</span>
			<input type="submit" value="Go!" />
		</label>
	</form><?php
} else {
	$hash = $_GET['class'];
	$default_category = 0;
	$index = 0;
	foreach ($class->categories as $cat) {
		if (str_contains(strtolower($cat[0]), 'final') ||
		    str_contains(strtolower($cat[0]), 'exam')) $default_category = $index;
		$index++;
	}

	if (isset($_POST['category'])) {
		if (ctype_digit($_POST['category']) &&
		    $_POST['category'] < count($class->categories)) $default_category = $_POST['category'];
	}
	$data = array(
		'scores' => array(),
		'category'=> $default_category,
		'target' => $class->min_score ? $class->min_score : null,
		'remarks' => ''
	);
	while (count($data['scores']) < count($class->categories)) array_push($data['scores'], null);
	$user_id = $_SESSION['hash'];
	if (is_file("$data_path/saves/$user_id/$hash.json")) {
		$decode = json_decode(file_get_contents("$data_path/saves/$user_id/$hash.json"), true);
		if ($decode != NULL) $data = $decode;
	}
	if (isset($_POST['categories'])) {
		for ($i = 0; $i < $_POST['categories']; $i++) {
			if (isset($_POST["field-$i"]) && is_numeric($_POST["field-$i"])) $data['scores'][$i] = (float) $_POST["field-$i"];
			else $data['scores'][$i] = null;
		}
	}
	if (isset($_POST['target'])) $data['target'] = $_POST['target'];
	if (isset($_POST['remarks'])) $data['remarks'] = $_POST['remarks'];
	file_put_contents("$data_path/saves/$user_id/$hash.json", json_encode($data));
	$total_weight = 0;
	$total_points = 0;
	$total_points_without_final = 0;
	$total_weight_without_final = 0;
	$i = 0;
	$final_weight = 0;
	foreach ($class->categories as $cat) {
		if (isset($data['scores'][$i])) {
			$total_points += $cat[1] * ((float) $data['scores'][$i]) / 100;
			$total_weight += $cat[1];
			if ($data['category'] != $i) {
				# not the final!
				$total_points_without_final += $cat[1] * ((float) $data['scores'][$i]) / 100;
				$total_weight_without_final += $cat[1];
			}
		}
		if ($data['category'] == $i) $final_weight = $cat[1];
		$i++;
	}
	if ($total_weight != 0) {
		$total_score = $total_points / $total_weight * 100;
		$points_needed = ($total_weight_without_final + $final_weight) * $data['target'] / 100;
		$final_points = $points_needed - $total_points_without_final;
		if ($final_weight != 0) {
			$final_score = $final_points / $final_weight * 100;
		} else $final_score = null;
	} else {
		$total_score = 0;
		$final_score = null;
	}
	if ($total_weight_without_final != 0) $score_without_final = $total_points_without_final / $total_weight_without_final * 100;
	else $score_without_final = null;
	?>
	<nav>
		<ul>
			<li><a href="index.php">home</a></li>
			<li><a href="class_index.php">index (slow)</a></li>
			<li><a href="edit.php?class=<?php echo htmlspecialchars($_GET['class']); ?>">edit</a></li>
			<li><strong>test</strong></li>
			<li><a href="edit.php">create</a></li>
		</ul>
	</nav>
	<?php
	if (is_file("$data_path/lastrev/" . $_GET['class'] . '.json')) {
		$lastrev = json_decode(file_get_contents("$data_path/lastrev/" . $_GET['class'] . '.json'));
		?><p>Last edited by <strong><?=htmlspecialchars($lastrev->user)?></strong> on <strong><?=date("F j, Y, g:i:s A ", $lastrev->time) . 'UTC'?></strong> - <strong><?=htmlspecialchars($lastrev->summary)?></strong></p><?php
	}
	if ($class->remarks) {
		?><p>Other users added information:</p>
		<pre><?=htmlspecialchars($class->remarks)?></pre>
		<p>Notice anything wrong? Edit!</p><?php
	}
	?>
	<form method="post">
		<label>Select the category of the final exam:
			<select name="category">
				<?php
				$index = 0;
				foreach ($class->categories as $cat) {
					?><option value="<?=$index?>"<?php 
						if ($index == $default_category) { ?> selected="selected"<?php }
					?>><?=htmlspecialchars($cat[0])?></option>
					<?php
					$index++;
				}
				?>
			</select>
		</label>
		<table>
			<tr>
				<th>Category</th>
				<th>Total Points or Weight</th>
				<th>Grade (%)</th>
				<th>System Remarks</th>
			</tr>
			<?php 
			$index = 0;
			$sum = 0;
			foreach ($class->categories as $cat) {
				?><tr>
					<td><label for="field-<?=$index?>"><?=htmlspecialchars($cat[0])?></label></td>
					<td><?=htmlspecialchars($cat[1])?></td>
					<td><input type="number" step="any" id="field-<?=$index?>" name="field-<?=$index?>" <?php 
					if ((count($data['scores']) - 1) >= $index && $data['scores'][$index] !== null) {
						?>value="<?=htmlspecialchars($data['scores'][$index])?>" <?php
					}
					?>/></td>
					<td>
						<?php 
						if ($index == $data['category']) {
							if ($final_score != null) {
								?>Required <strong><?=ceil($final_score * 100) / 100?>%</strong><?php
							} else {
								?>???<?php
							}
						}
						?>
					</td>
				</tr><?php
				$sum += $cat[1];
				$index++;
			}
			?>
			<tr>
				<td>[total]</td>
				<td><?=$sum?></td>
				<td><strong><?php 
				if ($total_score != null) echo floor($total_score * 100) / 100 . '%';
				else echo '???';
				?></strong></td>
			</tr>
			<tr>
				<td>[target]</td>
				<td>---</td>
				<td>
					<label>
						<span class="hidden2eyes">Target score: </span>
						<input type="number" name="target" value="<?=htmlspecialchars($data['target'] === null ? '' : $data['target'])?>" step="any" />
					</label>
				</td>
				<td><?php 
				if (!$class->min_score_present) {
					?>No default (92.5+ is A)<?php
				} else {
					?>Default: <?=htmlspecialchars($class->min_score)?><?php
				}
				?></td>
			</tr>
		</table>
		<input type="hidden" name="categories" value="<?=count($class->categories)?>" />
		<label>
			You can add notes for yourself here:<br />
			<textarea name="remarks" cols="30" rows="6"><?php echo htmlspecialchars($data['remarks']); ?></textarea>
		</label><br />
		<input type="submit" value="Let's go!" />
	</form>
	<?php 
	if ($final_score != null && $score_without_final != null) {
		?>
		<h2>COOKED scale</h2>
		<p>Non-linear scale that represents the difference between your current score
		in the class and the score you need to get on the final to reach your score target. <a href="https://www.desmos.com/calculator/qjqhybzqq8">The precise curve can be seen here.</a></p><p>
		<?php
		$cooked_amount = 2 * (1 - sqrt(max(0, 10 - $final_score + $score_without_final) / 20));
		?>Your current situation scores a <strong><?php echo round($cooked_amount * 100, 2);
		if ($cooked_amount == 2) echo '+'; ?></strong>% on the Cooked-O-Meter. <?php 
		if ($cooked_amount == 2) echo "You're so cooked, I can't even compute your score beyond that!";
		?></p><?php
		$cooked_score = 100 * $cooked_amount;
		$class_name = urlencode($class->name . ' - ' . $class->teacher);
		$image_url = "elements/cooked_o_meter_image.php?name=$class_name&score=$cooked_score";
		?><img src="<?=htmlspecialchars($image_url)?>" alt="Cooked-O-Meter diagram illustrating the number above" />
		<p>Share the pain!
		<?php
		make_x_link("现在如果我想要在" . $class_name . "课里得到" . $data['target'] . "分，" .
			    "我需要在期末考试上得到" . ceil(100 * $final_score) / 100 . 
			    "分。我要被cooked了！Cooked-O-Meter说我百分之" . 
			    ceil($cooked_score * 100) / 100 . "被cooked。", "Tweet (simplified)");
		echo ' ';
		make_x_link("現在如果我想要在" . $class_name . "课里得到" . $data['target'] . "分，" .
			    "我需要在期末考試上得到" . ceil(100 * $final_score) / 100 . 
			    "分。我要被cooked了！Cooked-O-Meter說我百分之" . 
			    ceil($cooked_score * 100) / 100 . "被cooked。", "Tweet (traditional)");
		?></p><?php
	}
	?>
	<?php
}
require_once 'elements/footer.php';