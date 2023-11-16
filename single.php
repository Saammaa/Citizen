<?php

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

if (isset($_POST['like']) && ($this->is('post') || $this->is('page'))) {
	if ($_POST['like'] == $this->cid) {
		exit($this->setField('allowLike', 'str', $this->fields->allowLike + 1, $this->cid));
	}
}

$this->need('includes/header.php');

?>

<div class="p-body-container" id="main" role="main">
	<?php if ($this->fields->thumbnail != ''): ?>
		<div class="p-cover u-fadeUp" style="--cover-source:url('<?php $this->fields->thumbnail(); ?>')"></div>
	<?php endif; ?>

	<header class="p-body-header u-fadeUp">
		<div class="page-heading" itemprop="name headline">
			<div class="firstHeading-container">
				<h1 id="firstHeading" class="firstHeading">
					<?php $this->title() ?>
				</h1>
			</div>

			<?php if ($this->fields->description != '' || $this->is('post')): ?>
				<div class="p-description">
					<?php $this->fields->description(); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="page-actions">
			<?php if ($this->fields->allowLike): ?>
			<a id="actionLike" data-cid="<?php echo $this->cid ?>" data-url="<?php $this->permalink() ?>"
			   class="p-like--pawButton button">
				<div class="p-like--text">
					<img src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/paw/likeHeart.svg" alt="小心心"/>
					<span>击爪</span>
				</div>
				<span id="likeCount"><?php $this->fields->allowLike() ?></span>
				<div class="p-like--paws">
					<img class="p-like--paw" alt="猫 JioJio"
						 src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/paw/likePaw.svg" />
					<div class="p-like--effect"><div></div></div>
					<img class="p-like--pawClap" alt="拍爪爪"
						 src="https://registry.npmmirror.com/seamworks-statics/latest/files/icons/paw/likePawClap.svg" />
				</div>
			</a>
			<?php endif ?>
		</div>
	</header>

	<div class="p-content u-fadeUp">
		<?php $this->content(); ?>
	</div>

	<?php if ($this->is('post')): ?>

	<footer class="p-body-footer u-fadeUp">
		<div id="catLinks" class="cat-links">
			<div class="footer-tags">
				<span>分类</span>
				<ul><li><?php $this->category('</li><li>', true) ?></ul>
			</div>
		</div>
		<div class="page-info">
			<section id="footer-info-lastMod" class="page-info--item">
				<div class="page-info--label">最近修改</div>
				<div class="page-info--text">
					此内容最后更新于<b><?php $modified = new \Typecho\Date($this->modified); echo $modified->word() ?></b>。
				</div>
			</section>
			<section id="footer-info-copyright" class="page-info--item">
				<div class="page-info--label">版权</div>
				<div class="page-info--text"><?php echo Helper::options()->copyRight ?></div>
			</section>
		</div>
	</footer>

	<?php endif ?>

	<?php $this->need('includes/comments.php'); ?>

	<?php if ($this->fields->showTOC): ?>
		<div class="p-toc p-menu-checkbox-container">
			<input type="checkbox" id="citizen-toc--checkbox" class="p-menu-checkbox"
				   role="button" aria-labelledby="citizen-toc--buttonCheckbox" aria-haspopup="true">
			<label id="citizen-toc--buttonCheckbox" class="p-menu-checkbox-button"
				   for="citizen-toc--checkbox" title="目录" aria-hidden="true">
				<span class="ui-icon material-icons">format_list_bulleted</span>
				<span id="panel-toc-label">目录</span>
			</label>
			<nav id="panel-toc" class="citizen-toc--card p-menu-checkbox-target">
				<div id="panel-toc-label" class="citizen-toc--header">目录</div>
				<div class="toc-target"></div>
			</nav>
		</div>
	<?php endif; ?>
</div>

<?php $this->need('includes/footer.php'); ?>