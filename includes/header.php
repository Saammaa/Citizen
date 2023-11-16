<?php

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need("includes/head.php");

?>

<body>

<div id="body">
	<header class="p-header">
		<div class="p-header--logo">
			<a href="<?php $this->options->siteUrl(); ?>" class="p-header--button" title="访问首页">
				<img class="logo-icon" src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/roundSquare/git.svg"
					 alt="" aria-hidden="true" height="32" width="32">
			</a>
		</div>

		<div class="p-search p-header--item p-menu-checkbox-container">
			<input type="checkbox" id="p-search--checkbox" class="p-menu-checkbox" role="button"
				   aria-labelledby="p-search--buttonCheckbox" aria-haspopup="true">
			<div class="p-search--button p-header--button">
				<div class="p-search--buttonIcon p-header--buttonIcon">
					<div></div>
					<div></div>
					<div></div>
				</div>
				<label id="p-search--buttonCheckbox" class="p-menu-checkbox-button p-header--buttonCheckbox"
					   for="p-search--checkbox" title="切换搜索" aria-hidden="true">
					<span>切换搜索</span>
				</label>
			</div>
			<div role="search" class="p-search-box p-search--card p-menu-checkbox-target">
				<form method="post" action="" class="p-search--form highlighter" id="searchForm" autocomplete="off">
					<label for="searchInput"><span class="ui-icon material-icons">search</span></label>
					<input type="text" name="s" id="searchInput" size="32" placeholder="在此网站搜索..."/>
					<span class="ui-icon material-icons p-search--formButton">double_arrow<input type="submit" id="searchSubmit"/></span>
				</form>
			</div>
		</div>

		<div class="p-drawer p-header--item p-menu-checkbox-container">
			<input type="checkbox" id="p-drawer--checkbox" class="p-menu-checkbox"
				   role="button" aria-labelledby="p-drawer--buttonCheckbox" aria-haspopup="true">
			<div class="p-drawer--button p-header--button">
				<div class="p-drawer--buttonIcon p-header--buttonIcon">
					<div></div>
					<div></div>
					<div></div>
				</div>
				<label id="p-drawer--buttonCheckbox" class="p-menu-checkbox-button p-header--buttonCheckbox"
					   for="p-drawer--checkbox" title="切换菜单" aria-hidden="true">
					<span>切换菜单</span>
				</label>
			</div>
			<aside id="p-drawer--card" class="p-drawer--card p-menu-checkbox-target">
				<header class="p-drawer--header">
					<a href="<?php $this->options->siteUrl(); ?>" class="p-drawer--logo" title="访问首页">
						<img class="logo-icon" src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/roundSquare/git.svg"
							 alt="" aria-hidden="true" height="80" width="80" loading="lazy">
					</a>
					<div class="p-drawer--siteInfo">
						<div class="logo-wordMark">
							<?php $this->options->title() ?>
						</div>
					</div>
				</header>
				<section class="p-drawer--menu">
					<nav id="navigation" class="p-menu portlet-navigation">
						<?php $this->need('includes/navigation.html'); ?>
					</nav>
				</section>
				<div id="player"></div>
			</aside>
		</div>

		<div class="p-drawer p-header--item p-menu-checkbox-container">
			<input type="checkbox" id="p-player--checkbox" class="p-menu-checkbox"
				   role="button" aria-labelledby="p-player--buttonCheckbox" aria-haspopup="true">
			<div class="p-player--entry p-header--button p-button">
				<span class="ui-icon material-icons">speaker</span>
				<label id="p-player--buttonCheckbox" class="p-menu-checkbox-button p-header--buttonCheckbox"
					   for="p-player--checkbox" title="打开播放器" aria-hidden="true">
					<span>打开播放器</span>
				</label>
			</div>
			<aside id="p-player--card" class="p-player--card p-drawer--card p-menu-checkbox-target">
				<?php $this->need('includes/player.php'); ?>
			</aside>
		</div>

		<div id="loading-indicator" class="bar-loader"></div>

		<div class="p-header--inner">
			<div class="p-header--start"></div>
			<div class="p-header--end">
				<div id="backToTop" class="p-drawer p-header--item">
					<div class="js-scrollButtons progress-wrap hidden">
						<svg class="progress-circle svg-content"
							 width="80%" height="100%"
							 viewBox="-12.5 -12.5 125 125">
							<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
						</svg>
					</div>
				</div>

				<div id="theme-switch" class="theme-switcher p-header--item">
					<button id="theme-toggle" class="p-header--button p-button" title="切换主题">
						<span class="ui-icon material-icons p-header--buttonIcon">wb_incandescent</span>
					</button>
				</div>
			</div>
		</div>
	</header>

	<div class="container">
		<div class="player-indicator"><img src="" alt="音乐播放器指示器" width="100" height="100" /></div>