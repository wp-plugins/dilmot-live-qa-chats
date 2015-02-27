<?php include sprintf("%s/common.php", dirname(__FILE__)); ?>

<div class="col-sm-12">
	<div class="col-sm-4 col-lg-4">
		<img class="img-responsive" id="public-stream-guest-image" src="<?php echo $templateData['stream_image']; ?>">
	</div>
  
	<div class="col-sm-8 col-lg-8" style="padding-left:0px;padding-right:0px;">
		<?php if ($templateData['stream_info_box_html']) { ?>
			<?php echo $templateData['stream_info_box_html']; ?>
		<?php } ?>
	</div>		
</div>

<div id="stream_holder">
	<?php echo $templateData['stream_html']; ?>
</div>