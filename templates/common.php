<script>
if (typeof bindReadMoreBtn === 'undefined') {
	function bindReadMoreBtn(streamId) {
		console.warn("'bindReadMoreBtn' is deprecated.");
	}
}

if (typeof publicationServiceData === 'undefined') {
	publicationServiceData = {
		pusher_key: "<?php echo $templateData['pusher_key'] ?>",
		channel_name: "<?php echo $templateData['channel_name'] ?>",
		stream_id: "<?php echo $templateData['stream_id']; ?>"
	}
}
</script>
