		<h2>Inputs</h2>
		<table>
			<tr>
				<th>file</th>
				<td><?php echo $file; ?></td>
			</tr>
			<tr>
				<th>fileRoot</th>
				<td><?php echo $transcodeResults['debug']['fileRoot']; ?></td>
			</tr>
			<tr>
				<th>localStartPath</th>
				<td><?php echo $transcodeResults['debug']['localStartPath']; ?></td>
			</tr>
			<tr>
				<th>webStartPath</th>
				<td><?php echo $transcodeResults['debug']['webStartPath']; ?></td>
			</tr>
			<tr>
				<th>flacTranscodePath</th>
				<td><?php echo $transcodeResults['debug']['flacTranscodePath']; ?></td>
			</tr>

			<tr>
				<th>audio_delimited</th>
				<td><?php echo $transcodeResults['debug']['audio_delimited']; ?></td>
			</tr>
			<tr>
				<th>video_delimited</th>
				<td><?php echo $transcodeResults['debug']['video_delimited']; ?></td>
			</tr>
			<tr>
				<th>default_audio_format</th>
				<td><?php echo $transcodeResults['debug']['default_audio_format']; ?></td>
			</tr>
			<tr>
				<th>default_video_format</th>
				<td><?php echo $transcodeResults['debug']['default_video_format']; ?></td>
			</tr>
		</table>
		<br />
		<h2>Outputs</h2>
		<table>
			<tr>
				<th>localSourcePath</th>
				<td><?php echo $transcodeResults['debug']['localSourcePath']; ?></td>
			</tr>
			<tr>
				<th>newLocalFile</th>
				<td><?php echo $transcodeResults['debug']['newLocalFile']; ?></td>
			</tr>
			<tr>
				<th>webFile</th>
				<td><?php echo $transcodeResults['debug']['webFile']; ?></td>
			</tr>
			<tr>
				<th>cmd</th>
				<td><?php echo $transcodeResults['debug']['cmd']; ?></td>
			</tr>
			<tr>
				<th>newLocalFilePath</th>
				<td><?php echo $newLocalFilePath; ?></td>
			</tr>
		</table>