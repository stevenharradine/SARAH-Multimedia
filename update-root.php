<?php
	session_start();
	require_once '../../global.php';

	if( isset ( $_SESSION['usertype'] ) ) {
		$USER_ID = $_SESSION['USER_ID'];
		
		DB_Connect($DB_ADDRESS, $DB_USER, $DB_PASS, $DB_NAME);
		
		$sql = "SELECT * FROM `music`;";
		//echo $sql;
		$music_data = mysql_query($sql) or die(mysql_error());
		
		$page_title = 'Update path | Music';
		
		include '../../views/_header.php';

		while ( ( $music_row = mysql_fetch_array ( $music_data ) ) != null) {
			echo $music_row['path'];
		}
		
		include '../../views/_footer.php';
	} else {
		require_once ('../../auth/login.php');
	}