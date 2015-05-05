<div class="error">
	<p>
		<?php 
			$defaultErrorMsg = (__('Unexpected error occured', 'dilmot'));
			echo (isset($errorMsg) ? $errorMsg : $defaultErrorMsg);
		?>
	</p>
</div>