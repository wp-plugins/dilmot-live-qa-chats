jQuery(document).ready(function() {
	jQuery('span[id^="readMoreDesc"]').each(function() {
		var streamId = this.id.replace(/^readMoreDesc/, '');

		jQuery('#readMoreDesc' + streamId).on('shown.bs.collapse', function () {
			jQuery('#readMoreBtn' + streamId + ' .glyphicon').addClass('glyphicon-chevron-left').removeClass('glyphicon-chevron-right');
		});
		jQuery('#readMoreDesc' + streamId + '').on('hidden.bs.collapse', function () {
			jQuery('#readMoreBtn' + streamId + ' .glyphicon').addClass('glyphicon-chevron-right').removeClass('glyphicon-chevron-left');
		});
	});

	if (typeof(publicationServiceData) != 'undefined') {
		DilmotPublicationService.init(
			publicationServiceData.pusher_key, publicationServiceData.channel_name, publicationServiceData.stream_id
		);
	} else {
		console.error("Cannot init publication service listener - data is missing.")
	}

	if (typeof(is_dilmot_active_stream) !== 'undefined' && is_dilmot_active_stream) {
		jQuery.ajax({
			url: dilmot_api_url,
	    method: 'post',
    	data: load_published_data,
	    complete: function(jqXHR, textStatus) {
				switch (jqXHR.status) {
					case 200:
						var published_interactions = jqXHR.responseText
						jQuery(published_interactions).hide().appendTo("#stream_holder").fadeIn("slow");
						break;
					default:
						jQuery(error_html).hide().appendTo("#stream_holder").fadeIn("slow");
						console.error("Failed to load published interactions: " + jqXHR.responseText);
	      }
	    }
	  });

		// ***************************************************************************
		// * Add communication with iframe in order to update its height dynamically
		// ***************************************************************************
	  jQuery( window ).on("resize", function() {
			var win = document.getElementById("new-question-form").contentWindow;
			win.postMessage("questionFormHeightRequest", "*");
	  });	

		jQuery('#new-question-form').load(function() {
			var win = document.getElementById("new-question-form").contentWindow;
			win.postMessage("questionFormHeightRequest", "*");
		});


		function listener(event){
			if (typeof(event.data.questionFormDivHeight) !== 'undefined') {
				var newHeight = event.data.questionFormDivHeight;
				jQuery('#new-question-form').height(newHeight);
			}
		}

		if (window.addEventListener){
		  addEventListener("message", listener, false)
		} else {
		  attachEvent("onmessage", listener)
		}
	}
});