<div class="namaste-search">
	<form class="namaste-search-form" method="get" action="<?php echo home_url();?>">
		<h3><?php _e('Search in Courses and Lessons', 'namaste');?></h3>
		<p>
			<input name="s" type="search" class="search-field" placeholder="<?php _e('Search...', 'namaste');?>" value="<?php echo empty($_GET['s']) ? '' : esc_attr($_GET['s'])?>">
			<?php _e('in', 'namaste');?> <select name="namaste_course_id" onchange="namasteSearchFillLessons(this.value);">
				<option value="0"><?php _e('All courses', 'namaste');?></option>
				<?php foreach($courses as $course):
					$selected = (!empty($_GET['namaste_course_id']) and $_GET['namaste_course_id'] == $course->ID) ? 'selected="selected"' : '';?>
					<option value="<?php echo $course->ID?>" <?php echo $selected?>><?php echo stripslashes($course->post_title);?></option>
				<?php endforeach;?>
			</select>
			
			<select name="namaste_lesson_id" id="namasteLessonSearchSelector" style='display:<?php echo (count($current_lessons) or is_user_logged_in()) ? 'inline' : 'none'; ?>'>
				<option value="0"><?php _e('All lessons', 'namaste');?></option>
				<?php if(count($current_lessons)): 
					foreach($current_lessons as $lesson):
						$selected = (!empty($_GET['namaste_lesson_id']) and $_GET['namaste_lesson_id'] == $lesson->ID) ? 'selected="selected"' : '';?>
						<option value="<?php echo $lesson->ID?>" <?php echo $selected?>><?php echo stripslashes($lesson->post_title);?></option>
				<?php endforeach;
				endif;?>
			</select>
			
			<input type="submit" class="search-submit" value="<?php _e('Search', 'namaste');?>" />
		</p>
		<input type="hidden" name="namaste_search" value="1">
	</form>
</div>

<script type="text/javascript" >
// courses/lessons object for the dropdown
function namasteSearchFillLessons(courseID) {
	var lessons = new Array();
	<?php if(!empty($courses) and count($courses)):
	foreach($courses as $course):?>
	lessons[<?php echo $course->ID?>] = [
		<?php foreach($course->lessons as $lesson):		
		echo '['.$lesson->ID.', "'.stripslashes($lesson->post_title).'"],';
		endforeach;?>
	];
	<?php endforeach;
	endif;?>
	
	if(lessons[courseID] != null) {
		if(lessons[courseID].length > 0 ) { 
			jQuery('#namasteLessonSearchSelector').show();
			var html = '<option value="0"><?php _e('All lessons', 'namaste');?></option>';
			for(i=0; i < lessons[courseID].length; i++) {
				html += '<option value="' + lessons[courseID][i][0] + '">' + lessons[courseID][i][1]+ '</option>' + "\n";
			}
		}
		else jQuery('#namasteLessonSearchSelector').hide();
		
		jQuery('#namasteLessonSearchSelector').html(html);
	}
	else {
		jQuery('#namasteLessonSearchSelector').show();
		jQuery('#namasteLessonSearchSelector').html('<option value="0"><?php _e('All lessons', 'namaste');?></option>');
	}
}
</script>
