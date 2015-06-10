<?php if ($_SESSION['usertype'] == "ADMIN") { ?>
	<section>
		<h2>Add Cover</h2>
		<form action="addCover.php" method="POST">
			<input type="hidden" name="artist" value="<?php echo (isset ($_REQUEST['artist']) ? $_REQUEST['artist'] : ""); ?>" />
			<input type="hidden" name="album" value="<?php echo (isset ($_REQUEST['album']) ? $_REQUEST['album'] : ""); ?>" />
			<input type="hidden" name="track" value="<?php echo (isset ($_REQUEST['track']) ? $_REQUEST['track'] : ""); ?>" />
			<input type="hidden" name="year" value="<?php echo (isset ($_REQUEST['year']) ? $_REQUEST['year'] : ""); ?>" />
			<input type="text" name="coverURL" />
			<input type="submit" value="Add cover" />
		</form>
	</section>
<?php } ?>