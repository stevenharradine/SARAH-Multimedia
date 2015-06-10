<?php
session_start();
$relative_base_path = '../../';

require_once $relative_base_path . 'global.php';

if( isset ($_SESSION['usertype']) ) {
	$USER_ID = $_SESSION['USER_ID'];
	
	DB_Connect($DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME);
	
	$play_id = isset ($_REQUEST['album']) ? $_REQUEST['album'] : (isset ($_REQUEST['artist']) ? $_REQUEST['artist'] : (isset ($_REQUEST['playlist']) ? $_REQUEST['playlist'] : (isset ($_REQUEST['track']) ? $_REQUEST['track'] : null)));
	
	$is_json = isset ($_REQUEST['json']) ? false : true;
	
	if (isset ($_REQUEST['playlist'])) {
		$sql = "SELECT * FROM `music_playlist` JOIN `music` ON  `music_playlist`.MUSIC_ID = `music`.MUSIC_ID WHERE `USER_ID`='" . $_SESSION['USER_ID'] . "' AND `playlist_name`='" . $_REQUEST['playlist'] . "'";
	} else if (isset ($_REQUEST['album'])) {
		$sql = "SELECT `album` FROM `music` WHERE `MUSIC_ID`=" . $play_id;
		$album_data = mysql_query($sql) or die(mysql_error());
		$album_row = mysql_fetch_array( $album_data );
		
		$sql = "SELECT * FROM `music` WHERE `album`=\"" . $album_row['album'] . "\"";
	} else if (isset ($_REQUEST['artist'])) {
		$sql = "SELECT `artist` FROM `music` WHERE `MUSIC_ID`=" . $play_id;
		$artist_data = mysql_query($sql) or die(mysql_error());
		$artist_row = mysql_fetch_array( $artist_data );
		
		$sql = "SELECT * FROM `music` WHERE `artist`=\"" . $artist_row['artist'] . "\"";
	} else if (isset ($_REQUEST['track'])) {
		$sql = "SELECT `track` FROM `music` WHERE `MUSIC_ID`=" . $play_id;
		$artist_data = mysql_query($sql) or die(mysql_error());
		$artist_row = mysql_fetch_array( $artist_data );
		
		$sql = "SELECT * FROM `music` WHERE `track`=\"" . $artist_row['track'] . "\"";
	} else {
		$sql = "SELECT * FROM `music_playlist` JOIN `music` ON  `music_playlist`.MUSIC_ID = `music`.MUSIC_ID WHERE `USER_ID`='" . $_SESSION['USER_ID'] . "'";
	}
	//echo $sql;
	$music_data = mysql_query($sql) or die(mysql_error());
	
	$sql_settings =	'SELECT `key`, `value` FROM `settings` WHERE ' .
					'`key`=\'music_audio\' OR ' .
					'`key`=\'music_video\' OR ' .
					'`key`=\'music_default_audio\' OR ' .
					'`key`=\'music_default_video\'';
	$setings_data = mysql_query($sql_settings) or die(mysql_error());
	
	while (($settings_row = mysql_fetch_array( $setings_data )) != null) {
		$key = $settings_row['key'];
		$value = $settings_row['value'];
		
		if ($key == 'music_audio') {
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

	$pastFirstRow = false;
	
	if ($is_json) {
?>
var playlist =
<?php
	}
?>
[
<?php
	while (($music_data_row = mysql_fetch_array( $music_data )) != null) {
		$artist	= $music_data_row['artist'];
		$track	= $music_data_row['track'];
		$path	= $music_data_row['path'];
		$cover	= $music_data_row['cover'];
		
		if (!$pastFirstRow) {
			$pastFirstRow = true;
		} else {
			echo ',';
		}
?>
					{
						"artist": "<?php echo $artist; ?>",
						"track"	: "<?php echo $track; ?>",
						"path"	: "<?php if ((isExtentionValid ($path, $video_file_types) == true && !endsWith($path, ".$default_video_format")) || (isExtentionValid ($path, $audio_file_types) == true && !endsWith($path, ".$default_audio_format"))) {echo 'transcode.php?file=';} echo str_replace ( '&', '%26' , $path); ?>",
						"cover"	: "<?php echo $cover; ?>"
					}
<?php } ?>
				]
<?php
	} else {
		require_once ('../../auth/login.php');
	}
