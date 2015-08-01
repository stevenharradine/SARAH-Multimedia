			<div>
				<div id="player"></div>
				<img class="cover" src="#"/>
			</div>
			<div class="controls">
				<select class="track-selection"></select>
				<select class="playlist-selection">
					<option>Play all</option>
		<?php while (($playlist_row = mysql_fetch_array( $playlist_data )) != null) { ?>
					<option<?php echo (isset ($_REQUEST['playlist']) && ($playlist_row['playlist_name'] == $_REQUEST['playlist'])) ? ' selected="selected"' : ''; ?>><?php echo $playlist_row['playlist_name']; ?></option>
		<?php } ?>
				</select>
				<a href="#" class="prev">Prev</a><a href="#" class="pauseplay">Pause</a><a href="#" class="next">Next</a>
				<a href="#" class="shuffle">Shuffle: <span>Off</span></a><a href="#" class="repeat">Repeat: <span>Off</span></a>

				<div class="status">
					<div id="progressBar"><span id="progress"><span class="track-current-position"></span></span></div>
					<span class="track-length"></span>
				</div>

				<input id="volume" min="0" max="1" step="0.01" type="range" />
				<span>
					<input type="checkbox" name="audioOnly" id="audioOnly" />
					<label for="audioOnly">Audio Only</label>
				</span>
				<div class="fullScreen">
					<label for="fullScreen">Full Screen</label>
					<input type="checkbox" name="fullScreen" id="fullScreen" />
				</div>
			</div>