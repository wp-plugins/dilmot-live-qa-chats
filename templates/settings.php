<div class="wrap">
	<h1><?php echo $this->getPluginDisplayName(); ?></h1>
	<h2><?php echo ' '; _e('Plugin Settings', 'dilmot'); ?></h2>

	<?php
	if (!empty($dilmotPluginValidationErrors)) {
		$errorMsg = '<b>' . __('Following errors occured', 'dilmot') . '</b>:</br>' . implode('<br>', $dilmotPluginValidationErrors);
		include(sprintf("%s/admin_error.php", dirname(__FILE__)));    		
	} elseif (isset($dilmotPluginValidationConfirm) && !empty($dilmotPluginValidationConfirm)) {
		$msg = $dilmotPluginValidationConfirm;
		include(sprintf("%s/admin_success.php", dirname(__FILE__)));
	}
	?>
	<form method="post">
  	<?php settings_fields($settingsGroup); ?>

    <h3><?php _e('Copy the following information and paste it in the <i>WordPress settings</i> section of your <a href="http://www.dilmot.com" >Dilmot</a> account:','dilmot') ?><br></h3>
    <table class="dilmot-settings">
    	<tr>
    		<th><?php _e('Site URL','dilmot') ?>:</th>
    		<td><?php echo site_url(); ?></td>
    	</tr>
    	<tr>
    		<th><?php _e('API Key','dilmot') ?>:</th>
    		<td>
    			<span id="api-key-holder"><?php echo $this->getOption('ApiKey'); ?></span>
					<span class="dashicons dashicons-update" id="api-key-reset"></span>
    		</td>
    	</tr>
    </table>
    <script>
    jQuery('#api-key-reset').bind('click', function() {resetApiKey() });

    function resetApiKey() {
    	var res = confirm('<?php _e("Are you sure you want to reset your API key?") ?>','dilmot');
    	if (!res) {
    		return;
    	}

    	jQuery('#api-key-reset').addClass('disabled');
			jQuery('#api-key-reset').unbind('click');

			jQuery.ajax({
				url: '<?php echo $this->getAjaxUrl("reset_dilmot_api_key"); ?>',
		    method: 'post',
		    complete: function(jqXHR, textStatus) {
					switch (jqXHR.status) {
						case 200:
							jQuery('#api-key-holder').html(jqXHR.responseText);
							alert('<?php _e("You should update your dilmot account settings with the new API key") ?>','dilmot');
							break;
						default:
							break;
		      }
		      jQuery('#api-key-reset').bind('click', function() {resetApiKey() });
		      jQuery('#api-key-reset').removeClass('disabled');
		    }
		  });
    }
    </script>
<h3><?php _e('Introduce the the following information regarding your Dilmot account:','dilmot') ?><br></h3>
    
    <table class="form-table">
    	<tbody>
				<?php
					foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
						$optionMetaDataInfoArr = $this->getOptionMetaDataInfo();
						$aOptionMetaDataInfo = $optionMetaDataInfoArr[$aOptionKey];
          	$displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
        ?>
				
				<tr valign="top">
					<th scope="row"><p><label for="<?php echo $aOptionKey ?>"><?php echo $displayText ?></label></p></th>
					<td>
						<?php $this->createFormControl($aOptionKey, $aOptionMeta, $aOptionMetaDataInfo, $this->getOption($aOptionKey), $dilmotPluginFormValues[$aOptionKey], $dilmotPluginValidationErrors[$aOptionKey]); ?>
						<?php if ($aOptionMetaDataInfo['hint']) {?>
						<div style="margin-left:5px;">
							<small><?php echo $aOptionMetaDataInfo['hint']; ?></small>
						</div>

						<?php } ?>
					</td>
				</tr>

				<?php
        	}
        ?>
      </tbody>
    </table>
		
		<?php @submit_button(); ?>
	</form>
</div>
