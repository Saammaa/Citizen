<?php

if (!defined('__TYPECHO_ROOT_DIR__')) exit
$this->need('includes/header.php');

?>

<div class="p-body-container" id="main" role="main">
	<div class="p-content p-error">
		<h1>找不到页面</h1>
		<p class="p-error-text">
			当前所访问的页面不存在。您可以<a class="button" href="<?php Utils::indexHome() ?>">返回首页</a>。
		</p>
	</div>
</div>

<?php $this->need('includes/footer.php'); ?>