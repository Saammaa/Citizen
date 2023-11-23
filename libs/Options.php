<?php

function themeConfig($form) {
	$SnippetLength = new Typecho_Widget_Helper_Form_Element_Radio('SnippetLength', array(
		'FullText' => _t('手动截取'),
		'Short' => _t('固定截取'),
		'Title' => _t('不截取')
	),
		'Short',
		_t('摘要长度'),
		_t('
    <p class="description">该设置会影响首页的文章列表内的文章内容摘要如何显示。</p>
    <p class="description">使用手动截取时，系统将查找 <code>&lt;!--more--></code> 标签，并截获该标签前方的所有内容。</p>
    <p class="description">使用固定截取时，系统将自动抽取前 200 个字符。</p>
    <p class="description">若选择不截取，则文章只会显示其标题和描述。</p>')
	);
	$form->addInput($SnippetLength->addRule('required', _t('此处必须设置')));

	// ############################# 自定义 ################################

	$copyRight = new Typecho_Widget_Helper_Form_Element_Text(
		'copyRight', NULL,
		'⚡ 除非另有声明，本网站内容采用<a target="_blank" rel="nofollow" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">知识共享署名-非商业性使用-相同方式共享</a>授权。',
		_t('<h2>自定义</h2> 版权文本'),
		_t('<p class="description">为您网站中的文章等内容添加一份简短的版权描述文字。<br><small>该文字会显示在内容正文的下方。</small></p>'));
	$form->addInput($copyRight);
}
