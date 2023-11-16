<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit ?>

<div class="p-player" data-playlist="">
	<div class="p-player--album">
		<img src="" alt="">

		<div class="p-player--info">
			<h1 id="songName"></h1>
			<h2 id="songArtist"></h2>
		</div>
	</div>

	<div class="p-player--actions">
		<button id="songShuffle">
			<span class="ui-icon material-icons">shuffle</span>
		</button>
		<button id="songPrevious">
			<span class="ui-icon material-icons">skip_previous</span>
		</button>
		<button id="playPause">
			<span class="ui-icon material-icons">play_arrow</span>
		</button>
		<button id="songNext">
			<span class="ui-icon material-icons">skip_next</span>
		</button>
		<button id="songRepeat">
			<span class="ui-icon material-icons">repeat_one</span>
		</button>
	</div>
</div>