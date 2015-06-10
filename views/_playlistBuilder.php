<?php if ($prev_page >= 0) { ?>
		<a href="?page=<?php echo $prev_page . $searchResults; ?>">Previous</a>
<?php } ?>		
		<a href="?page=<?php echo $next_page . $searchResults; ?>">Next</a>

	<table>
		<tr>
			<th></th>
			<th>#ID</th>
			<th>Artist</th>
			<th>Album</th>
			<th>Track</th>
			<th>Year</th>
			<th></th>
		</tr>
	<form action="playlist-builder.php" method="POST">
		<tr>
			<td></td>
			<td></td>
			<td><input type="text" name="artist" <?php echo (isset ($_REQUEST['artist']) ? " value=\"" . $_REQUEST['artist'] . "\" " : ""); ?> /></td>
			<td><input type="text" name="album" <?php echo (isset ($_REQUEST['album']) ? " value=\"" . $_REQUEST['album'] . "\" " : ""); ?> /></td>
			<td><input type="text" name="track" <?php echo (isset ($_REQUEST['track']) ? " value=\"" . $_REQUEST['track'] . "\" " : ""); ?> /></td>
			<td><input type="text" name="year" <?php echo (isset ($_REQUEST['year']) ? " value=\"" . $_REQUEST['year'] . "\" " : ""); ?> /></td>
			<td><input type="submit" /></td>
		</tr>
	</form>
	<?php while (($music_row = mysql_fetch_array( $music_data )) != null) { ?>
		<tr>
			<td><a class="addToPlaylist" href="#" data-id="<?php echo $music_row['MUSIC_ID']; ?>">Add</a></td>
			<td><a target="_blank" href="<?php echo endsWith ($music_row['path'], 'flac') ? 'transcode.php?file=' : ''; ?><?php echo $music_row['path']; ?>"><?php echo $music_row['MUSIC_ID']; ?></a></td>
			<td><a href="index.php?artist=<?php echo $music_row['MUSIC_ID']; ?>"><?php echo $music_row['artist']; ?></a></td>
			<td><img src="<?php echo $music_row['cover']; ?>" style="height: 16px;" /><a href="index.php?album=<?php echo $music_row['MUSIC_ID']; ?>"><?php echo $music_row['album']; ?></a></td>
			<td><a href="index.php?track=<?php echo $music_row['MUSIC_ID']; ?>"><?php echo $music_row['track']; ?></a></td>
			<td><?php echo $music_row['year']; ?></td>
			<td><?php echo $sessionManager->getUserType() == 'ADMIN' ? '<a class="edit" href="edit.php?id=' . $music_row['MUSIC_ID'] . '">Edit</a>' : ''; ?></td>
		</tr>
	<?php } ?>
	</table>