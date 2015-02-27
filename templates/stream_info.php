<div class="panel panel-default">
	<div class="panel-body">
		<div class="col-xs-3 avatar-left">
			<img class="img-responsive avatar-list" src="<?php echo $templateData['stream_image']; ?>">
		</div>
		<div class="col-xs-9">
			<h3 id="stream_title_public">
				<a href="<?php echo get_permalink() ?>"><?php echo get_the_title() ?></a>
			</h3>
			<br>
			<?php if ($templateData['stream_info_box_html']) { ?>
				<?php echo $templateData['stream_info_box_html']; ?>
			<?php } ?>
		</div>
	</div>
</div>
