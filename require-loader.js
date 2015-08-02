require ({
		baseUrl: "../../js/"
	},[
		"jquery-1.6.2.min",
		"endsWith"
	], function ($, ew) {
		require({
			baseUrl: "../../js/"
		}, [
			"navigation",
			"add",
			"edit",
			"replaceAll"
		], function( 
			nav,
			add,
			edit,
			replaceAll
		) {
			jQuery ("a.addToPlaylist").bind ("click", function () {
				var playlistName = prompt("Enter playlist name:","");
				
				jQuery.get('playlist-builder.php?action=add_song_to_playlist&playlistName=' + playlistName + '&id=' + jQuery(this).attr("data-id"), function(data) {
					jQuery('.result').html(data);
					container.children(".description").prepend(data);
					container.removeClass("new");
				});
				
				return false;
			});

			var playlist_size	= Object.keys(playlist).length;
			var playlist_index	= 0;
			
			update_player (playlist_index);

			function volumeUp (e) {
				jQuery ("#volume").val(parseFloat (jQuery ("#volume").val()) + parseFloat (jQuery ("#volume").attr("step")));
				update_volume ();
				e.preventDefault();
			}

			function volumeDown (e) {
				jQuery ("#volume").val(parseFloat (jQuery ("#volume").val()) - parseFloat (jQuery ("#volume").attr("step")));
				update_volume ();
				e.preventDefault();
			}
			
			jQuery ("html").keyup(function (e) {
				var code = e.keyCode || e.which;
				
				showControls ();

				switch (code) {
					case 32:
						jQuery (".controls a.pauseplay").click();
						e.preventDefault();
						break;
					case 70:
						jQuery ("#fullScreen").click();
						e.preventDefault();
						break;
					case 78:
						jQuery (".controls a.next").click();
						e.preventDefault();
						break;
					case 80:
						jQuery (".controls a.prev").click();
						e.preventDefault();
						break;
					case 82:
						jQuery (".controls a.repeat").click();
						e.preventDefault();
						break;
					case 83:
						jQuery (".controls a.shuffle").click();
						e.preventDefault();
						break;
					default:
						console.log (code);
				}
			}).keydown (function (e) {
				var code = e.keyCode || e.which;

				showControls ();

				switch (code) {
					case 107: 	// volume up
						volumeUp (e);
						break;
					case 109: 	// volume down
						volumeDown (e);
						break;
					case 187: 	// volume up
						volumeUp (e);
						break;
					case 189: 	// volume down
						volumeDown (e);
						break;
					default:
						console.log (code);
				}
			});
			
			jQuery (playlist).each(function (index) {
				jQuery (".track-selection").append("<option value=\"" + index + "\">" + playlist[index].artist + " - " + playlist[index].track + "</option>");
			});
			
			jQuery (".playlist-selection").bind ("change", function () {
				var playlist_path = 'playlist.js.php?json=json&playlist=' + jQuery(this).val();
				
				var xhReq = new XMLHttpRequest();
				xhReq.open("GET", playlist_path, false);
				xhReq.send(null);
				
				playlist = jQuery.parseJSON (xhReq.responseText);
				
				playlist_size	= Object.keys(playlist).length;
				playlist_index	= 0;
				update_player (playlist_index);
				
				jQuery (".track-selection").html("");
				jQuery (playlist).each(function (index) {
					jQuery (".track-selection").append("<option value=\"" + index + "\">" + playlist[index].artist + " - " + playlist[index].track + "</option>");
				});
			});

			/* Show/Hide controls */
			var controlsTimeout     = 2000;
			var controlsTimeoutLock = false;

			jQuery (".content").mousemove (function( event ) {
				showControls ();
			});
			jQuery (".content").click (function( event ) {
				showControls ();
			});

			function showControls () {
				controlsTimeout = 2000;

				jQuery ("body").addClass ("showControls");

				countDownControlsTimeout (false);
			}
			function countDownControlsTimeout (overrideLock) {
				if (overrideLock || !controlsTimeoutLock) {
					controlsTimeoutLock = true;

					if (controlsTimeout <= 0) {
						jQuery ("body").removeClass ("showControls");
						controlsTimeoutLock = false;
					} else {
						var timeoutCheck = 500;

						setTimeout(function () {
							controlsTimeout -= timeoutCheck;
							countDownControlsTimeout (true)
						}, timeoutCheck);
					}
				}
			}

			jQuery (".controls a.prev").bind ("click", function () {
				prev_track();
				
				return false;	// stop links href from firing when clicked
			});
			jQuery (".controls a.next").bind ("click", function () {
				next_track();
				
				return false;	// stop links href from firing when clicked
			});
			jQuery (".controls a.pauseplay").bind ("click", function () {
				var player = document.getElementById("player_actual");
				
				if (player.paused) {
					player.play();
					jQuery (this).html ("Pause");
				} else {
					player.pause();
					jQuery (this).html ("Play");
				}
				
				return false;	// stop links href from firing when clicked
			});
			jQuery (".controls a.shuffle").bind ("click", function () {
				// use selector not 'this' because we want to update all instances, not just this clicked instance
				var shuffle = jQuery (".controls a.shuffle span").html();
				
				if (shuffle == "On") {
					jQuery (".controls a.shuffle span").html("Off");
					jQuery (".controls a.repeat").removeAttr ("disabled");
					jQuery (".controls a.shuffle").removeClass ("full");
				} else if (shuffle == "Off") {
					jQuery (".controls a.shuffle span").html("On");
					jQuery (".controls a.repeat").attr ("disabled", "disabled");
					jQuery (".controls a.shuffle").addClass ("full");
				}
				
				return false;	// stop links href from firing when clicked
			});
			jQuery ("img.cover").bind ("click", function () {
				jQuery (this).toggleClass ("large");
			});
			jQuery (".controls a.repeat").bind ("click", function () {
				// use selector not 'this' because we want to update all instances, not just this clicked instance
				var repeat = jQuery (".controls a.repeat span").html();
				
				if (repeat == "Off") {
					jQuery (".controls a.repeat span").html("One");
				} else if (repeat == "One") {
					jQuery (".controls a.repeat span").html("All");
				} else if (repeat == "All") {
					jQuery (".controls a.repeat span").html("Off");
				}
				
				return false;	// stop links href from firing when clicked
			});
			jQuery ('#progressBar').bind("click", function (event) {
				var player = document.getElementById("player_actual");
				var track_length_str= jQuery (".track-length").html().split(':');
				var containerX = jQuery (this).position().left;
				
				// set player time = (the percent across the progress bar clicked) * (lenth of the current track in seconds)
				player.currentTime = (event.pageX / (parseInt(jQuery(this).css("width")))) * ((parseInt(track_length_str[0]) * 60) + parseInt(track_length_str[1]));
			});
			jQuery ("#volume").bind ("change", function () {
				update_volume();
			});
			jQuery (".track-selection").bind ("change", function () {
				update_player(jQuery (this).val());
			});

			jQuery ("#fullScreen").bind ("change", function () {
				if (jQuery(this).attr("checked") == "checked") {
					jQuery ("body").addClass ("forceFullscreen");
				} else {
					jQuery ("body").removeClass ("forceFullscreen");
				}
			});

			jQuery ("#audioOnly").bind ("change", function () {
				update_player_with_time (playlist_index, document.getElementById("player_actual").currentTime);

				if (this.checked)
					jQuery ("body").addClass ("forceAudio")
				else
					jQuery ("body").removeClass ("forceAudio")
			});
			
			function next_track () {
				if (jQuery (".controls a.shuffle span").html() == "Off") {
					var repeat = jQuery (".controls a.repeat span").html();
					
					if (repeat == "All") {
						update_player((++playlist_index) % playlist_size);
					} else if (repeat == "Off") {
						update_player((++playlist_index));
					} else if (repeat == "One") {
						update_player(playlist_index);
					}
				} else {	// shuffle "On"
					update_player( Math.floor( Math.random() * playlist_size ) );
				}
			}
			function prev_track () {
				if (jQuery (".controls a.shuffle span").html() == "Off") {
					var repeat		= jQuery (".controls a.repeat span").html();
					var prev_track	= playlist_index - 1;
					
					if (repeat == "All") {
						update_player (playlist_index = prev_track < 0 ? playlist_size - 1 : prev_track);
					} else if (repeat == "Off") {
						update_player(--playlist_index);
					} else if (repeat == "One") {
						update_player(playlist_index);
					}
				} else {	// shuffle "On"
					update_player( Math.floor( Math.random() * playlist_size ) );
				}
			}
			
			/*
			 * isExtentionValid (String path, String[] extentions)
			 * returns boolean, does the path end with an extention in the extentions array?
			 * 
			 * requires:
			 * 		String.prototype.endsWith = function (suffix) {
			 * 			return this.indexOf(suffix, this.length - suffix.length) !== -1;
			 * 		};
			 */
			function isExtentionValid (path, extentions) {
				for (var i = 0; i <= extentions.length - 1; i++) {
					if (path.toLowerCase().endsWith (extentions[i].toLowerCase())) {
						return true;
					}
				}
				
				return false;
			}
			
			function update_player_with_time (index, scrubToTime) {
				var artist	= playlist[index].artist;
				var track	= playlist[index].track;
				var path	= playlist[index].path;
				var cover	= playlist[index].cover == undefined ? "#" : playlist[index].cover;
				var player;

				var isAudioOnly     = jQuery ("#audioOnly").attr("checked") == "checked";
				
				playlist_index = index;

				if (isAudioOnly && !path.endsWith(".mp3")) {
					path = "transcode.php?transcodeTo=mp3&file=" + path.replaceAll (" ", "%20");
				}
				
				update_track_list ();
				jQuery(".track-selection option[value='" + index + "']").attr("selected", "selected");
				
				var tag = ""

				if (isExtentionValid (path, video_file_types) && !isAudioOnly) {
					tag = "video";

					jQuery ("img.cover").hide();

					jQuery ("body").addClass("video").removeClass("audio");
				} else if (isExtentionValid (path, audio_file_types) || isAudioOnly) {
					tag = "audio";
					
					jQuery ("img.cover").show();

					jQuery ("body").addClass("audio").removeClass("video");
				}

				jQuery ("#player").html('<' + tag + ' id="player_actual" autoplay="autoplay"><source src="' + path + '" /></' + tag + '>');
				player = document.getElementById("player_actual");
				player.addEventListener('ended', function () {
					next_track();
				}, false);
				
				update_volume();	// persist volume level when changing tracks
				
				jQuery (".artist").html(artist);
				jQuery (".track").html(track);
				jQuery ("img.cover").attr("src", cover);
				
				jQuery("#player_actual").bind ("timeupdate", function () {
					var progress = document.getElementById("progress");
					var value = 0;
					if (player.currentTime > 0) {
						value = (100 / player.duration) * player.currentTime;
						jQuery (".track-length").html( seconds2Minutes (player.duration) );
					}
					progress.style.width = value + "%";
					jQuery (".track-current-position").html( seconds2Minutes (player.currentTime) );
					if (player.duration - player.currentTime < 0.2) {
						console.log ("Ended");
				//		next_track();
					}
				//	console.log (player.duration - player.currentTime);
				});
				
				jQuery ("span.playlist_index").html(index);

				player.currentTime = scrubToTime;

			}

			function update_player(index) {
				update_player_with_time (index, 0);
			}
			function update_track_list () {
				jQuery (".track-selection option").removeAttr("selected");
				jQuery (".track-selection option[value='" + playlist_index + "']").attr("selected", "selected");
			}
			function update_volume() {
				var volume = document.getElementById("volume");
				document.getElementById("player_actual").volume = volume.value == "" ? volume.value = 0.5 : volume.value;
			}
			
			function seconds2Minutes (seconds) {
				return leadingZero (Math.floor(seconds / 60)) + ":" + leadingZero (Math.floor(seconds % 60));
			}
			function leadingZero (value) {
				return value < 10 ? '0' + value : value
			}
	});
});
