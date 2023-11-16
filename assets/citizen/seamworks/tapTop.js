/**
 * Seamworks 回顶小控件。
 * 跨平台移植时，请参考 taptop.css 中的样式。
 * 需要 jQuery。
 */

!function ($, window, document) {
	"use strict";

	let hideTimer = null,
		pauseScrollWatch = false,
		upOnly = false,
		isShown = false,
		scrollTop = window.pageYOffset || document.documentElement.scrollTop,
		scrollDir = null,
		scrollTopDirChange = null,
		scrollTrigger,
		$buttons = null;

	const progressPath	= document.querySelector('.progress-wrap path'),
		  pathLength	= progressPath ? progressPath.getTotalLength() : 0;

	function initialize() {
		// 已初始化的情况
		if ($buttons && $buttons.length) {
			return false;
		}

		$buttons = $('.js-scrollButtons');
		if (!$buttons.length) {
			return false;
		}

		if ($buttons.data('trigger-type') === 'up') {
			upOnly = true;
		}

		$buttons.on({
			'mouseenter focus':	actionEnter,
			'mouseleave blur':	actionLeave,
			'click':			actionClick
		});

		if (pathLength) {
			progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
			progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
			progressPath.style.strokeDashoffset = pathLength;
			progressPath.getBoundingClientRect();
			progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';

			updateProgress();
			$(window).scroll(updateProgress);
		}

		$(window).scroll(onScroll);

		return true;
	}

	function onScroll(e) {
		if (pauseScrollWatch) return;

		let newScrollTop = window.pageYOffset || document.documentElement.scrollTop,
			oldScrollTop = scrollTop;

		scrollTop = newScrollTop;

		if (newScrollTop > oldScrollTop) {
			if (scrollDir !== 'down') {
				scrollDir = 'down';
				scrollTopDirChange = oldScrollTop;
			}
		} else if (newScrollTop < oldScrollTop) {
			if (scrollDir !== 'up') {
				scrollDir = 'up';
				scrollTopDirChange = oldScrollTop;
			}
		} else {
			return;
		}

		if (upOnly) {
			if (scrollDir !== 'up' || scrollTop < 100) {
				if (scrollTrigger) {
					scrollTrigger.cancel();
					scrollTrigger = null;
				}
				return;
			}

			// 仅在向上滚动 30px 后触发以减少误报
			if (scrollTopDirChange - newScrollTop < 30) {
				return;
			}
		}

		// 即将触发
		if (scrollTrigger) return;

		// 注意 Android 上的 Chrome 可能会严重限制 setTimeout
		// 因此应尽量使用 requestAnimationFrame 代替，以确保能够在预期时间触发此操作
		scrollTrigger = requestAnimationTimeout(function () {
			scrollTrigger = null;

			if (scrollTop < 10) {
				actionHide();
			} else {
				actionShow();
				startHideTimer();
			}
		}, 50);
	}

	/**
	 * 在特定延迟后执行给定的回调函数，允许进行更精确的时间控制，以提供更平滑的动画效果。
	 * 传递所要执行的回调函数和延迟时间作为参数，然后使用返回的对象的 cancel 方法来取消定时任务。
	 * 引用自 XenForo。
	 *
	 * @param fn
	 * @param delay
	 * @returns {{}}
	 */
	function requestAnimationTimeout(fn, delay) {
		if (!delay) delay = 0;

		// 如果不支持则使用 setTimeout 作为替代
		let raf = window.requestAnimationFrame ||
				function (cb) {
					return window.setTimeout(cb, 1000 / 60);
				},
			start = Date.now(),
			data  = {};

		function loop() {
			// 如果当前时间距离起始时间超过了指定的延迟时间，则执行回调函数
			if (Date.now() - start >= delay) {
				fn();
				// 否则继续使用 requestAnimationFrame 或 setTimeout 来调度下一次循环
			} else {
				data.id = raf(loop);
			}
		}

		// 初始化循环
		data.id = raf(loop);

		// 定义一个取消函数，用于取消定时任务
		data.cancel = function () {
			let caf = window.cancelAnimationFrame || window.clearTimeout;
			caf(this.id);
		};

		// 返回包含定时任务信息的对象
		return data;
	}

	function actionShow() {
		if (!isShown) {
			$buttons.removeClass('hidden');
			$buttons.addClass('active-progress');
			isShown = true;
		}
	}

	function actionHide() {
		if (isShown) {
			$buttons.addClass('hidden');
			$buttons.removeClass('active-progress');
			isShown = false;
		}
	}

	function updateProgress() {
		const scroll = $(window).scrollTop(),
			  height = $(document).height() - $(window).height();
		progressPath.style.strokeDashoffset = pathLength - (scroll * pathLength / height);
	}

	function startHideTimer() {
		clearHideTimer();

		hideTimer = setTimeout(function () {
			actionHide();
		}, 3000);
	}

	function clearHideTimer() {
		clearTimeout(hideTimer);
	}

	function actionEnter() {
		if (scrollTop > 0) {
			clearHideTimer();
			actionShow();
		}
	}

	function actionLeave() {
		clearHideTimer();
	}

	function actionClick(e) {
		pauseScrollWatch = true;

		setTimeout(function () {
			pauseScrollWatch = false;
		}, 500);

		actionHide();
		jQuery('html, body').animate({scrollTop: 0}, 80);
	}

	initialize();

	// 兼容 PJAX
	$(document).on('pjax:click', function() {
		actionHide();
	}).on('pjax:complete', function() {
		// 恢复由 core.js 撤销绑定的 scroll 事件
		$(window).scroll(updateProgress);
		$(window).scroll(onScroll);
	});
}(window.jQuery, window, document);