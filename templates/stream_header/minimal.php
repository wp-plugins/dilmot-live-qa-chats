<?php if ($templateData['stream_status'] != 'closed') { ?>
	<iframe src="<?php echo $templateData['accout_url']; ?>/streams/<?php echo $templateData['stream_slug']; ?>/embed/new-question-form?title=false" id="new-question-form" width="100%" height="290" scrolling="no" frameborder="0" style="margin-bottom:30px;"></iframe>
<?php } ?>