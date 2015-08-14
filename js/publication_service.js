var DilmotPublicationService = {
  stream_id: null,

  init: function(pusher_key, channel_name, stream_id) {
    var pusher = new Pusher(pusher_key);
    var channel = pusher.subscribe(channel_name);
    DilmotPublicationService.stream_id = stream_id;

    channel.bind('new_interaction', this.new_interaction_listener);
    channel.bind('update_interaction', this.update_interaction_listener);
    channel.bind('remove_interaction', this.remove_interaction_listener);
    channel.bind('close_stream', this.close_stream_listener);
    channel.bind('open_stream', this.open_stream_listener);
  },

  add_new_interaction: function(interaction_id, interaction_html, holder, interaction_holder_id_prefix) {
    var element_id = interaction_holder_id_prefix + interaction_id

    if (jQuery(element_id).length) {
      DilmotPublicationService.update_interaction(element_id, interaction_html, holder);
      return;
    }

    jQuery(holder + ' .empty_msg').hide('fast');
    jQuery(interaction_html).hide().prependTo(holder).fadeIn("slow");

  },

  update_interaction: function(element_id, interaction_html, holder) {
    jQuery(element_id).fadeOut("slow", function() {
      var updated_element = jQuery(interaction_html).hide();
      jQuery(this).replaceWith(updated_element);
      jQuery(element_id).fadeIn("slow");
    });
  },

  // pusher listeners
  new_interaction_listener: function(data) {
    var interaction_html = data.content.html;
    var interaction_id = data.content.id;

    DilmotPublicationService.add_new_interaction(interaction_id, interaction_html, "#stream_holder", "#interaction_");
  },

  update_interaction_listener: function(data) {
    var interaction_id = data.content.id;
    var element_id = '#interaction_' + interaction_id;
    var interaction_html = data.content.html;

    if (jQuery(element_id).length) {
      DilmotPublicationService.update_interaction(element_id, interaction_html,"#stream_holder");

    } else {
      DilmotPublicationService.add_new_interaction(interaction_id, interaction_html, "#stream_holder", "#interaction_");
    }
  },

  remove_interaction_listener: function(data) {
    var interaction_id = data.content
    var element_id = '#interaction_' + interaction_id;
    jQuery(element_id).fadeOut("slow", function() { 
      jQuery(this).remove();
      if (jQuery('#stream_holder .interaction-holder').length == 0) {
        $('#stream_holder .empty_msg').show('fast');
      }
    });
  },

  close_stream_listener: function() {
    // TBD - we should CHECK that plugin updated stream status and got the stream html before we reload
    setTimeout(function(){
       window.location.reload(1);
    }, 10000);
  },

  open_stream_listener: function() {
    // TBD - we should CHECK that plugin updated stream status and cleared stream html before we reload
    setTimeout(function(){
       window.location.reload(1);
    }, 10000);
  },
}