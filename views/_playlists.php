<h2>Playlists</h2>
<table>
	<?php while (($playlist_row = mysql_fetch_array( $playlist_data )) != null) { ?>
		<tr>
			<td><a href="?playlist=<?php echo $playlist_row['playlist_name']; ?>"><?php echo $playlist_row['playlist_name']; ?></a></td>
			<td><a href="zip.php?playlist=<?php echo $playlist_row['playlist_name']; ?>">Download</a></td>
			<td><a href="zip.php?playlist=<?php echo $playlist_row['playlist_name']; ?>&transcode=mp3">Download (mp3)</a></td>
		</tr>
	<?php } ?>
</table>