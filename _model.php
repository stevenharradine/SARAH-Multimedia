	<?php
		class MusicManager {
			private function getPlayableFormat ( $type ) {
				$sql_musicFiletypes = "SELECT `key`, `value` FROM `settings` WHERE `key`='music_$type'";
				$musicFiletypes_data = mysql_query($sql_musicFiletypes) or die(mysql_error());
				
				while (($musicFiletypes_row = mysql_fetch_array( $musicFiletypes_data )) != null) {
					$key = $musicFiletypes_row['key'];
					$value = $musicFiletypes_row['value'];
					
					if ($key == "music_$type") {
						return delimitedToJSArray (';', $value);
					}
				}
			}

			public function getAudioFormats () {
				return $this->getPlayableFormat ('audio');
			}

			public function getVideoFormats () {
				return $this->getPlayableFormat ('video');
			}

			public function getRecord ($id) {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				$sql = "SELECT * FROM `music` WHERE `MUSIC_ID`='$id';";
				$data = mysql_query($sql) or die(mysql_error());

				return mysql_fetch_array( $data );
			}

			public function getSettings () {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				$sql = <<<EOD
	SELECT
		`key`,
		`value`
	FROM
		`settings`
	WHERE
		`key`='music_fileRoot'
			OR
		`key`='music_localStartPath'
			OR
		`key`='music_webStartPath'
			OR
		`key`='music_flacTranscodePath'
			OR
		`key`='music_audio'
			OR
		`key`='music_video'
			OR
		`key`='music_default_audio'
			OR
		`key`='music_default_video'
EOD;
				$data = mysql_query( $sql ) or die(mysql_error());

				while (($settings_row = mysql_fetch_array( $data )) != null) {
					$key = $settings_row['key'];
					$value = $settings_row['value'];
					
					if ($key == 'music_fileRoot') {
						$fileRoot = $value;
					} else if ($key == 'music_localStartPath') {
						$localStartPath = $value;
					} else if ($key == 'music_webStartPath') {
						$webStartPath = $value;
					} else if ($key == 'music_flacTranscodePath') {
						$flacTranscodePath = $value;
					} else if ($key == 'music_audio') {
						$audio_delimited = $value;
						
						$audio_file_types = explode (';', $audio_delimited);
					} else if ($key == 'music_video') {
						$video_delimited = $value;
						
						$video_file_types = explode (';', $video_delimited);
					} else if ($key == 'music_default_audio') {
						$default_audio_format = $value;
					} else if ($key == 'music_default_video') {
						$default_video_format = $value;
					}
				}

				return array (
					'fileRoot' => $fileRoot,
					'localStartPath' => $localStartPath,
					'webStartPath' => $webStartPath,
					'flacTranscodePath' => $flacTranscodePath,
					'audio_file_types' => $audio_file_types,
					'video_file_types' => $video_file_types,
					'default_audio_format' => $default_audio_format,
					'default_video_format' => $default_video_format
				);
			}

			public function getPlaylists () {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				$sql_playlist = "SELECT DISTINCT `playlist_name` FROM `music_playlist` WHERE `USER_ID`='$USER_ID';";
				$playlist_data = mysql_query($sql_playlist) or die(mysql_error());

				return $playlist_data;
			}

			public function getPlaylist ($playlist_name) {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				$sql_playlist = <<<EOD
SELECT *
FROM `music_playlist`
JOIN `music`
ON `music_playlist`.MUSIC_ID = `music`.MUSIC_ID
WHERE `playlist_name` =  '$playlist_name'
AND `USER_ID` =  '$USER_ID'
EOD;
				$playlist_data = mysql_query($sql_playlist) or die(mysql_error());

				return $playlist_data;
			}

			public function getPlaylistNameById ($id) {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				$sql_playlist = <<<EOD
SELECT `playlist_name`
FROM `sarah`.`music_playlist`
WHERE `music_playlist`.`PLAYLIST_ID` = '$id'
AND `USER_ID` =  '$USER_ID'
EOD;

				$playlist_data = mysql_query($sql_playlist) or die(mysql_error());
				$playlist_row = mysql_fetch_array( $playlist_data );

				return $playlist_row['playlist_name'];
			}

			public function deletePlaylistById ($id) {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				$playlist_name = $this->getPlaylistNameById ($id);

				$sql_deleteplaylist = <<<EOD
DELETE FROM `sarah`.`music_playlist`
WHERE `music_playlist`.`PLAYLIST_ID` = '$id'
AND `USER_ID` =  '$USER_ID'
EOD;

				$deleteplaylist_data = mysql_query($sql_deleteplaylist) or die(mysql_error());
				$this->writePlaylist ($playlist_name);

				return $deleteplaylist_data;
			}

			private function getWhere () {
				$where = '';
				if ( (isset ($_REQUEST['path']) || isset ($_REQUEST['artist']) || isset ($_REQUEST['album']) || isset ($_REQUEST['track']) || isset ($_REQUEST['year']) ) && $_REQUEST['action'] != 'update_by_id' ) {
					$where =	'WHERE ' .
								(isset ($_REQUEST['artist']) ? ($_REQUEST['artist'] != "" ? "`artist` LIKE '%" . $_REQUEST['artist'] . "%' AND" : "") : "") .
								(isset ($_REQUEST['album']) ? ($_REQUEST['album'] != "" ? "`album` LIKE '%" . $_REQUEST['album'] . "%' AND" : "") : "") .
								(isset ($_REQUEST['track']) ? ($_REQUEST['track'] != "" ? "`track` LIKE '%" . $_REQUEST['track'] . "%' AND" : "") : "") .
								(isset ($_REQUEST['year']) ? ($_REQUEST['year'] != "" ? "`year` LIKE '%" . $_REQUEST['year'] . "%' AND" : "") : "");
					$where = rtrim ($where, " AND");
				}

				return $where;
			}
			private function getLimit ($RESULTS_PER_PAGE) {
				$page = isset ($_REQUEST['page']) ? $_REQUEST['page'] : 0;

				return 'LIMIT ' . $RESULTS_PER_PAGE * $page . ", $RESULTS_PER_PAGE";
			}
			public function updateSong ($ID, $artist, $album, $track, $track_no, $year, $cover) {
				global $sessionManager;

				// only allow ADMIN to invoke this function
				if ($sessionManager->getUserType() == 'ADMIN') {
					$artist = str_replace('\'', '\\\'', $artist);
					$album = str_replace('\'', '\\\'', $album);
					$track = str_replace('\'', '\\\'', $track);
					$track_no = str_replace('\'', '\\\'', $track_no);
					$year = str_replace('\'', '\\\'', $year);
					$cover = str_replace('\'', '\\\'', $cover);

					$sql = <<<EOD
UPDATE  `sarah`.`music`
SET `artist` =  '$artist',
	`album` =  '$album',
	`track` =  '$track',
	`track_no` =  '$track_no',
	`year` =  '$year',
	`cover` =  '$cover'
WHERE  `music`.`MUSIC_ID` =$ID;
EOD;
					
					return mysql_query($sql) or die(mysql_error());
				}
			}
			public function getMusic ($RESULTS_PER_PAGE) {
				$where = $this->getWhere();
				$limit = $this->getLimit($RESULTS_PER_PAGE);
				
				$sql = "SELECT * FROM `music` $where ORDER BY `artist`, `year`, `album`, `track` $limit";
				$music_data = mysql_query($sql) or die(mysql_error());

				return $music_data;
			}

			public function addSongToPlaylist ($RESULTS_PER_PAGE) {
				global $sessionManager;
				$USER_ID = $sessionManager->getUserId();

				if (isset ($_REQUEST['playlistName']) && isset ($_REQUEST['id'])) {
					$playlist_name = $_REQUEST['playlistName'];
					$music_id = $_REQUEST['id'];

					$where = $this->getWhere();
					$limit = $this->getLimit($RESULTS_PER_PAGE);
					
					$sql = "SELECT * FROM `music` " . $where . " ORDER BY `artist`, `year`, `album`, `track` $limit";
					$db_write_success = mysql_query("INSERT INTO  `sarah`.`music_playlist` (`USER_ID` ,`MUSIC_ID`, `playlist_name`)VALUES ('$USER_ID', '$music_id', '$playlist_name');");

					$this->writePlaylist ($playlist_name);
				}
			}

			public function writePlaylist ($playlist_name) {
				global $sessionManager;
				$username = $sessionManager->getUserName();

				$playlist_data = $this->getPlaylist ($playlist_name);
				$root_path = '/home/douglas/media/Music/Playlists/' . $username .'/';	// TODO pull from db

				mkdir ($root_path, 0777, true);

				$handle = fopen($root_path . $playlist_name . '.m3u', 'w');

				while (($playlist_row = mysql_fetch_array( $playlist_data )) != null) {
					// if path uses protocalless link insert http to construct a valid link then convert all %20 to spaces.
					// TODO: find a way to detect if HTTPS is avilable from server (note this file is not always accessed via browser, xbmc for example)
					$path = (beginsWith ($playlist_row['path'], '//') ? 'http:' : '') . str_replace(' ', '%20', $playlist_row['path']);

					fwrite ($handle, "$path\n");
				}

				fclose ($handle);
			}

			public function writeAllPlaylists () {
				global $sessionManager;
				$username = $sessionManager->getUserName();
				$playlists_data = $this->getPlaylists ();

				while (($playlists_row = mysql_fetch_array( $playlists_data )) != null) {
					$playlist_name = $playlists_row['playlist_name'];

					$this->writePlaylist ($playlist_name);
				}
			}
			public function transcode ($file, $transcodeTo=null, $BIT_RATE='128k') {
				$file = str_replace ("%20"," ",$file);
				$NUM_OF_CORES		= "2";						// TODO: put in DB

				$settings = MusicManager::getSettings();
				$fileRoot = $settings['fileRoot'];
				$localStartPath = $settings['localStartPath'];
				$webStartPath = $settings['webStartPath'];
				$flacTranscodePath = $settings['flacTranscodePath'];
				$audio_file_types = $settings['audio_file_types'];
				$video_file_types = $settings['video_file_types'];
				$default_audio_format = $settings['default_audio_format'];
				$default_video_format = $settings['default_video_format'];

				if ($transcodeTo != null) {
					$default_audio_format = $transcodeTo;
					$default_video_format = $transcodeTo;
				}

				if (isExtentionValid($file, $audio_file_types) == true || isExtentionValid ($transcodeTo, $audio_file_types) == true) {
					$newLocalFile = str_replace($webStartPath, $localStartPath . $flacTranscodePath, replace_extension ($file, $default_audio_format));
				} else {
					$newLocalFile = str_replace ($webStartPath, $localStartPath . $flacTranscodePath, replace_extension ($file, $default_video_format));
				}

				$newLocalFileForCmd = str_replace('\'', '\'"\'"\'', $newLocalFile); // allows for proper handleing of ' in file name with the cmd using single quotes for the expression

				$newLocalFilePath = substr ($newLocalFile, 0, strrpos ($newLocalFile, '/') + 1);
				$webFile = str_replace ($localStartPath, $webStartPath, $newLocalFile);

				$localSourcePath = str_replace ($webStartPath, $localStartPath, $file);
				$localSourcePath = str_replace('\'', '\'"\'"\'', $localSourcePath); // allows for proper handleing of ' in file name with the cmd using single quotes for the expression

				$cmd = "avconv -threads $NUM_OF_CORES -i '$localSourcePath' ";
				if (isExtentionValid ($file, $audio_file_types) == true || isExtentionValid ($transcodeTo, $audio_file_types) == true) {		// if audio
					$cmd .= "-ab $BIT_RATE";
				} else {														// if video (by means of not being audio via else statement)
					$cmd .= '-vcodec libx264';
				}
				$cmd .= " -map_metadata 0 '$newLocalFileForCmd'";
				
				if (!file_exists ($newLocalFilePath)) {
					mkdir ($newLocalFilePath, 0777, true);
				}
				exec ($cmd);

				return array (
					'webFile' => $webFile,
					'newLocalFile' => $newLocalFile,
					'cmd' => $cmd,
					'debug' => array (
						'fileRoot' => $fileRoot,
						'localStartPath' => $localStartPath,
						'webStartPath' => $webStartPath,
						'flacTranscodePath' => $flacTranscodePath,
						'audio_delimited' => $audio_file_types,
						'video_delimited' => $audio_file_types,
						'default_audio_format' => $default_audio_format,
						'default_video_format' => $default_video_format,
						'localSourcePath' => $localSourcePath,
						'newLocalFile' => $newLocalFile,
						'webFile' => $webFile,
						'cmd' => $cmd,
						'newLocalFilePath' => $newLocalFilePath
					)
				);
			}
		}