<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function threadedComments($comments, $options): void {
	$commentClass = '';
	if ($comments->authorId) {
		if ($comments->authorId == $comments->ownerId) {
			$commentClass .= ' comment-by-author';
		} else {
			$commentClass .= ' comment-by-user';
		}
	}
	$commentLevelClass = $comments->_levels > 0 ? ' comment-child' : ' comment-parent';
?>

<div id="<?php $comments->theId(); ?>" class="comment-body<?php echo $commentLevelClass; echo $commentClass; ?>">
	<div class="comment-body-inner">
		<div class="comment-avatar" title="点按即可回复。">
			<?php $comments->reply(' '); ?>
			<?php Citizen::fnAvatar($comments) ?>
		</div>

		<div class="comment-main">
			<div class="comment-meta">
				<span class="comment-author">
					<?php echo $comments->author; ?>
				</span>
				<span class="comment-date">
					<?php if ('waiting' == $comments->status) { ?>
						<em class="comment-waiting">正在审核</em>
					<?php } else { ?>
						<?php echo Citizen::fnDate($comments->created); Comments::getParent($comments->coid);
					} ?>
				</span>
			</div>

			<div class="comment-content">
				<?php $comments->content(); ?>
			</div>
		</div>
	</div>

	<?php if ($comments->children) { ?>
		<div class="comment-children">
			<?php $comments->threadedComments($options); ?>
		</div>
	<?php } ?>
</div>

<?php } Comments::replyScript($this); ?>

<div class="p-comment u-fadeUp" id="comments">
	<?php $this->comments()->to($comments); ?>

	<?php if ($this->allow('comment')): ?>
		<div id="<?php $this->respondId(); ?>" class="respond">
			<script>login = false;</script>
			<?php $msg = isset($GLOBALS['msgPage']) && $GLOBALS['msgPage']; ?>

			<h2><?php if ($msg) { $this->title(); } else { echo '添加新评论'; } ?></h2>

			<p class="p-comment--tip">点击评论者的头像，即可对其进行回复。</p>

			<?php if ($msg) $this->content(); ?>

			<form method="post" action="<?php $this->commentUrl() ?>" id="comment-form" class="p-comment-form" role="form">
				<div class="p-comment--text">
					<textarea rows="8" cols="50" name="text" id="textarea"
							  class="textarea oo-ui-inputWidget-input"
							  placeholder="善言结善缘，恶语伤人心。"
							  required="required"><?php $this->remember('text'); ?></textarea>
				</div>
				<div class="p-comment--action oo-ui-textInputWidget">
					<?php if ($this->user->hasLogin()): ?>
						<script>login = true;</script>
					<?php else: ?>
						<input class="text oo-ui-inputWidget-input" type="text" name="author" id="author"
							   placeholder="用户名" value="<?php $this->remember('author'); ?>" required/>
						<input class="text oo-ui-inputWidget-input" type="email" name="mail" id="mail"
							   placeholder="邮箱" value="<?php $this->remember('mail'); ?>"
							<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?> />
					<?php endif; ?>

					<div class="submit-extra">
						<div class="comments-mail-me oo-ui-fieldLayout-body">
							<span class="oo-ui-fieldLayout-field">
								<span class="oo-ui-widget oo-ui-checkboxInputWidget">
									<input aria-label="有回复时通知我" name="notify" type="checkbox" id="comment-notify" class="oo-ui-inputWidget-input" value="1" checked/>
									<span class="oo-ui-checkboxInputWidget-checkIcon oo-ui-icon-check oo-ui-iconWidget"></span>
								</span>
								<label for="comment-notify" class="oo-ui-fieldLayout-header">有回复时通知我。</label>
							</span>
						</div>
					</div>

					<button class="submit oo-ui-buttonElement-button p-button p-button--primary" id="commentSubmit">
						<?php if (!$msg) { echo '提交评论'; } else { echo '写下留言'; } ?>
					</button>

					<span class="cancel-comment-reply oo-ui-buttonElement-button p-button p-button--destructive">
						<?php $comments->cancelReply(); ?>
					</span>
				</div>
			</form>
		</div>
	<?php endif ?>

	<?php if ($comments->have()): ?>
		<?php $comments->listComments(); ?>
		<div class="p-comment--nav">
			<?php $comments->pageNav('上一页', '下一页'); ?>
		</div>
	<?php endif; ?>
</div>