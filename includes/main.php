<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<div class="p-body-container" id="main" role="main">
	<?php if ($this->is('archive')) { if ($this->is('category')) { ?>
		<header class="p-category-header p-body-header u-fadeUp">
			<div class="page-heading">
				<div class="firstHeading-container">
					<h1 id="firstHeading" class="firstHeading">
						<?php $this->archiveTitle('%s', '', ''); ?>
					</h1>
				</div>
				<div class="p-description">
					<?php echo $this->getDescription(); ?>
				</div>
			</div>
		</header>
	<?php } elseif ($this->is('tag')) { ?>
		<h3 class="p-tag---title"><?php $this->archiveTitle('%s', '', ''); ?></h3>
	<?php } else { ?>
		<h1 class="archive-title">
			<?php $this->archiveTitle(array(
				'category' => _t('分类 - %s'),
				'search'   => _t('搜索 - %s'),
				'tag'      => _t('标签 - %s'),
				'author'   => _t('%s 发布的文章')
			), '', ''); ?>
		</h1>
	<?php }} $this->need('includes/posts.php') ?>
</div>