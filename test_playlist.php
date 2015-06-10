<?php
	require_once '../../views/_secureHead.php';
	require_once '../../models/_header.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$musicManager = new MusicManager ();

		$musicManager->writeAllPlaylists();

		$headerView = new HeaderView ('Playlist test | Music');

		$views_to_load = array();
//		$views_to_load[] = '_addCover.php';
		
		include '../../views/_generic.php';
?>


<?php
	}