<?php
$title = 'Edit class grade weight';
$class_valid = false;
$data_path = file_get_contents("elements/DATA_PATH");
if (isset($_GET['class']) && !ctype_xdigit($_GET['class'])) $class_valid = false;
else $class_valid = true;
if ($class_valid && isset($_GET['class']) && is_file("$data_path/classes/" . $_GET['class'] . ".json")) {
	$class = json_decode(file_get_contents("$data_path/classes/" . $_GET['class'] . '.json'), false);
	$title = $class->name;
	if ($class->teacher) {
		$title .= " - " . $class->teacher;
	}
	$title .= ' - ';
	$title .= substr($_GET['class'], 0, 6);
} else if (!isset($_GET['class'])) {
	$title = 'Add a class';
	$class = new stdClass;
	$class->name = '';
	$class->teacher = '';
	$class->min_score = 92.5;
	$class->min_score_present = false;
	$class->remarks = '';
	$class->categories = array();
} else {
	$title = 'No class to edit';
	require_once 'elements/header.php';
	?><div class="error">Invalid or missing class ID to edit.</div><?php
	require_once 'elements/footer.php';
	exit();
}
if (isset($_POST['name'])) $class->name = $_POST['name'];
if (isset($_POST['teacher'])) $class->teacher = $_POST['teacher'];
if (isset($_POST['remarks'])) $class->remarks = $_POST['remarks'];
if (isset($_POST['target'])) {
	$class->min_score = $_POST['target'];
	$class->min_score_present = isset($_POST['has-target']);
}
if (isset($_POST['row-count'])) {
	$class->categories = array();
	for ($i = 0; $i < $_POST['row-count']; $i++) {
		if ($_POST["category-name-$i"] != '') {
			$class->categories[] = [$_POST["category-name-$i"], $_POST["category-weight-$i"]];
		}
	}
}
require_once 'elements/header.php';
$summary = '';
if (!isset($_GET['class'])) {
	$summary = "Created grade weights for new class";
	?><p>You are creating a new class. Remember:</p>
	<ul>
		<li>Please do not add classes without final exams.</li>
		<li>If the class has a grade weight, enter the percentages into the grade weight or points column. For example, 30% weight would be entered as 30. If the class has a grade weight, the total number of points in a category is irrelevant.</li>
		<li>If the class does not have a grade weight, look at the bottom of your Grades page, and insert the total number of points in the category instead.</li>
		<li>Remember to enter class names EXACTLY as it says on your schedule! Names and teachers CANNOT be changed after creation!</li>
		<li>In the Cooked-O-Meter, you target a grade to find what you need to get on the final. The 'target grade' section's number is a default for what grade to target. I recommend putting the grade required for getting into the next class.</li>
		<li>For developers: A hash of <code><strong>e3b0c4</strong>4298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</code> probably means trouble, as it is the sha256 of an empty string</li>
	</ul><?php
}
if (isset($_POST['summary'])) $summary = $_POST['summary'];
if (isset($_POST['submit'])) {
	$serialize = json_encode($class);
	$invalid = 0;
	if (isset($_GET['class'])) {
		$hash = $_GET['class'];
		if (is_file("$data_path/classes/" . $_GET['class'] . '.json')) {
			if (file_get_contents("$data_path/classes/" . $_GET['class'] . '.json') == $serialize) {
				$invalid = 1;
			}
		}
	} else $hash = substr(hash('sha256', $class->name . ' - ' . $class->teacher), 0, 6);
	if (is_file("$data_path/classes/$hash.json") && !isset($_GET['class'])) $invalid = 2;
	if ($invalid == 1) {
		?><div class="error">You changed nothing</div><?php
	} else if ($invalid == 2) {
		?><div class="error">A class with that hash already exists. <a href="meter.php?class=<?=htmlspecialchars($hash)?>" target="_blank">Click here to view.</a></div><?php
	} else {
		if (!isset($_GET['class'])) {
			$index = json_decode(file_get_contents("$data_path/class_index.json"));
			# previously array_push for the first 9 classes on test environment
			array_unshift($index, [$hash, $class->name, $class->teacher]);
			file_put_contents("$data_path/class_index.json", json_encode($index));
		}
		file_put_contents("$data_path/classes/$hash.json", json_encode($class));
		if (!is_dir("$data_path/revs/$hash")) mkdir("$data_path/revs/$hash");
		$rev = array(
			'time' => time(),
			'parent' => $hash,
			'class' => $class,
			'summary' => $summary,
			'user' => $_SESSION['username'],
			'user_hash' => $_SESSION['hash']
		);
		$time = time();
		if (is_file("$data_path/revs/$hash/$time.json")) {
			?><div class="error">Sorry, but someone submitted a revision or creation the same <em>second</em> you did. What are the odds? Please try again, and it should be fine.</div><?php
		} else {
			file_put_contents("$data_path/lastrev/$hash.json", json_encode($rev));
			file_put_contents("$data_path/revs/$hash/$time.json", json_encode($rev));
		}
		log_message("Class $hash(..." . substr(hash('sha256', $class->name . ' - ' . $class->teacher), 6, 300) . ') [' . $class->name . ' - ' . $class->teacher . '] created or edited (summ: ' . $summary . ')');
		header("Location: meter.php?class=$hash");
	}
}
?>
<form class="table-display" method="post">
	<h3>General information</h3>
	<label>
		<span>Class name:</span>
		<?php if (isset($_GET['class'])) { ?><strong><?=htmlspecialchars($class->name)?></strong><?php 
		} else { ?><input type="text" name="name" value="<?=htmlspecialchars($class->name)?>" required="required" /><?php } ?>
	</label>
	<label>
		<span>Teacher last name:</span>
		<?php if (isset($_GET['class'])) { ?><strong><?=htmlspecialchars($class->teacher)?></strong><?php 
		} else { ?><input type="text" name="teacher" value="<?=htmlspecialchars($class->teacher)?>" required="required" /><?php } ?>
	</label>
	<h3>Public remarks</h3>
	<label>
		<span>Remarks (publicly viewable and editable):</span>
		<textarea class="input-cell" cols="40" rows="10" name="remarks"><?=htmlspecialchars($class->remarks)?></textarea>
	</label>
	<h3>Target grade</h3>
	<label>
		<span>Include default target?</span>
		<input type="checkbox" name="has-target" <?php 
		if ($class->min_score_present) echo 'checked="checked" ';
		?>/>
	</label>
	<label>
		<span>Target grade</span>
		<input type="number" step="any" name="target" value="<?=htmlspecialchars($class->min_score)?>" />
	</label>
	<h3>Grade categories</h3>
	<?php 
	$lines_to_display = count($class->categories) + 5;
	if (isset($_POST['trim'])) $lines_to_display -= 5;
	if (isset($_POST['row-count']) && !isset($_POST['trim'])) {
		if (isset($_POST['moar'])) {
			$lines_to_display = $_POST['row-count'] + 5;
		} else $lines_to_display = $_POST['row-count'];
	}
	for ($i = 0; $i < $lines_to_display; $i++) {
		?><div class="table-row">
			<label class="no-table-row" style="display: table-cell;">
				<span class="hidden2eyes">Category name:</span>
				<input type="text" name="category-name-<?=$i?>" placeholder="Category name #<?=$i + 1?>" value="<?php 
				if (isset($class->categories[$i])) echo htmlspecialchars($class->categories[$i][0]);
				?>" />
			</label>
			<label class="no-table-row" style="display: table-cell;">
				<span class="hidden2eyes">Category weight:</span>
				<input type="number" step="any" name="category-weight-<?=$i?>" placeholder="Category weight #<?=$i + 1?>" value="<?php 
				if (isset($class->categories[$i])) echo htmlspecialchars($class->categories[$i][1]);
				?>" />
			</label>
		</div><?php
	}
	?>
	<input type="hidden" name="row-count" value="<?=$lines_to_display?>" />
	<label>
		<span>Need more rows?</span>
		<input type="submit" name="moar" value="More rows" />
	</label>
	<label>
		<span>Need <em>less</em> rows?</span>
		<span>Just blank the category name, and it won't count: <input class="no-input-cell" type="submit" name="trim" value="trim unneeded rows" /></span>
	</label>
	<h3>Save it!</h3>
	<label>
		<span>Edit summary:</span>
		<input type="text" name="summary" value="<?=htmlspecialchars($summary)?>" />
	</label>
	<label>
		<span>Let's go!</span>
		<input type="submit" name="submit" value="Submit" />
	</label>
</form>
<?php
require_once 'elements/footer.php';