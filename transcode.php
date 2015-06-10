<?php
	require_once '../../views/_secureHead.php';
	require_once '../../models/_header.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$file				= request_isset('file');
		$transcodeTo		= request_isset('transcodeTo', null);
		$debug				= request_isset('debug');	// prevents redirection to mp3 after conversion

		$transcodeResults = MusicManager::transcode ($file, $transcodeTo);

		$webFile = $transcodeResults['webFile'];
		
		if (!$debug) {
			header("location: $webFile");
		} else {
			$headerView = new HeaderView ('Transcoder | Music');

			$views_to_load = array();
			$views_to_load[] = '_transcode.php';
			
			include '../../views/_generic.php';
		}
	}