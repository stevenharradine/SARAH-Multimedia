<?php
	require_once '../../views/_secureHead.php';
	require_once '../../models/_header.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		// empty strings (for use in js, thats why theres nested strings)
		$audio_file_types = '""';
		$video_file_types = '""';
	
		$musicManager = new MusicManager ();
	
		$playlist_data = $musicManager->getPlaylists ();
		$audio_file_types = $musicManager->getAudioFormats ();
		$video_file_types = $musicManager->getVideoFormats ();
	
		$script  = '<script>';
		$script .= '	var audio_file_types =' . $audio_file_types . ';';
		$script .= '	var video_file_types =' . $video_file_types . ';';
		$script .= '</script>';
		$script .= '<script src="playlist.js.php' . ( isset ($_REQUEST['playlist']) ? '?playlist=' . $_REQUEST['playlist'] : '' ) . ( isset ( $_REQUEST['album'] ) ? '?album=' . $_REQUEST['album'] : '' ) . ( isset ( $_REQUEST['artist'] ) ? '?artist=' . $_REQUEST['artist'] : '' ) . ( isset ( $_REQUEST['track']) ? '?track=' . $_REQUEST['track'] : '' ) . '"></script>';

		$headerView = new HeaderView ('Music');
		$headerView->setScript ($script);
		$headerView->setAltMenu ('<a class="playlist-builder" href="playlist-builder.php">Playlist builder</a>');
		$headerView->setLink('<link rel="stylesheet" type="text/css" href="css/player.css" />');

		$views_to_load = array();
		$views_to_load[] = '_player.php';
		
		include '../../views/_generic.php';
	}