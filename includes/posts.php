<section class="<?php if (!$this->is('search')) { ?>p-grid<?php } ?> u-fadeUp">
	<?php if ($this->have()) { while ($this->next()): ?>
		<article class="p-post">
			<a href="<?php $this->permalink() ?>">
				<?php if($this->fields->thumbnail!=''): ?>
					<img class="lazy p-post--cover" src="<?php Utils::indexTheme('assets/loading.svg'); ?>"
						 data-src="<?php $this->fields->thumbnail(); ?>" alt="<?php $this->title() ?>">
				<?php endif;?>

				<div class="p-post--text">
					<h2 class="p-post--title" itemprop="name headline">
						<?php $this->title() ?>
					</h2>

					<p class="p-post--meta">
						<?php $this->fields->description(); ?>
					</p>

					<?php if ($this->is('search')): ?>
						<div class="p-snippet" itemprop="articleBody">
							<?php Citizen::fnExcerpt($this); ?>
						</div>
					<?php endif; ?>
				</div>
			</a>
		</article>
	<?php endwhile; } else { ?>
		<p>😶 空空如也，但你可以选择<a href="<?php Utils::indexHome() ?>">返回首页</a>。</p>
	<?php } ?>
</section>

<?php $this->pageNav('上一页', '下一页', '5', '……'); ?>