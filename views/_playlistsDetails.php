<table>
	<tr>
		<th>Artist</th>
		<th>Track</th>
		<th>Album</th>
		<th>Year</th>
		<td></td>
	</tr>
	<?php while (($playlistDetails_row = mysql_fetch_array( $playlistDetails_data )) != null) { ?>
		<tr>
			<td><?php echo $playlistDetails_row['artist']; ?></td>
			<td><?php echo $playlistDetails_row['track']; ?></td>
			<td><?php echo $playlistDetails_row['album']; ?></td>
			<td><?php echo $playlistDetails_row['year']; ?></td>
			<td><a href="?playlist=<?php echo $playlist; ?>&action=delete_by_playlist_id&playlist_id=<?php echo $playlistDetails_row['PLAYLIST_ID']; ?>">Delete</a></td>
		</td>
	<?php } ?>
</table>