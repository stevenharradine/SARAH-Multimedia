<?php
session_start();
require_once '../../global.php';

if( isset ($_SESSION['usertype']) && $_SESSION['usertype'] == "ADMIN") {
	$USER_ID = $_SESSION['USER_ID'];
	
	$webStartRoot = '';
	$localStartRoot = '';
	$coverIndex = '';
	$coversPath = '';

	$artist = request_isset ('artist');
	$album = request_isset ('album');
	$track = request_isset ('track');
	$year = request_isset ('year');
	
	DB_Connect($DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME);
	
	$sql =	'SELECT `key`, `value` FROM `settings` WHERE ' .
			'`key`=\'music_localStartPath\' OR ' .
			'`key`=\'music_webStartPath\' OR ' .
			'`key`=\'music_coverIndex\' OR ' .
			'`key`=\'music_coversPath\'';
	$setings_data = mysql_query($sql) or die(mysql_error());
	
	while (($settings_row = mysql_fetch_array( $setings_data )) != null) {
		$key = $settings_row['key'];
		$value = $settings_row['value'];
		
		if ($key == 'music_localStartPath') {
			$localStartRoot = $value;
		} else if ($key == 'music_webStartPath') {
			$webStartRoot = $value;
		} else if ($key == 'music_coverIndex') {
			$coverIndex = $value;
		} else if ($key == 'music_coversPath') {
			$coversPath = $value;
		}
	}
	
	if (isset ($_REQUEST['path']) || isset ($_REQUEST['artist']) || isset ($_REQUEST['album']) || isset ($_REQUEST['track']) || isset ($_REQUEST['year'])) {
		$where =	"WHERE " .
					(isset ($_REQUEST['artist']) ? ($_REQUEST['artist'] != "" ? "`artist` LIKE '%" . $_REQUEST['artist'] . "%' AND" : "") : "") .
					(isset ($_REQUEST['album']) ? ($_REQUEST['album'] != "" ? "`album` LIKE '%" . $_REQUEST['album'] . "%' AND" : "") : "") .
					(isset ($_REQUEST['track']) ? ($_REQUEST['track'] != "" ? "`track` LIKE '%" . $_REQUEST['track'] . "%' AND" : "") : "") .
					(isset ($_REQUEST['year']) ? ($_REQUEST['year'] != "" ? "`year` LIKE '%" . $_REQUEST['year'] . "%' AND" : "") : "");
		$where = rtrim ($where, " AND");
	}
	
	$coverURL = request_isset ('coverURL');
	$coversPath = $localStartRoot . $coversPath;
//	$coversIndexBase36 = ;
//	$coversIndexBase36Padded = ;
	$coversExtention = substr ($coverURL, strrpos ($coverURL, '.'));
	$newCoverPath = $coversPath . '/' . $coverIndex . $coversExtention;
	$newCoverURL = str_replace ($localStartRoot, $webStartRoot, $newCoverPath);

	// escape
	$coverURL = str_replace ('(', '\\(', str_replace (')', '\\)', $coverURL));

	$cmd = "wget $coverURL -O $newCoverPath";
	exec ($cmd);

	$sql = "UPDATE `settings` SET `value`='" . ($coverIndex + 1) . "' WHERE `key`='music_coverIndex'";
//	echo $sql;
	$db_write_success = mysql_query($sql) or die(mysql_error());
	
	$sql = "UPDATE `music` SET `cover`='$newCoverURL' $where";
	echo $sql;
	$db_write_success = mysql_query($sql) or die(mysql_error());
	
	$sql = "SELECT * FROM `music` $where ORDER BY `artist`, `year`, `album`, `track`";
	//echo $sql;
	$music_data = mysql_query($sql) or die(mysql_error());

	header ("location: playlist-builder.php?artist=$artist&album=$album&track=$track&year=$year");
	
	$page_title = 'Add cover | Music';
	
	include '../../views/_header.php';
?>
		<h1>Inputs</h1>
		<table>
			<tr>
				<th>coversURL</th>
				<td><?php echo $coverURL; ?></td>
			</tr>
			<tr>
				<th>coversExtention</th>
				<td><?php echo $coversExtention; ?></td>
			</tr>
			<tr>
				<th>newCoversPath</th>
				<td><?php echo $newCoverPath; ?></td>
			</tr>
			<tr>
				<th>newCoverURL</th>
				<td><?php echo $newCoverURL; ?></td>
			</tr>
		</table>
<?php
		include '../../views/_footer.php';
	} else {
		require_once ('../../auth/login.php');
	}