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

<?php echo $templateData['stream_header']; ?>

<div id="stream_holder" style="clear:both"></div>
