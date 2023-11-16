let CITIZEN = window.CITIZEN || {};

if (window.jQuery === undefined) jQuery = $ = {};

CITIZEN = {
	fancyOptions: {
		loop : true,
		buttons : [ 'slideShow', 'fullScreen', 'thumbs', 'download', 'zoom', 'close' ],
		transitionEffect : "slide",
		thumbs : {
			axis: 'x',
		},
		lang : 'zh',
		i18n : {
			'zh' : {
				CLOSE : '关闭',
				NEXT : '下一个',
				PREV : '上一个',
				ERROR : '内容加载失败。',
				PLAY_START : '播放幻灯片',
				PLAY_STOP : '暂停幻灯片',
				FULL_SCREEN : '全屏',
				THUMBS : '缩略图',
				DOWNLOAD : '下载',
				ZOOM : '缩放'
			}
		}
	},

	init: function() {
		// 注入搜索控件
		$('#searchSubmit').click(function(e){
			e.preventDefault();
			CITIZEN.submitSearch();
		});

		this.reload();
		this.onPjax();
	},

	reload: function() {
		try {
			if (typeof ajaxComment !== 'undefined') {
				ajaxComment.init();
			}
		} catch(e) {}

		this.fnPrismJS();
		this.fnFancybox();
		this.lazyLoader();
		this.fnTocBot();
		this.pawButtonInit();
	},

	/**
	 * 为正文中的图片初始化 Fancybox。
	 * 原始元素参见 Citizen::fnLazyLoad() 方法。
	 *
	 * @author chee5e
	 * @see https://chee5e.space/fancybox-pjax
	 */
	fnFancybox: function() {
		$('.p-content figure.image').each(function(){
			const $lightbox = $(this).find('a[data-lightbox]');

			$lightbox.attr({
				'data-fancybox'	: "articleBody",
				'data-caption'	: $(this).find('figcaption').text(),
			});
		});

		if ($.fancybox) $('[data-lightbox]').fancybox(this.fancyOptions);
	},

	fnPrismJS: function() {
		if (typeof Prism !== 'undefined') {
			const pres = document.getElementsByTagName('pre');
			for (let i = 0; i < pres.length; i++) {
				if (pres[i].getElementsByTagName('code').length > 0) pres[i].className = 'line-numbers';
			}
			Prism.highlightAll(false, null);
		}
	},

	fnTocBot: function() {
		if ($('.p-toc').length) {
			tocbot.init({
				tocSelector: '.toc-target',
				contentSelector: '.p-content',
				headingSelector: 'h2',
				ignoreSelector: '.js-toc-ignore',
				activeLinkClass: 'is-active',
				hasInnerContainers: true,
				scrollSmooth: true,
				scrollSmoothDuration: 40,
				headingsOffset: 20,
				scrollSmoothOffset: -20,
				includeTitleTags: true,
			});
		}
	},

	actionLike: function(){
		if (this.likeStatus || !this.likeButton.hasClass('liked')) return;

		const cid = CITIZEN.likeButton.attr('data-cid');

		$.ajax({
			type: 'post',
			url: this.likeButton.attr('data-url'),
			data: 'like=' + this.likeButton.attr('data-cid'),
			async: true,
			timeout: 30000,
			cache: false,
			success: function (data) {
				let record = JSON.parse(localStorage.getItem('like_record'));
				if (!record) record = [];
				record.push(cid);
				localStorage.setItem('like_record',JSON.stringify(record));
			},
		});
	},

	/**
	 * 初始化点赞按钮点击与动画行为。
	 *
	 * @link https://codepen.io/aaroniker/pen/VwwxopM
	 */
	pawButtonInit: function() {
		// 注册控件，查询状态
		CITIZEN.likeButton = $('#actionLike');

		const record = JSON.parse(localStorage.getItem('like_record'));
		CITIZEN.likeStatus = this.likeButton && ($.inArray(this.likeButton.attr('data-cid'), record) !== -1);

		if (CITIZEN.likeStatus) {
			this.likeButton.addClass('animation liked confetti noEffect');
			return;
		}

		let confettiAmount = 60,
			confettiColors = [
				'#7d32f5',
				'#f6e434',
				'#63fdf1',
				'#e672da',
				'#295dfe',
				'#6e57ff'
			],
			random = (min, max) => {
				return Math.floor(Math.random() * (max - min + 1) + min);
			},
			createConfetti = to => {
				let elem = document.createElement('i'),
					set = Math.random() < 0.5 ? -1 : 1;
				elem.style.setProperty('--x', random(-260, 260) + 'px');
				elem.style.setProperty('--y', random(-160, 160) + 'px');
				elem.style.setProperty('--r', random(0, 360) + 'deg');
				elem.style.setProperty('--s', random(.6, 1));
				elem.style.setProperty('--b', confettiColors[random(0, 5)]);
				to.appendChild(elem);
			};

		document.querySelectorAll('#actionLike').forEach(elem => {
			elem.addEventListener('click', e => {
				let number = elem.children[1].textContent;
				if (!elem.classList.contains('animation')) {
					elem.classList.add('animation');
					for (let i = 0; i < confettiAmount; i++) {
						createConfetti(elem);
					}
					setTimeout(() => {
						elem.classList.add('confetti');
						setTimeout(() => {
							elem.classList.add('liked');
							elem.children[1].textContent = parseInt(number) + 1;
						}, 400);
						setTimeout(() => {
							elem.querySelectorAll('i').forEach(i => i.remove());
						}, 600);
					}, 260);
				} else {
					elem.classList.remove('animation', 'liked', 'confetti');
					elem.children[1].textContent = parseInt(number) - 1;
				}
				e.preventDefault();
			});
		});
	},

	lazyLoader: function() {
		$('.lazy').Lazy({
			effect: 'fadeIn',
			visibleOnly: true,
			effectTime: 300,

			onError: function(element) {
				console.error('加载 ' + element.data('src') + ' 时发生了错误。');
			},

			afterLoad: function(el) {
				$(el).addClass('lazy-loaded');
			}
		});
	},

	submitSearch: function() {
		const searchInput = $('#searchInput');

		if (searchInput.val() === '') {
			$.toast({content: "请输入关键词。"});
		} else {
			const wrapper = '<span class="p-search--word">$&</span>';
			let url = window.location.protocol + '//' + window.location.host +
				'/index.php/search/' + searchInput.val() + '/';

			$.pjax({
				url: url,
				container: '#main',
				fragment: '#main',
				timeout: 8000,
			}).done(function() {
				// 高亮搜索结果
				$("article .p-snippet").each(function() {
					$(this).html($(this).text().replace(new RegExp(searchInput.val(), 'g'), wrapper));
				});
			});

			// 面向已经打开的搜索窗口
			// 随便单击个地方意思意思得了
			$('body').click();
		}
	},

	onPjax: function() {
		let target = 'a:not(a[target="_blank"], a[no-pjax], .cancel-comment-reply a, .comment-reply a)';

		$(document).pjax(target, {
			container: '#main',
			fragment: '#main',
			timeout: 8000
		}).on('pjax:click', function() {
			CITIZEN.actionLike();
		}).on('pjax:send', function() {
			$('.bar-loader').fadeIn(100);
			// 取消文章目录自动关闭时的滚动绑定
			$(window).off('scroll');
			if ($('.p-toc').length) tocbot.destroy();
		}).on('pjax:complete', function() {
			CITIZEN.reload();
			let toc = '';
			$('.bar-loader').fadeOut(300);
		});
	}
}

CITIZEN.init();