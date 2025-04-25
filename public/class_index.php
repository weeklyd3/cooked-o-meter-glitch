<?php
$title = 'Cooked-O-Meter Class Index';
require_once 'elements/header.php';
?>
<p>List of classes that have been added to the Cooked-O-Meter, with the newest first. You might want to use your browser's find function (in Safari, type it into the address bar and select "Find" on the bottom of the menu.)</p>
<p>I would like to emphasize that everything here is added by users. See anything wrong or missing? Add or fix it!</p>
<ul>
	<?php 
	$hash = $_SESSION['hash'];
	$dir = json_decode(file_get_contents("$data_path/class_index.json"));
	foreach ($dir as $entry) {
		?><li><a href="meter.php?class=<?=$entry[0]?>"><?=htmlspecialchars($entry[1])?> - <?=htmlspecialchars($entry[2])?></a></li><?php
	}
	?>
</ul>
<?php
require_once 'elements/footer.php';