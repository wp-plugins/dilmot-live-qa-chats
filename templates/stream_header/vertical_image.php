<div class="col-xs-12 zero-lateral-padding">
	<div class="col-xs-3">
		<img class="img-responsive" id="public-stream-guest-image" src="<?php echo $templateData['stream_image']; ?>">
	</div>
  
	<div class="col-sm-9 zero-lateral-padding">
		<iframe src="<?php echo $templateData['accout_url']; ?>/streams/<?php echo $templateData['stream_slug']; ?>/embed/new-question-form?title=false" id="new-question-form" width="100%" height="290" scrolling="no" frameborder="0" style="margin-bottom:10px;"></iframe>
	</div>		
</div>
<?php if ($templateData['stream_info_box_html']) { ?>
	<?php echo $templateData['stream_info_box_html']; ?>
<?php } ?>
