<div class="namaste-review-course">
	<form method="post" id="rateCourse<?php echo intval($course_id);?>">
		<p><?php _e('Rate this course:', 'namaste');?>
		
			<?php foreach($rating_options as $rating => $option):?> 
				<input class="namaste-hidden" id="namasteRatingCourse<?php echo $course_id;?>_<?php echo intval($rating);?>" type="radio" name="namaste_rating" value="<?php echo intval($rating);?>" <?php if($rating == 5) echo 'checked';?>>
				<label for="namaste_rating_<?php echo intval($rating);?>" onmouseover="namasteSetRating(<?php echo intval($rating)?>, <?php echo $course_id;?>)">
					<span id="namasteRatingSpan<?php echo intval($course_id);?>_<?php echo intval($rating);?>" class="namaste-rating dashicons dashicons-star-empty dashicons-star-filled" style="color:#ffb900 !important;" title="<?php echo esc_attr($option);?>"></span>
					<span class="screen-reader-text"><?php echo esc_attr($option);?></span>
				</label>
			<?php endforeach;?>
		</p>
		
		<label><?php _e('Your review','namaste');?></label>
		<div class="namaste-review-box">
			<p><?php wp_editor( $content, $editor_id, $settings );?></p>
		</div>			
		
		<div class="namaste-submit-review">
			<p><input type="submit" value="<?php _e('Send Review','namaste');?>"></p>
		</div>
		
		<input type="hidden" name="course_id" value="<?php echo intval($course_id);?>">
		<input type="hidden" name="namaste_review_course" value="1">
	</form>
</div>