<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
	<meta charset="<?php $this->options->charset(); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="renderer" content="webkit">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="x-dns-prefetch-control" content="on">
	<meta name="referrer" content="same-origin">
	<meta http-equiv="Cache-Control" content="no-transform">
	<meta http-equiv="Cache-Control" content="no-siteapp">
	<link rel="preconnect" href="https://https://registry.npmmirror.com" crossorigin>
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Material+Icons:FILL@1|Noto+Serif+SC:500&display=swap">
	<title><?php Citizen::fnTitle($this); ?></title>
	<?php Citizen::head(); $this->header(); ?>
</head>