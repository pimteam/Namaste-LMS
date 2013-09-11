<h1><?php _e('Submitting a solution to assignment', 'namaste');?></h1>

<div class="wrap">
		<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $user_ID?>"><?php _e('Back to assignments in', 'namaste')?> "<?php echo $lesson->post_title?>"</a> 
	<?php _e('from course','namaste')?> "<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->ID?>&student_id=<?php echo $user_ID?>"><?php echo $course->post_title?></a>"</p>

	<h2><?php echo $homework->title?></h2>
	
	<div><?php echo apply_filters('the_content', stripslashes($homework->description))?></div>

	<p><b><?php _e('Submit your solution below:','namaste')?></b></p>
	
	<form method="post">
	<div><?php wp_editor('', 'content');?></div>
	<p align="center">
		<input type="submit" value="<?php _e('Submit your solution', 'namaste')?>">
		<input type="hidden" name="ok" value="1">
	</p>
	</form>
</div>