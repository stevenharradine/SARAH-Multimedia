<?php
	require_once '../../views/_secureHead.php';
	require_once $relative_base_path . 'models/edit.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$MUSIC_ID = request_isset ('id');

		$musicManager = new MusicManager ();
		
		$record = $musicManager->getRecord ($MUSIC_ID);

		$page_title = 'Edit | Music';

		// build edit view
		$editModel = new EditModel ('Edit', 'update_by_id', $MUSIC_ID, 'playlist-builder.php');
		$editModel->addRow ('path', 'Path', $record['path'], 'readonly="readonly" dir="rtl"');
		$editModel->addRow ('artist', 'Artist', $record['artist'] );
		$editModel->addRow ('album', 'Album', $record['album'] );
		$editModel->addRow ('track', 'Track', $record['track'] );
		$editModel->addRow ('track_no', 'Track #', $record['track_no'] );
		$editModel->addRow ('year', 'Year', $record['year'] );
		$editModel->addRow ('cover', 'Cover', $record['cover'] );

		$views_to_load = array();
		$views_to_load[] = ' ' . EditView2::render($editModel);

		include $relative_base_path . 'views/_generic.php';
	}
?>