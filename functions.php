<?php

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once('libs/Utils.php');
require_once('libs/Citizen.php');
require_once('libs/Options.php');
require_once('libs/Comments.php');

Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Citizen', 'parseContent');
Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Citizen', 'parseExcerpt');

function themeInit($archive): void {
	Helper::options()->commentsAntiSpam = false;
	Helper::options()->commentsMaxNestingLevels	= '9999';
	Helper::options()->commentsPageDisplay = 'first';
	Helper::options()->commentsOrder = 'DESC';
	Helper::options()->commentsCheckReferer = false;
}

function themeFields($layout): void {
	$description = new Typecho_Widget_Helper_Form_Element_Text('description',
		NULL,
		NULL,
		_t('描述'),
		_t('此文章的简要描述，显示在标题的正下方。'));
	$layout->addItem($description);

	$thumbnail = new Typecho_Widget_Helper_Form_Element_Text('thumbnail', NULL,
		NULL,
		_t('封面图'),
		_t('输入封面图的 URL，则其将会作为一个巨型背景图片显示在标题的下层。'));
	$layout->addItem($thumbnail);

	$showTOC = new Typecho_Widget_Helper_Form_Element_Radio('showTOC', array(
		true  => _t('开启'),
		false => _t('关闭')),
		false,
		_t('显示目录'),
		_t('在侧边栏中显示目录。'));
	$layout->addItem($showTOC);

	$allowLike = new Typecho_Widget_Helper_Form_Element_Text('allowLike', NULL,
		NULL,
		_t('点赞'),
		_t('允许访客通过点击一个交互式按钮来表达对内容的喜爱。<br><small>偷偷改点赞你就是小丑。</small>'));
	$layout->addItem($allowLike);
}