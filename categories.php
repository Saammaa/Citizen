<?php

/**
 * 分类总览
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php $this->need('includes/header.php'); ?>

<div class="p-body-container" id="main" role="main">
	<header class="p-body-header u-fadeUp">
		<div class="page-heading">
			<div class="firstHeading-container">
				<h1 id="firstHeading" class="firstHeading">分类中心</h1>
			</div>
			<div class="p-description">您可以在此页面浏览站点中所有已被创建的分类。</div>
		</div>
	</header>

	<div class="p-content u-fadeUp">
		<ul class="p-category--container">
			<?php $this->widget('Widget_Metas_Category_List')->parse('<li><a href="{permalink}" class="button">{name}</a></li>'); ?>
		</ul>
	</div>
</div>

<?php $this->need('includes/footer.php'); ?>