<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Citizen {
	/**
	 * 用于生成头像。
	 *
	 * @see https://ui-avatars.com
	 */
	const AVATAR_API_URL = "https://ui-avatars.com/api/?name=";

	/**
	 * 将样式表递交至 HTML head 标签。
	 *
	 * @return void
	 */
	public static function head(): void {
		$src_link = array(
			'https://fonts.googleapis.com/css?family=Material+Icons:FILL@1|Noto+Serif+SC:500&display=swap',
			'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-y/fancybox/3.5.7/jquery.fancybox.min.css',
			'https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-y/tocbot/4.18.2/tocbot.min.css',
			'https://registry.npmmirror.com/seamworks-statics/latest/files/citizen/prism.css',
			'https://registry.npmmirror.com/seamworks-statics/latest/files/citizen/render.css'
		);

		foreach ($src_link as $value) echo '<link rel="stylesheet" href="' . $value . '" />';
	}

	public static function footer(): void {
		$src_link = array(
			// 来自冰河时期的 StaticFile 表示它们并不会支持 HTTP/2，故换掉
			'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-y/jquery/3.6.0/jquery.min.js',
			'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-y/jquery.pjax/2.0.1/jquery.pjax.min.js',
			'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/jquery.lazy/1.7.11/jquery.lazy.min.js',
			'https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-y/fancybox/3.5.7/jquery.fancybox.min.js',
			'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/jquery-throttle-debounce/1.1/jquery.ba-throttle-debounce.min.js',
			'https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-y/tocbot/4.18.2/tocbot.min.js',
			'https://lf9-cdn-tos.bytecdntp.com/cdn/expire-1-y/howler/2.2.3/howler.core.min.js',
			'https://registry.npmmirror.com/seamworks-statics/latest/files/citizen/prism.js',
			'https://registry.npmmirror.com/seamworks-statics/latest/files/citizen/ajaxComment.min.js',
			'https://registry.npmmirror.com/seamworks-statics/latest/files/citizen/core-compiled.js',
		);

		foreach ($src_link as $value) echo '<script src="' . $value . '"></script>';
	}

	public static function fnTitle(Widget_Archive $archive): void {
		$archive->archiveTitle(array(
			'category'	=> '%s',
			'search'	=> '搜索：%s',
			'tag'		=> '#%s',
			'author'	=> '%s 的文章'
		), '', ' - ');

		Helper::options()->title();
	}

	public static function fnExcerpt(Widget_Archive $archive): void {
		if (Helper::options()->SnippetLength == 'FullText') {
			$archive->content('继续阅读');
		} elseif (Helper::options()->SnippetLength == 'Short') {
			$archive->excerpt(200);
		}
	}

	public static function fnAvatar($comment, int $size = 64): void {
		$class = "avatar";

		if (str_ends_with($comment->mail, "@gmail.com")) {
			$url = 'https://dn-qiniu-avatar.qbox.me/avatar/' . md5(strtolower(trim($comment->mail))) . '?s=' . $size;
		} elseif (str_ends_with($comment->mail, "@qq.com") && preg_match('/^\d+$/', substr($comment->mail, 0, -7))) {
			$url = "https://q1.qlogo.cn/g?b=qq&nk=" . $comment->mail . "&s=640";
		} else {
			$class .= " ui-avatar";
			$url = Citizen::AVATAR_API_URL . $comment->author . "&size=" . $size;
		}

		echo '<img class="' . $class . '" loading="lazy" src="' . $url . '" alt="' .
			$comment->author . '" width="' . $size . '" height="' . $size . '" />';
	}

	public static function fnDate($created): string {
		$diff = time() - $created;
		$d = floor($diff / 3600 / 24);
		$Y = date('Y', $created);

		if (date('Y-m-d', $created) == date('Y-m-d')) {
			return '今天';
		} elseif ($d == 1) {
			return '昨天';
		} elseif ($d == 2) {
			return '前天';
		} elseif ($d <= 31) {
			return $d . ' 天前';
		} elseif ($Y == date('Y')) {
			return date('m-d', $created);
		} else {
			return date('Y-m-d', $created);
		}
	}

	/**
	 * 为正文中的图片启用 LazyLoad 与 Fancybox 支持。
	 * 为能够自定义 options 且兼容 pjax，故此处不会添加 data-fancybox 属性。
	 * 进一步配置请前往 core.js。
	 *
	 * @param $text
	 * @return array|string|null
	 */
	public static function fnLazyLoad($text): array|string|null {
		return preg_replace(
			'/\<img(.*?)src=\"(.*?)\"(.*?)alt=\"(.*?)\"(.*?)\>/s',
			'<a href="${2}" data-lightbox no-linkTarget><img${1}src="' . Utils::indexThemeUrl('assets/loading.svg') . '" data-src="${2}"${3}class="lazy"alt="${4}"${5}></a>', $text);
	}

	/**
	 * 将正文中的标题转换为 TOCBot 可识别模式。
	 *
	 * @param $text
	 * @return array|string|null
	 */
	public static function fnToc($text): array|string|null {
		return preg_replace(
			'/\<h(2|3)(.*?)\>(.*?)\<\/h(2|3)\>/s',
			'<h${1}${2} id="${3}">${3}</h${1}>',$text);
	}

	public static function parseLess($text): array|string|null {
		return preg_replace(
			'/(.*?)\<\!\-\-less\-\-\>(.*?)/s',
			'${2}', $text);
	}

	public static function parseContent($data, $widget, $last) {
		$text = empty($last) ? $data : $last;
		if ($widget instanceof Widget_Archive) {
			$text = Citizen::fnToc(Citizen::fnLazyLoad($text));
		}
		return $text;
	}

	public static function parseExcerpt($data, $widget, $last) {
		$text = empty($last) ? $data : $last;
		if ($widget instanceof Widget_Archive) {
			$text = Citizen::parseLess(Citizen::fnToc(Citizen::fnLazyLoad($text)));
		}
		return $text;
	}
}