<?php include sprintf("%s/common.php", dirname(__FILE__)); ?>

<script>
var dilmot_api_url = "<?php echo $templateData['accout_url']; ?>/api/streams/<?php echo $templateData['stream_id']; ?>/get_published_interactions";

var is_dilmot_active_stream = true;
var error_msg = "<?php echo __('Failed to load published answers', 'dilmot'); ?>";
var error_html = "<div class='alert alert-warning'>" + error_msg + "</div>";

<?php if ($templateData['private_stream_pass']) { ?>
	var load_published_data = { stream_pass: '<?php echo $templateData["private_stream_pass"] ?>' };
<?php } else { ?>
	var load_published_data = {};
<?php } ?>
</script>

<div class="col-sm-12">
	<div class="col-sm-3">
		<img class="img-responsive" id="public-stream-guest-image" src="<?php echo $templateData['stream_image']; ?>">
	</div>
  
	<div class="col-sm-9" style="padding-left:0px;padding-right:0px;">
		<iframe src="<?php echo $templateData['accout_url']; ?>/streams/<?php echo $templateData['stream_slug']; ?>/embed/new-question-form?title=false" id="new-question-form" width="100%" height="290" scrolling="no" frameborder="0"></iframe>
	</div>		
</div>
		<?php if ($templateData['stream_info_box_html']) { ?>
			<?php echo $templateData['stream_info_box_html']; ?>
		<?php } ?>

<div id="stream_holder" style="clear:both"></div>
