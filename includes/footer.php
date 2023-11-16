<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

</div>
</div>

<footer class="p-footer" id="footer" role="contentinfo">
	<canvas id="playerVisualizer" class="p-player--canvas" width="800" height="500"></canvas>

	<div class="p-footer--container">
		<section class="p-footer--content">
			<div class="p-footer--siteInfo">
				<div id="footer-siteTitle" class="global-title">
					<div class="logo-wordMark"><?php $this->options->title() ?></div>
				</div>
				<p id="footer-desc">你可以在 includes 文件夹中的 footer.php 中修改页脚内容。</p>
			</div>
			<nav id="footer-places">
				<ul>
					<li><a href="https://typecho.org" target="_blank">官网</a></li>
					<li><a href="https://saammaa.com/citizen" target="_blank">公民</a></li>
					<li><a href="https://starcitizen.tools" target="_blank">StarCitizen</a></li>
					<li><a href="https://www.npmjs.com/package/seamworks-statics" target="_blank">NPM</a></li>
					<li><a href="https://github.com/Saammaa/Citizen" target="_blank">GitHub</a></li>	
				</ul>
			</nav>
		</section>

		<section class="p-footer--bottom">
			<div id="footer-tagline">
				<a href="#">❤ SPONSORED BY NAVTOREY</a> | ©1999-2023 TonyStarkIndustries. All Rights Reserved.
			</div>

			<nav id="footer-icons">
				<ul><li id="footer-poweredByIcon">
						<a href="https://typecho.org" target="_blank" title="基于 Typecho">
							<img src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/logo/typecho.svg"
								 alt="基于 Typecho 1.2.1" width="110" height="50" loading="lazy">
						</a></li>
					<li id="footer-copyrightIcon">
						<a href="https://creativecommons.org/licenses/by-nc-sa/4.0" target="_blank" title="CC-BY-NC-SA 4.0">
							<img src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/logo/cc_by_sa.svg"
								 alt="知识共享署名-非商业性使用-相同方式共享" width="110" height="50" loading="lazy">
						</a></li>
					<li id="footer-platformIcon">
						<a href="https://unpkg.com" target="_blank" title="Great Thanks to NPM">
							<img src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/logo/npm.svg"
								 alt="NPM LogoIcon" width="50" height="50" loading="lazy" style="filter: invert(1)">
						</a></li>
				</ul>
			</nav>
		</section>
	</div>
</footer>

<footer id="script">
	<?php Citizen::footer(); $this->footer(); ?>
</footer>

</body>
</html>