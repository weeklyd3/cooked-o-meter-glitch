<?php
$title = 'Cooked-O-Meter Index';
require_once 'elements/header.php';
?>
<h2>Classes with saved grades</h2>
<ul>
	<?php 
	$hash = $_SESSION['hash'];
	$dir = opendir("$data_path/saves/$hash");
	$count = 0;
	while (false !== ($entry = readdir($dir))) {
		if ($entry == '.' || $entry == '..') continue;
		$file = json_decode(file_get_contents("$data_path/classes/$entry"));
		?><li><a href="meter.php?class=<?=substr($entry, 0, -5)?>"><?=htmlspecialchars($file->name)?> - <?=htmlspecialchars($file->teacher)?></a></li><?php
		$count++;
	}
	if ($count == 0) {
		?><li>You must be pretty new here. Go to the Class Index below and choose classes you're in to test your grades!</li><?php
	}
	?>
</ul>
<h2>All classes</h2>
<a href="class_index.php">Class index (may take a while to load)</a>
<h2>Add a class grade weight</h2>
<p>If you don't see one of your classes on this list, <a href="edit.php">add a class's grade weight!</a> This helps other students know how cooked they are on the finals.</p>
<?php
require_once 'elements/footer.php';