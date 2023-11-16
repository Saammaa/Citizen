let PLAYER = window.PLAYER || {};

// 用于连接 Meting API 的管道地址。type 仅支持 playlist 类型
const PLAYER_API = '/play.php/?type=playlist&id=';
const PLAY_TEXT = $("#playPause span");

/**
 * 适用于 Citizen 侧边栏的音乐播放器。
 */

PLAYER = {
	// musicArray[a] ~ artist, lrc, name, pic, url
	musicArray: [],
	musicCurrent: null,

	mainContainer:		$('.p-player'),
	albumContainer:		$('.p-player--album'),
	infoContainer:		$('.p-player--info'),

	// 可视化波纹渲染区
	canvasContainer:	document.getElementById('playerVisualizer'),

	// musicCurrent 已被实例化的 Howl 对象
	sound: null,
	// 播放队列指针
	pointer: 0,
	// 随机播放、静音和单曲循环
	// 静音未被用于前台交互
	random: false,
	mute: false,
	loop: false,

	// 按钮单击事件是否已被绑定
	bound: false,

	init: function (id = ''){
		if (typeof Howl === "undefined") {
			console.log("由于未引入 Howler，播放器现已中止加载。");
			return;
		}

		if (this.mainContainer.length === 0) {
			console.error('找不到播放器容器。');
			return;
		}

		const playListId = id === '' ? this.mainContainer.data('playlist') : id;
		if (!playListId) {
			console.warn('未声明歌单 ID。');
		} else {
			this.processPlaylist(playListId);
		}
	},

	playerInit: function(data) {
		this.musicArray = data;
		this.musicCurrent = data[0];

		this.playerUI();

		this.bindFunctions({
			"playPause":	"actionPlay",
			"songNext":		"playNext",
			"songPrevious":	"playPrevious",
			"songShuffle":	"actionShuffle",
			"songRepeat":	"actionLoop",
		});
	},

	processPlaylist: function(playListId) {
		fetch(PLAYER_API + playListId).then(response => response.json()).then(data => {
			if (data[0].name !== '') {
				this.playerInit(data);
			} else {
				console.error('歌单中不存在音乐，请检查所提供的歌单。');
			}
		});
	},

	/**
	 * 实例化并播放 Howl 音频对象。
	 */
	play: function(index) {
		this.musicCurrent = this.musicArray[index];

		if (this.musicCurrent) {
			if (this.sound) this.sound.stop();

			this.sound = new Howl({
				src: this.musicCurrent.url,
				// https://github.com/goldfire/howler.js#format-array-
				format: ['mp3'],
				preload: true,
				// 自动播放恶心死人，我认真的
				autoplay: false,
				// 手动触发切换操作仍会跳转至下一首
				loop: this.loop,
				onload: function () {
					$('#playPause').removeClass('is-loading');
				},
				onplay: function() {
					PLAY_TEXT.html("pause");
				},
				onpause: function() {
					PLAY_TEXT.html("play_arrow");
				},
				onstop: function() {
					PLAY_TEXT.html("play_arrow");
				},
				onend: function () {
					PLAY_TEXT.html("play_arrow");
					PLAYER.playNext(1, true);
					// 看上去很 SB，但能有效等待下一张封面图加载完成
					setTimeout(function () {
						PLAYER.onNextUI();
					}, 1500);
				},
			});

			this.playerUI();
			this.visualizerUI();

			if (this.sound.state() === 'loading') {
				$('#playPause').addClass('is-loading');
			}

			this.sound.play();
		}
	},

	/**
	 * 同步用户界面封面与文本。
	 */
	playerUI: function() {
		$('.p-player--info h1').text(this.musicCurrent.name);
		$('.p-player--info h2').text(this.musicCurrent.artist);
		$('.p-player--album img').attr('src', this.musicCurrent.pic);
	},

	/**
	 * 闪烁一个显示当前所播放音乐封面图的指示型元素。
	 */
	onNextUI: function() {
		$('.player-indicator img').attr('src', this.musicCurrent.pic);

		$('.player-indicator').fadeIn('fast', function() {
			$(this).delay(1000).fadeOut('fast');
		});
	},

	/**
	 * 渲染音乐可视化播放区。
	 *
	 * @link https://blog.logrocket.com/audio-visualizer-from-scratch-javascript
	 */
	visualizerUI: function() {
		if(!this.canvasContainer) return;

		const audioCtx = Howler.ctx;
		const ctx = this.canvasContainer.getContext("2d");

		let analyser = null;
		// 创建用于分析时间和频率的音频节点
		analyser = audioCtx.createAnalyser();

		const soundNode = this.sound._sounds[0]._node;
		// 将音频源连接至 analyser，以供 analyser 获取并分析音频的时间和频率
		soundNode.connect(analyser);

		// 可视化波形区的带渲染波形条总数
		// 控制 FFT 的大小，后者是一种快速傅立叶变换，表现为声音样本的数量
		analyser.fftSize = 128;

		const bufferLength = analyser.frequencyBinCount;
		// 转换为无符号 8 位整型数组
		const dataArray = new Uint8Array(bufferLength);
		// 每个波形条纹的宽度
		const barWidth = this.canvasContainer.width / bufferLength;

		let x = 0;

		function animate() {
			x = 0;
			ctx.clearRect(0, 0, PLAYER.canvasContainer.width, PLAYER.canvasContainer.height);
			analyser.getByteFrequencyData(dataArray);
			drawVisualizer({ bufferLength, dataArray, barWidth });
			requestAnimationFrame(animate);
		}

		const drawVisualizer = ({ bufferLength, dataArray, barWidth }) => {
			let barHeight;
			for (let i = 0; i < bufferLength; i++) {
				barHeight = dataArray[i + 10] * 0.75;
				const red = (i * barHeight) / 10;
				const green = i * 5;
				const blue = barHeight / 4 - 16;
				ctx.fillStyle = `rgb(${red}, ${green}, ${blue})`;
				// 画布从左上角开始渲染，故使用下列 Y 轴计算方式，从左下角开始绘制波形条
				ctx.fillRect(x, PLAYER.canvasContainer.height - barHeight, barWidth, barHeight);
				x += barWidth;
			}
		};

		animate();
	},

	/**
	 * 获取播放队列中下一首音乐的指针，然后立刻播放该指针指向的音乐。
	 * 自动循环队列首尾的对象。
	 */
	playNext: function(offset = 1, end = false) {
		if (end && this.loop) return true;

		if (this.random) {
			this.pointer = Math.floor(Math.random() * (this.musicArray.length - 1));
		} else {
			this.pointer += offset;

			if (this.pointer < 0) {
				this.pointer = this.musicArray.length - 1;
			} else if (this.pointer === this.musicArray.length) {
				this.pointer = 0;
			}
		}

		this.play(this.pointer);
	},

	playPrevious: function() {
		this.playNext(-1);
	},

	/**
	 * 交互播放 PLAYER.sound 对象。
	 * 若对象正在播放，则暂停播放。
	 *
	 * @for UserInterface
	 */
	actionPlay: function() {
		if (this.sound) {
			if (this.sound.playing()) {
				this.sound.pause();
			} else {
				this.sound.play();
			}
		} else {
			this.play(this.pointer);
		}
	},

	/**
	 * 切换随机模式。
	 *
	 * @for UserInterface
	 */
	actionShuffle: function() {
		this.loop = false;
		this.random = !this.random;

		$('#songShuffle').toggleClass('is-active');
		$('#songRepeat').removeClass('is-active');
	},

	actionLoop: function() {
		this.random = false;
		this.loop = !this.loop;

		$('#songRepeat').toggleClass('is-active');
		$('#songShuffle').removeClass('is-active');
	},

	bindFunctions: function(functionsArray) {
		// 避免重复绑定播放器控件
		if (this.bound) return;

		$.each(functionsArray, function(id, functionName) {
			$("#" + id).click(function() {
				if (typeof PLAYER[functionName] === "function") {
					PLAYER[functionName]();
				}
			});
		});

		this.bound = true;
	}
}

PLAYER.init();