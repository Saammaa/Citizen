<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Utils {
	/**
	 * 输出相对首页路由，本方法会自适应伪静态，用于动态文件。
	 */
	public static function index($path = ''): void {
		Helper::options()->index($path);
	}

	/**
	 * 输出相对首页路径，本方法用于静态文件。
	 */
	public static function indexHome($path = ''): void {
		Helper::options()->siteUrl($path);
	}

	/**
	 * 输出相对主题目录路径，用于静态文件。
	 */
	public static function indexTheme($path = ''): void {
		Helper::options()->themeUrl($path);
	}

	public static function indexThemeUrl($path = ''): string {
		return Helper::options()->themeUrl . '/' . $path;
	}
}