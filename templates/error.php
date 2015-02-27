<div>
	<img src='<?php echo plugins_url('/images/dilmot-error.png', dirname(__FILE__)) ?>' />
	<span style="margin-left:7px">
	<?php 
		$defaultErrorMsg = (__('Unexpected error occured', 'dilmot'));
		echo (isset($templateData['errorMsg']) ? $templateData['errorMsg'] : $defaultErrorMsg);
	?>
	</span>
</div>
