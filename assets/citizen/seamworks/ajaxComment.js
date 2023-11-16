let replyTo = '';
let ajaxComment = window.ajaxComment || {};

ajaxComment = {
	formMail:		null,
	formText:		null,
	formAuthor:		null,

	init: function() {
		this.formMail	= $('#comment-form [name="mail"]');
		this.formText	= $('#comment-form [name="text"]');
		this.formAuthor	= $('#comment-form [name="author"]');

		$('#commentSubmit').click(function (e) {
			let commentData;

			if (ajaxComment.formText.length && !(ajaxComment.formText.val().length > 0)) {
				$.toast({content: "请填写评论内容。"});
				return false;
			}

			// 如果 mail 有 required 属性，则要求填写昵称和邮箱
			if (
				ajaxComment.formMail.attr('required') &&
				(!ajaxComment.formAuthor.val().length > 0 || !ajaxComment.formMail.val().length > 0)
			) {
				$.toast({content: "昵称和邮箱不能为空。"});
				return false;
			}

			// 如果 mail 没有 required 属性，则仅要求昵称不能为空
			if (
				ajaxComment.formAuthor.length &&
				!ajaxComment.formMail.attr('required') &&
				!ajaxComment.formAuthor.val().length > 0
			) {
				$.toast({content: "昵称不能为空。"});
				return false;
			}

			// 发送 POST 之前的操作
			ajaxComment.preSubmit();

			// POST 基本信息
			if (login) {
				commentData = {
					'text': ajaxComment.formText.val()
				};
			} else {
				commentData = {
					'author':	ajaxComment.formAuthor.val(),
					'mail':		ajaxComment.formMail.val(),
					'text':		ajaxComment.formText.val()
				};
			}

			if (document.getElementById("comment-parent")) {
				commentData['parent'] = document.getElementById("comment-parent").value;
			}

			if (document.getElementById("comment-notify")) {
				commentData['notify'] = document.getElementById("comment-notify").checked ? 1 : 0;
			}

			// ajax 发送评论
			$.ajax({
				type: 'POST',
				url: $('#comment-form').attr('action'),
				data: commentData,
				error: function (jqXHR, textStatus, error) {
					if (jqXHR.status === 403) {
						$.toast({content: "评论发送过于频繁，请稍后再试。"});
					} else {
						$.toast({content: "评论发送失败，请尝试刷新。"});
					}
					ajaxComment.postSubmit(false);
				},
				success: function (data) {
					let message;
					if (!$('#comments', data).length) {
						data = $('<body></body>').prepend(data);
						if ($('pre>code>h1', data).length) {
							message = $('pre > code > h1', data).text();
						} else if ($('title').eq(0).text().trim().toLowerCase() === 'error') {
							message = $('.container', data).eq(0).text();
						} else {
							message = '评论提交失败';
						}

						$.toast({content: message});
						ajaxComment.postSubmit(false);
						return false
					}

					// 获取评论数据
					const htmlData = $(document.createElement('body')).append(data);

					// 如果 htmlData 存在，则获取 id
					if (htmlData.html()) {
						newCommentId = htmlData.html().match(/id=\"?comment-\d+/g).join().match(/\d+/g).sort(function (a, b) {
							return a - b
						}).pop();
					} else {
						$.toast({content: "获取评论 ID 时发生了错误，请刷新重试。"});
						return false;
					}

					// 处理评论数据
					let newComment, replyControl;
					if ('' === replyTo) {
						if (!$('.comment-list').length) {
							$('.respond').after($('.comment-list', data));
						} else if ($('.prev').length) {
							$('.comments-pagenav li a').eq(1).click();
						} else {
							newComment = $("#comment-" + newCommentId, data).addClass('fadeIn');
							$('.comment-list').first().prepend(newComment);
						}
					} else {
						newComment = $("#comment-" + newCommentId, data).addClass('fadeIn');
						if ((replyControl = $('#' + replyTo)).hasClass('comment-parent')) {
							if (replyControl.find('.comment-children').length) {
								$('#' + replyTo + ' .comment-children .comment-list').first().prepend(newComment);
								TypechoComment.cancelReply();
							} else {
								replyControl.append('<div class="comment-children"><ol class="comment-list"></ol></div>');
								$('#' + replyTo + ' .comment-children .comment-list').first().prepend(newComment);
								TypechoComment.cancelReply();
							}
						} else {
							// 如果回复的对象是子级评论，则直接插入在对应的子级评论之后
							replyControl.after(newComment);
							TypechoComment.cancelReply();
						}
					}

					ajaxComment.postSubmit(true);
					$.toast({content: "评论成功。"});
				}
			});
		});
	},

	preSubmit: function() {
		$('#comment-form .submit').html('处理中');
		$("#comment-form input,#comment-form textarea,#comment-form .submit").attr('disabled', true).css('cursor', 'not-allowed');
		$('#comment-form').animate({ opacity: .5 }, 300);
	},

	postSubmit: function(ok) {
		// 恢复表单状态
		$('#comment-form .submit').html('提交评论');
		$("#comment-form input,#comment-form textarea,#comment-form .submit").attr('disabled', false).css('cursor', 'pointer');
		$('#comment-form').animate({ opacity: 1 }, 100);

		// 发送成功则清空表单内容
		if (ok) {
			$("#textarea").val('');
			replyTo = '';
		}
	}
}