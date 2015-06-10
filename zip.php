<?php
	set_time_limit(0);

	require_once '../../libs/zip.lib.php';
	require_once '../../views/_secureHead.php';
	require_once '../../models/_header.php';
	require_once '../../models/_table.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$playlist = request_isset ('playlist');
		$debug = request_isset ('debug');
		$transcode = request_isset ('transcode');

		$settings = MusicManager::getSettings();
		$fileRoot = $settings['fileRoot'];
		$localStartPath = $settings['localStartPath'];
		$webStartPath = $settings['webStartPath'];
		$flacTranscodePath = $settings['flacTranscodePath'];
		$audio_file_types = $settings['audio_file_types'];
		$video_file_types = $settings['video_file_types'];
		$default_audio_format = $settings['default_audio_format'];
		$default_video_format = $settings['default_video_format'];

		$playlistDetails_data = MusicManager::getPlaylist($playlist);

		$fileListTracing = '';

		$zip = new zipfile();	//create the zip

		while (($playlistDetails_row = mysql_fetch_array( $playlistDetails_data )) != null) {
			if (isset ($transcode)) {
				$transcodeResults = MusicManager::transcode ($playlistDetails_row['path'], $transcode);

				$cmd = $transcodeResults['cmd'];

				$path = $transcodeResults['newLocalFile'];
			} else {
				$path = str_replace (
					$webStartPath,
					$localStartPath,
					str_replace ("%20"," ",$playlistDetails_row['path'])
				);
			}
			$filename = getFilename ($path);

			$zip->addFile(file_get_contents($path), $filename);
			$fileListTracing .= "$path | $filename<br />\n";
		}

		if (!$debug) {
			// Zip file header
			header('Content-Type: application/octet-stream');
			header('Content-disposition: attachment; filename="' . $playlist . '.zip"');

			echo $zip->file();
		} else {
			$audio_file_types_string = implode ($audio_file_types, '; ');
			$video_file_types_string = implode ($video_file_types, '; ');

			echo <<<EOD
	<table>
		<tr>
			<td>fileRoot</td>
			<td>$fileRoot</td>
		</tr>
		<tr>
			<td>localStartPath</td>
			<td>$localStartPath</td>
		</tr>
		<tr>
			<td>webStartPath</td>
			<td>$webStartPath</td>
		</tr>
		<tr>
			<td>flacTranscodePath</td>
			<td>$flacTranscodePath</td>
		</tr>
		<tr>
			<td>audio_file_types</td>
			<td>$audio_file_types_string</td>
		</tr>
		<tr>
			<td>video_file_types</td>
			<td>$video_file_types_string</td>
		</tr>
		<tr>
			<td>default_audio_format</td>
			<td>$default_audio_format</td>
		</tr>
		<tr>
			<td>default_video_format</td>
			<td>$default_video_format</td>
		</tr>
		<tr>
			<td>fileListTracing</td>
			<td>$fileListTracing</td>
		</tr>
		<tr>
			<td>cmd</td>
			<td>$cmd</td>
		</tr>
	</table>
EOD;
		}
	}

	function getFilename ($string) {
		$exploded = explode('/', $string);

		return $exploded[count($exploded) - 1];
	}