<?php foreach($reviews as $review):?>
	<div class="namaste-review">
		<p><?php printf(__('%1$s on %2$s', 'namaste'), $review->user_name, date_i18n($date_format.' '.$time_format, strtotime($review->datetime)));?></p>
		
		<p><?php echo NamasteLMSReviews :: stars($review->rating);?><span class="screen-reader-text"><?php echo $review->rating;?></span></p>
					<?php echo wpautop(stripslashes($review->review));?>
	</div>	
<?php endforeach;?>			