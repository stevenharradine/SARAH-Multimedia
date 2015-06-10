<?php
	require_once '../../views/_secureHead.php';
	require_once '../../models/_header.php';
	require_once '../../models/_table.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$RESULTS_PER_PAGE	= 50;
		$playlist = request_isset ('playlist');
		$playlist_id = request_isset ('playlist_id');
		$page = request_isset ('page', 0);
		$ID = request_isset ('id');
		$artist = request_isset ('artist');
		$album = request_isset ('album');
		$track = request_isset ('track');
		$track_no = request_isset ('track_no');
		$year = request_isset ('year');
		$cover = request_isset ('cover');
		$prev_page = $page - 1;
		$next_page = $page + 1;

		$musicManager = new MusicManager ();

		$searchResults =	(isset ($_REQUEST['artist']) && $_REQUEST['artist'] != '' ? '&artist=' . $_REQUEST['artist'] : '') . 
	 						(isset ($_REQUEST['album']) && $_REQUEST['album'] != '' ? '&album=' . $_REQUEST['album'] : '') . 
	 						(isset ($_REQUEST['track']) && $_REQUEST['track'] != '' ? '&track=' . $_REQUEST['track'] : '') .
	 						(isset ($_REQUEST['year']) && $_REQUEST['year'] != '' ? '&year=' . $_REQUEST['year'] : '');
	
		switch ($page_action) {
			case ('add_song_to_playlist')	:	$db_add_success = $musicManager->addSongToPlaylist ($RESULTS_PER_PAGE);
												break;
			case ('delete_by_playlist_id')	:	$db_delete_success = $musicManager->deletePlaylistById ($playlist_id);
												break;
			case ('update_by_id')			:	$db_update_success = $musicManager->updateSong ($ID, $artist, $album, $track, $track_no, $year, $cover);
												break;
		}

		$music_data = $musicManager->getMusic ($RESULTS_PER_PAGE);
		$playlist_data = $musicManager->getPlaylists();
		$playlistDetails_data = $musicManager->getPlaylist($playlist);

		$headerView = new HeaderView ('Playlist bulder | Music');
		$headerView->setAltMenu ('<a class="playlist-builder" href=".">&lt; Music</a>');

		$views_to_load = array();
		$views_to_load[] = '_addCover.php';
		$views_to_load[] = '_playlists.php';
		if ($playlist != null) {
			$views_to_load[] = '_playlistsDetails.php';
		}
		$views_to_load[] = '_playlistBuilder.php';
		
		include '../../views/_generic.php';
?>


<?php
	}
	
	function issetAndDefined ($var) {
		return isset ($_REQUEST['artist']) ? ($_REQUEST['artist'] != "" ? "`artist` LIKE '%" . $_REQUEST['artist'] . "%'" : "") : "";
	}